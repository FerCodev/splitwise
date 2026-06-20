<?php

namespace App\Controllers;

use App\Models\Pago;
use App\Models\Grupo;
use App\Services\GroupPermission;

class Pagos extends BaseController
{
    public function index()
    {
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));

        $pagoModel = new Pago();
        $filters = [];

        if ($this->request->getGet('grupo_id')) {
            $filters['grupo_id'] = (int) $this->request->getGet('grupo_id');
        }
        if ($this->request->getGet('pagador_id')) {
            $filters['pagador_id'] = (int) $this->request->getGet('pagador_id');
        }
        if ($this->request->getGet('receptor_id')) {
            $filters['receptor_id'] = (int) $this->request->getGet('receptor_id');
        }
        if ($this->request->getGet('fecha_desde')) {
            $filters['fecha_desde'] = $this->request->getGet('fecha_desde');
        }
        if ($this->request->getGet('fecha_hasta')) {
            $filters['fecha_hasta'] = $this->request->getGet('fecha_hasta');
        }
        if ($this->request->getGet('descripcion')) {
            $filters['descripcion'] = $this->request->getGet('descripcion');
        }
        $filters['sort'] = $this->request->getGet('sort') ?: 'fecha';
        $filters['order'] = $this->request->getGet('order') ?: 'DESC';

        $pagos = $pagoModel->getPagosWithFilters($filters, 10);
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
            'pager' => $pager,
        ]);
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

        $prefill = [
            'receptor_id' => $this->request->getGet('receptor_id'),
            'monto' => $this->request->getGet('monto'),
            'fecha' => $this->request->getGet('fecha'),
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

        $pagoModel = new Pago();
        $pagoModel->insert([
            'grupo_id' => $grupoId,
            'pagador_id' => $pagadorId,
            'receptor_id' => $receptorId,
            'monto' => (float) $this->request->getPost('monto'),
            'fecha' => $this->request->getPost('fecha'),
            'descripcion' => $this->request->getPost('descripcion'),
        ]);

        if ($this->request->getPost('origen') === 'grupo_balance') {
            return redirect()->to('/grupos/' . $grupoId)->with('success', 'Pago registrado correctamente.');
        }

        if ($this->request->getPost('origen') === 'grupo_balance_detalle') {
            return redirect()->to('/grupos/' . $grupoId . '/balance')->with('success', 'Pago registrado correctamente.');
        }

        return redirect()->to('/pagos')->with('success', 'Pago registrado correctamente.');
    }

    public function show(int $id)
    {
        $pagoModel = new Pago();
        $pago = $pagoModel->select('pagos.*, pagador.name as pagador_nombre, receptor.name as receptor_nombre, grupos.nombre as grupo_nombre')
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

        return redirect()->to('/pagos')->with('success', 'Pago actualizado correctamente.');
    }

    public function delete(int $id)
    {
        $pagoModel = new Pago();
        $pago = $pagoModel->find($id);

        if (!$pago) {
            return redirect()->to('/pagos')->with('error', 'Pago no encontrado.');
        }

        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($pago['grupo_id']);
        if (!$grupo || !$grupoModel->isMiembro($pago['grupo_id'], session()->get('userId'))) {
            return redirect()->to('/pagos')->with('error', 'No tenés acceso a este pago.');
        }

        $userId = session()->get('userId');
        $rol = $grupoModel->getUserRol($pago['grupo_id'], $userId);

        $errorPermiso = GroupPermission::check($rol, $grupo['estado'], 'pago_delete', $userId, (int) $pago['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to('/pagos')->with('error', $errorPermiso);
        }

        $pagoModel->delete($id);

        return redirect()->to('/pagos')->with('success', 'Pago eliminado correctamente.');
    }

    private function verificarAccesoGrupo(int $grupoId): ?array
    {
        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($grupoId);

        if (!$grupo) {
            return null;
        }

        $userId = session()->get('userId');
        if (!$grupoModel->isMiembro($grupoId, $userId)) {
            return null;
        }

        return [
            'grupo' => $grupo,
            'rol' => $grupoModel->getUserRol($grupoId, $userId),
        ];
    }
}
