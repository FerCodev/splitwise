<?php

namespace App\Controllers;

use App\Controllers\Traits\BuildsListFilters;
use App\Controllers\Traits\ExportResponses;
use App\Controllers\Traits\GroupAccess;
use App\Controllers\Traits\NormalizesAmountInput;
use App\Models\Pago;
use App\Models\Grupo;
use App\Services\GroupPermission;
use App\Services\DebtPaymentValidator;
use App\Services\NotificationService;
use App\Services\UiFeedbackResolver;

class Pagos extends BaseController
{
    use BuildsListFilters {
        getFilters as getBaseFilters;
    }
    use ExportResponses;
    use GroupAccess;
    use NormalizesAmountInput;

    public function index()
    {
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));

        $pagoModel = new Pago();
        $filters = $this->getFilters();

        $pagos = $pagoModel->getPagosWithFilters($filters, 10);
        $totalFiltrado = $this->sumMonto($this->getPagosParaExportar($filters));
        $pager = $pagoModel->pager;

        if ($this->request->getGet('partial')) {
            $requestedPage = max(1, (int) ($this->request->getGet('page') ?: 1));
            if ($requestedPage > $pager->getPageCount()) {
                return '';
            }

            return view('pagos/_items', [
                'pagos' => $pagos,
                'filters' => $filters,
            ]);
        }

        return view('pagos/index', [
            'pagos' => $pagos,
            'grupos' => $grupos,
            'filters' => $filters,
            'totalFiltrado' => $totalFiltrado,
            'pager' => $pager,
        ]);
    }

    public function exportarPdf()
    {
        $filters = $this->getFilters();
        $pagos = $this->getPagosParaExportar($filters);
        $total = array_sum(array_map(static fn($pago) => (float) $pago['monto'], $pagos));
        $this->streamPdf('pagos/pdf', [
            'pagos' => $pagos,
            'total' => $total,
            'fecha' => date('d/m/Y H:i'),
        ], 'pagos');
    }

    public function exportarExcel()
    {
        $filters = $this->getFilters();
        $pagos = $this->getPagosParaExportar($filters);
        $total = array_sum(array_map(static fn($pago) => (float) $pago['monto'], $pagos));

        $this->streamExcel('pagos/excel', [
            'pagos' => $pagos,
            'total' => $total,
            'fecha' => date('d/m/Y H:i'),
        ], 'pagos');
    }

    private function getFilters(): array
    {
        return $this->getBaseFilters(['receptor_id']);
    }

    private function getPagosParaExportar(array $filters): array
    {
        return (new Pago())->getPagosForExport($filters);
    }

    public function new()
    {
        $grupoId = $this->request->getGet('grupo_id') ?: old('grupo_id');
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));
        $miembros = [];

        if ($grupoId) {
            $acceso = $this->verificarAccesoGrupo((int) $grupoId);
            if ($acceso === null) {
                return redirect()->to('/pagos')->with('error', 'Grupo no encontrado o no tenés acceso.');
            }

            $bloqueo = Grupo::restriccionEstado($acceso['grupo']['estado'], 'pago_create');
            if ($bloqueo) {
                return redirect()->to('/pagos')->with('error', $bloqueo);
            }

            $miembros = $grupoModel->getMiembros((int) $grupoId);
        }

        $prefillMonto = $this->request->getGet('monto');
        if ($prefillMonto !== null && $prefillMonto !== '') {
            $prefillMonto = number_format((float) $prefillMonto, 2, '.', '');
        }

        $prefill = [
            'receptor_id' => $this->request->getGet('receptor_id'),
            'monto' => $prefillMonto,
            'fecha' => $this->request->getGet('fecha'),
            'descripcion' => $this->request->getGet('descripcion'),
        ];

        return view('pagos/form', [
            'grupos' => $grupos,
            'grupoId' => $grupoId,
            'miembros' => $miembros,
            'prefill' => $prefill,
        ]);
    }

    public function create()
    {
        $this->normalizarMonto();

        $rules = [
            'descripcion' => 'permit_empty|max_length[255]',
            'monto' => 'required|numeric|greater_than[0]',
            'fecha' => 'required|valid_date',
            'grupo_id' => 'required|is_natural_no_zero',
            'receptor_id' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $grupoId = (int) $this->request->getPost('grupo_id');
        $pagadorId = session()->get('userId');
        $receptorId = (int) $this->request->getPost('receptor_id');

        $grupoModel = new Grupo();
        $userId = session()->get('userId');
        $grupo = $grupoModel->find($grupoId);

        if (!$grupo || !$grupoModel->isMiembro($grupoId, $userId)) {
            return redirect()->to('/pagos')->with('error', 'No tenés acceso a este grupo.');
        }

        $bloqueo = Grupo::restriccionEstado($grupo['estado'], 'pago_create');
        if ($bloqueo) {
            return redirect()->to('/pagos')->with('error', $bloqueo);
        }

        if ($pagadorId === $receptorId) {
            return redirect()->back()->withInput()->with('errors', ['receptor_id' => 'El pagador y el receptor no pueden ser la misma persona.']);
        }

        if (!$grupoModel->isMiembro($grupoId, $receptorId)) {
            return redirect()->back()->withInput()->with('errors', ['receptor_id' => 'El receptor no pertenece al grupo.']);
        }

        $origen = $this->request->getPost('origen');
        if ($origen === 'grupo_balance_detalle') {
            $montoCentavos = DebtPaymentValidator::amountToCents($this->request->getPost('monto'));
            if ($montoCentavos === null) {
                return redirect()->back()->withInput()->with('error', 'El monto debe ser mayor a cero y tener como máximo dos decimales.');
            }

            $db = \Config\Database::connect();
            $db->transStart();
            $db->query('SELECT id FROM grupos WHERE id = ? FOR UPDATE', [$grupoId]);

            $grupoActual = $grupoModel->find($grupoId);
            if (!$grupoActual || $grupoActual['estado'] !== 'activo' || !$grupoModel->isMiembro($grupoId, $pagadorId)) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'El grupo cambió. Actualizá la página antes de registrar el pago.');
            }

            $gastoModel = new \App\Models\Gasto();
            $deuda = $gastoModel->getDeudaVigente($grupoId, $pagadorId, $receptorId);
            $errorMonto = DebtPaymentValidator::validateCurrentDebt($deuda, $this->request->getPost('monto'));
            if ($errorMonto !== null) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', $errorMonto);
            }

            $pagoModel = new Pago();
            $insertId = $pagoModel->insert([
                'grupo_id' => $grupoId,
                'pagador_id' => $pagadorId,
                'receptor_id' => $receptorId,
                'monto' => DebtPaymentValidator::centsToDecimal($montoCentavos),
                'fecha' => $this->request->getPost('fecha'),
                'descripcion' => $this->request->getPost('descripcion'),
            ]);

            if (!$insertId) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'No se pudo registrar el pago. Intentá nuevamente.');
            }

            $db->transComplete();
            if (!$db->transStatus()) {
                return redirect()->back()->withInput()->with('error', 'No se pudo registrar el pago. Intentá nuevamente.');
            }

            $actorId = (int) session()->get('userId');
            $actorName = session()->get('userName') ?? 'Usuario';
            $grupoNombre = $grupo['nombre'] ?? 'Grupo';
            $this->queueNotifications(static function (NotificationService $notifications) use (
                $grupoNombre, $actorId, $actorName, $insertId, $pagadorId, $receptorId, $montoCentavos
            ): void {
                $notifications->notifyPaymentCreated(
                    $grupoNombre, $actorId, $actorName, (int) $insertId,
                    (int) $pagadorId, $receptorId,
                    DebtPaymentValidator::centsToDecimal($montoCentavos)
                );
            });

            $successMessage = UiFeedbackResolver::message('payments.create.completed', [], 'Pago registrado correctamente.');
            return redirect()->to('/grupos/' . $grupoId . '/balance')->with('success', $successMessage);
        }

        $pagoModel = new Pago();
        $insertId = $pagoModel->insert([
            'grupo_id' => $grupoId,
            'pagador_id' => $pagadorId,
            'receptor_id' => $receptorId,
            'monto' => (float) $this->request->getPost('monto'),
            'fecha' => $this->request->getPost('fecha'),
            'descripcion' => $this->request->getPost('descripcion'),
        ]);

        if (!$insertId) {
            return redirect()->back()->withInput()->with('error', 'No se pudo registrar el pago. Intentá nuevamente.');
        }

        $actorId = (int) session()->get('userId');
        $actorName = session()->get('userName') ?? 'Usuario';
        $grupoNombre = $grupo['nombre'] ?? 'Grupo';
        $monto = (float) $this->request->getPost('monto');
        $this->queueNotifications(static function (NotificationService $notifications) use (
            $grupoNombre, $actorId, $actorName, $insertId, $pagadorId, $receptorId, $monto
        ): void {
            $notifications->notifyPaymentCreated(
                $grupoNombre, $actorId, $actorName, (int) $insertId,
                (int) $pagadorId, $receptorId, $monto
            );
        });

        $successMessage = UiFeedbackResolver::message('payments.create.completed', [], 'Pago registrado correctamente.');

        if ($this->request->getPost('origen') === 'grupo_balance') {
            return redirect()->to('/grupos/' . $grupoId)->with('success', $successMessage);
        }

        if ($this->request->getPost('origen') === 'grupo_balance_detalle') {
            return redirect()->to('/grupos/' . $grupoId . '/balance')->with('success', $successMessage);
        }

        return redirect()->to('/pagos')->with('success', $successMessage);
    }

    public function show(int $id)
    {
        $pagoModel = new Pago();
        $pago = $pagoModel->select('pagos.*, pagador.name as pagador_nombre, receptor.name as receptor_nombre, pagador.avatar_filename as pagador_avatar_filename, pagador.avatar_updated_at as pagador_avatar_updated_at, receptor.avatar_filename as receptor_avatar_filename, receptor.avatar_updated_at as receptor_avatar_updated_at, grupos.nombre as grupo_nombre')
            ->join('users as pagador', 'pagador.id = pagos.pagador_id')
            ->join('users as receptor', 'receptor.id = pagos.receptor_id')
            ->join('grupos', 'grupos.id = pagos.grupo_id')
            ->find($id);

        if (!$pago) {
            return redirect()->to('/pagos')->with('error', 'Pago no encontrado.');
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');

        if (!$grupoModel->isMiembro($pago['grupo_id'], $userId)) {
            return redirect()->to('/pagos')->with('error', 'No tenés acceso a este pago.');
        }

        $rol = $grupoModel->getUserRol($pago['grupo_id'], $userId);
        $grupo = $grupoModel->find($pago['grupo_id']);
        $permisos = GroupPermission::getAll($rol, $grupo['estado'], $userId, null, (int) $pago['pagador_id']);

        return view('pagos/show', [
            'pago' => $pago,
            'rol' => $rol,
            'permisos' => $permisos,
        ]);
    }

    public function edit(int $id)
    {
        $pagoModel = new Pago();
        $pago = $pagoModel->find($id);

        if (!$pago) {
            return redirect()->to('/pagos')->with('error', 'Pago no encontrado.');
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');
        $grupo = $grupoModel->find($pago['grupo_id']);

        if (!$grupo || !$grupoModel->isMiembro($pago['grupo_id'], $userId)) {
            return redirect()->to('/pagos')->with('error', 'No tenés acceso a este pago.');
        }

        $rol = $grupoModel->getUserRol($pago['grupo_id'], $userId);

        $errorPermiso = GroupPermission::check($rol, $grupo['estado'], 'pago_edit', $userId, (int) $pago['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to("/pagos/{$id}")->with('error', $errorPermiso);
        }

        $grupos = $grupoModel->getGruposByUser($userId);
        $miembros = $grupoModel->getMiembros($pago['grupo_id']);

        return view('pagos/form', [
            'pago' => $pago,
            'rol' => $rol,
            'grupos' => $grupos,
            'grupoId' => $pago['grupo_id'],
            'miembros' => $miembros,
        ]);
    }

    public function update(int $id)
    {
        $this->normalizarMonto();

        $pagoModel = new Pago();
        $pagoExistente = $pagoModel->find($id);

        if (!$pagoExistente) {
            return redirect()->to('/pagos')->with('error', 'Pago no encontrado.');
        }

        $rules = [
            'descripcion' => 'permit_empty|max_length[255]',
            'monto' => 'required|numeric|greater_than[0]',
            'fecha' => 'required|valid_date',
            'receptor_id' => 'required|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $receptorId = (int) $this->request->getPost('receptor_id');
        $grupoIdOriginal = (int) $pagoExistente['grupo_id'];

        $grupoModel = new Grupo();
        $userId = session()->get('userId');
        $grupo = $grupoModel->find($grupoIdOriginal);

        if (!$grupo || !$grupoModel->isMiembro($grupoIdOriginal, $userId)) {
            return redirect()->to('/pagos')->with('error', 'No tenés acceso a este pago.');
        }

        $rol = $grupoModel->getUserRol($grupoIdOriginal, $userId);

        $errorPermiso = GroupPermission::check($rol, $grupo['estado'], 'pago_edit', $userId, (int) $pagoExistente['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to('/pagos')->with('error', $errorPermiso);
        }

        if ($rol !== 'admin') {
            $pagadorId = (int) $pagoExistente['pagador_id'];
        } else {
            $pagadorId = (int) $this->request->getPost('pagador_id');
        }

        if ($pagadorId === $receptorId) {
            return redirect()->back()->withInput()->with('errors', ['receptor_id' => 'El pagador y el receptor no pueden ser la misma persona.']);
        }

        if (!$grupoModel->isMiembro($grupoIdOriginal, $pagadorId)) {
            return redirect()->back()->withInput()->with('errors', ['pagador_id' => 'El pagador no pertenece al grupo.']);
        }

        if (!$grupoModel->isMiembro($grupoIdOriginal, $receptorId)) {
            return redirect()->back()->withInput()->with('errors', ['receptor_id' => 'El receptor no pertenece al grupo.']);
        }

        $pagoModel->update($id, [
            'grupo_id' => $grupoIdOriginal,
            'pagador_id' => $pagadorId,
            'receptor_id' => $receptorId,
            'monto' => (float) $this->request->getPost('monto'),
            'fecha' => $this->request->getPost('fecha'),
            'descripcion' => $this->request->getPost('descripcion'),
        ]);

        return redirect()->to('/pagos')->with('success', UiFeedbackResolver::message('payments.update.completed', [], 'Pago actualizado correctamente.'));
    }

    public function delete(int $id)
    {
        $pagoModel = new Pago();
        $pago = $pagoModel->find($id);

        if (!$pago) {
            return redirect()->to('/pagos')->with('error', UiFeedbackResolver::message('payments.delete.failed', ['reason' => 'Pago no encontrado.'], 'Pago no encontrado.'));
        }

        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($pago['grupo_id']);
        if (!$grupo || !$grupoModel->isMiembro($pago['grupo_id'], session()->get('userId'))) {
            return redirect()->to('/pagos')->with('error', UiFeedbackResolver::message('payments.delete.failed', ['reason' => 'No tenés acceso a este pago.'], 'No tenés acceso a este pago.'));
        }

        $userId = session()->get('userId');
        $rol = $grupoModel->getUserRol($pago['grupo_id'], $userId);

        $errorPermiso = GroupPermission::check($rol, $grupo['estado'], 'pago_delete', $userId, (int) $pago['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to('/pagos')->with('error', $errorPermiso);
        }

        $pagoModel->delete($id);

        return redirect()->to('/pagos')->with('success', UiFeedbackResolver::message('payments.delete.completed', [], 'Pago eliminado correctamente.'));
    }

}
