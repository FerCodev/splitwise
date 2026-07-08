<?php

namespace App\Controllers;

use App\Controllers\Traits\BuildsListFilters;
use App\Controllers\Traits\ExportResponses;
use App\Controllers\Traits\GroupAccess;
use App\Controllers\Traits\NormalizesAmountInput;
use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Grupo;
use App\Models\GastoParticipante;
use App\Models\Categoria;
use App\Models\UserGroupColorOverride;
use App\Services\GroupPermission;
use App\Services\NotificationService;
use App\Services\UiFeedbackResolver;
use App\Services\UserColor;

class Gastos extends BaseController
{
    use BuildsListFilters {
        getFilters as getBaseFilters;
    }
    use ExportResponses;
    use GroupAccess;
    use NormalizesAmountInput;

    public function index()
    {
        $userId = (int) session()->get('userId');
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser($userId);

        $gastoModel = new Gasto();
        $filters = $this->getFilters();

        $gastos = $gastoModel->getGastosWithFilters($filters, 10);
        $totalFiltrado = $this->getTotalFiltrado($filters);
        $pager = $gastoModel->pager;

        $colorMap = $this->buildColorMapForMovimientos($userId, $gastos);

        if ($this->request->getGet('partial')) {
            $requestedPage = max(1, (int) ($this->request->getGet('page') ?: 1));
            if ($requestedPage > $pager->getPageCount()) {
                return '';
            }

            return view('gastos/_items', [
                'gastos'    => $gastos,
                'filters'   => $filters,
                'colorMap'  => $colorMap,
            ]);
        }

        return view('gastos/index', [
            'gastos' => $gastos,
            'grupos' => $grupos,
            'filters' => $filters,
            'totalFiltrado' => $totalFiltrado,
            'categorias' => model(Categoria::class)->getActivas(),
            'pager' => $pager,
            'colorMap' => $colorMap,
        ]);
    }

    /**
     * Construye un mapa [gasto_id => colorKey] resolviendo el color del
     * pagador de cada gasto en su grupo para el viewer actual.
     *
     * Optimizacion: agrupa overrides por grupo (1 query por grupo unico)
     * y global colors por pagador (1 query para todos los pagadores
     * unicos). Asi el costo no crece lineal con el numero de gastos
     * mostrados.
     *
     * @return array<int, string>
     */
    private function buildColorMapForMovimientos(int $viewerId, array $movimientos): array
    {
        if (empty($movimientos)) {
            return [];
        }

        $grupoIds  = [];
        $targetIds = [];
        foreach ($movimientos as $m) {
            $gid = (int) ($m['grupo_id'] ?? 0);
            $tid = (int) ($m['pagador_id'] ?? 0);
            if ($gid > 0) $grupoIds[$gid] = true;
            if ($tid > 0) $targetIds[$tid] = true;
        }
        if (empty($targetIds)) {
            return [];
        }

        $overrideModel = new UserGroupColorOverride();
        $overridesByGroup = [];
        foreach (array_keys($grupoIds) as $gid) {
            $overridesByGroup[$gid] = $overrideModel->getOverridesForGroup($viewerId, $gid);
        }

        $targetIdList = array_keys($targetIds);
        $globals = db_connect()->table('users')
            ->select('id, color')
            ->whereIn('id', $targetIdList)
            ->get()
            ->getResultArray();
        $globalMap = [];
        foreach ($globals as $row) {
            $globalMap[(int) $row['id']] = (string) ($row['color'] ?? UserColor::DEFAULT_KEY);
        }

        $out = [];
        foreach ($movimientos as $m) {
            $gid = (int) ($m['grupo_id'] ?? 0);
            $tid = (int) ($m['pagador_id'] ?? 0);
            if ($tid <= 0 || $gid <= 0) {
                continue;
            }
            $override = $overridesByGroup[$gid][$tid] ?? null;
            $out[(int) $m['id']] = UserColor::resolve($override, $globalMap[$tid] ?? null);
        }
        return $out;
    }

    public function exportarPdf()
    {
        $filters = $this->getFilters();
        $gastos = $this->getGastosParaExportar($filters);

        $total = array_sum(array_map(static fn($gasto) => (float) $gasto['monto'], $gastos));
        $this->streamPdf('gastos/pdf', [
            'gastos' => $gastos,
            'filters' => $filters,
            'total' => $total,
            'fecha' => date('d/m/Y H:i'),
        ], 'gastos');
    }

    public function exportarExcel()
    {
        $filters = $this->getFilters();
        $gastos = $this->getGastosParaExportar($filters);
        $total = array_sum(array_map(static fn($gasto) => (float) $gasto['monto'], $gastos));

        $this->streamExcel('gastos/excel', [
            'gastos' => $gastos,
            'total' => $total,
            'fecha' => date('d/m/Y H:i'),
        ], 'gastos');
    }

    private function getFilters(): array
    {
        $filters = $this->getBaseFilters();
        if ($this->request->getGet('categoria_id')) {
            $filters['categoria_id'] = (int) $this->request->getGet('categoria_id');
        }

        return $filters;
    }

    private function getTotalFiltrado(array $filters): float
    {
        return array_sum(array_map(static fn($gasto) => (float) $gasto['monto'], $this->getGastosParaExportar($filters)));
    }

    private function getGastosParaExportar(array $filters): array
    {
        return (new Gasto())->getGastosForExport($filters);
    }

    public function new()
    {
        $grupoId = $this->request->getGet('grupo_id') ?: old('grupo_id');
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));
        $miembros = [];
        $participantesIds = [];

        if ($grupoId) {
            $acceso = $this->verificarAccesoGrupo((int) $grupoId);
            if ($acceso === null) {
                return redirect()->to('/gastos')->with('error', 'Grupo no encontrado o no tenés acceso.');
            }

            $bloqueo = Grupo::restriccionEstado($acceso['grupo']['estado'], 'gasto_create');
            if ($bloqueo) {
                return redirect()->to('/gastos')->with('error', $bloqueo);
            }

            $miembros = $grupoModel->getMiembros((int) $grupoId);
            $participantesIds = array_column($miembros, 'user_id');
        }

        $prefillMonto = $this->request->getGet('monto');
        if ($prefillMonto !== null && $prefillMonto !== '') {
            $prefillMonto = number_format((float) $prefillMonto, 2, '.', '');
        }

        $prefill = [
            'descripcion' => $this->request->getGet('descripcion'),
            'monto' => $prefillMonto,
            'fecha' => $this->request->getGet('fecha'),
            'categoria_id' => $this->request->getGet('categoria_id'),
        ];

        return view('gastos/form', [
            'grupos' => $grupos,
            'grupoId' => $grupoId,
            'miembros' => $miembros,
            'participantesIds' => $participantesIds,
            'pagadorPorDefecto' => session()->get('userId'),
            'categorias' => model(Categoria::class)->getActivas(),
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
            'participantes' => 'required',
            'categoria_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $grupoId = (int) $this->request->getPost('grupo_id');
        $pagadorId = (int) ($this->request->getPost('pagador_id') ?: session()->get('userId'));
        $monto = (float) $this->request->getPost('monto');
        $descripcion = trim((string) $this->request->getPost('descripcion'));
        if ($descripcion === '') {
            $descripcion = 'Gasto';
        }
        $participantesIds = $this->request->getPost('participantes');

        if (!is_array($participantesIds) || count($participantesIds) < 1) {
            return redirect()->back()->withInput()->with('errors', ['participantes' => 'Debe seleccionar al menos un participante.']);
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');
        $grupo = $grupoModel->find($grupoId);

        if (!$grupo || !$grupoModel->isMiembro($grupoId, $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este grupo.');
        }

        $bloqueo = Grupo::restriccionEstado($grupo['estado'], 'gasto_create');
        if ($bloqueo) {
            return redirect()->to('/gastos')->with('error', $bloqueo);
        }

        $miembros = $grupoModel->getMiembros($grupoId);
        $miembrosIds = array_column($miembros, 'user_id');

        if (!in_array($pagadorId, $miembrosIds)) {
            return redirect()->back()->withInput()->with('errors', ['pagador_id' => 'El pagador no pertenece al grupo.']);
        }

        foreach ($participantesIds as $pid) {
            if (!in_array((int) $pid, $miembrosIds)) {
                return redirect()->back()->withInput()->with('errors', ['participantes' => 'Uno de los participantes no pertenece al grupo.']);
            }
        }

        $divisionTipo = $this->request->getPost('division_tipo') ?: 'igualitario';
        $divisionValores = $this->request->getPost('division_valores') ?? [];

        $participantesIds = array_unique(array_map('intval', $participantesIds));
        $participantesMonto = Gasto::calcularMontosDivision($divisionTipo, $monto, $participantesIds, $divisionValores);

        $gastoModel = new Gasto();
        $categoriaModel = model(Categoria::class);
        $categoriaId = (int) $this->request->getPost('categoria_id');
        $catValida = $categoriaModel->find($categoriaId);
        if ($categoriaId <= 0 || !$catValida || !$catValida['activa']) {
            $categoriaId = $categoriaModel->getOtrosId();
        }

        $errorValidacion = $this->validarDivision($divisionTipo, $monto, $participantesIds, $divisionValores, $miembrosIds);
        if ($errorValidacion !== null) {
            return redirect()->back()->withInput()->with('errors', $errorValidacion);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $reciboData = $this->procesarRecibo();
            if ($reciboData === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', UiFeedbackResolver::message('expenses.create.failed', ['reason' => 'Error al procesar el recibo. Verific&aacute; el formato y tama&ntilde;o.'], 'Error al procesar el recibo. Verific&aacute; el formato y tama&ntilde;o.'));
            }

            $gastoId = $gastoModel->insert([
                'grupo_id' => $grupoId,
                'pagador_id' => $pagadorId,
                'descripcion' => $descripcion,
                'monto' => $monto,
                'fecha' => $this->request->getPost('fecha'),
                'categoria_id' => $categoriaId,
                'division_tipo' => $divisionTipo,
                'nota' => $this->request->getPost('nota') ?: null,
            ] + ($reciboData ?: []));

            if (!$gastoId) {
                throw new \RuntimeException('Error al crear el gastito.');
            }

            $participanteModel = new GastoParticipante();
            foreach ($participantesIds as $pid) {
                if (!$participanteModel->insert([
                    'gasto_id' => $gastoId,
                    'user_id' => $pid,
                    'monto_asignado' => $participantesMonto[$pid],
                ])) {
                    throw new \RuntimeException('Error al insertar participante.');
                }
            }

            $divisionModel = new GastoDivision();
            $valoresRaw = [];
            if (!empty($divisionValores) && is_array($divisionValores)) {
                foreach ($divisionValores as $dv) {
                    $valoresRaw[(int) $dv['user_id']] = (float) $dv['valor'];
                }
            }
            foreach ($participantesIds as $pid) {
                if (!$divisionModel->insert([
                    'gasto_id' => $gastoId,
                    'user_id' => $pid,
                    'tipo' => $divisionTipo,
                    'valor' => $valoresRaw[$pid] ?? null,
                    'monto_calculado' => $participantesMonto[$pid],
                ])) {
                    throw new \RuntimeException('Error al insertar division.');
                }
            }

            $notificationService = new NotificationService();
            $grupoNombre = $grupo['nombre'] ?? 'Grupo';
            $actorName = session()->get('userName') ?? 'Usuario';
            $notificationService->notifyExpenseCreated(
                $grupoId,
                $grupoNombre,
                (int) $userId,
                $actorName,
                $gastoId,
                $descripcion,
                $monto
            );

            $db->transCommit();
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', UiFeedbackResolver::message('expenses.create.failed', ['reason' => 'Error al crear el gastito. Intentalo de nuevo.'], 'Error al crear el gastito. Intentalo de nuevo.'));
        }

        // La notificación del alta ya se encoló dentro de la transacción.
        $this->queueNotifications();

        return redirect()->to('/grupos/' . $grupoId)->with('success', UiFeedbackResolver::message('expenses.create.completed', [], 'Gastito creado correctamente.'));
    }

    public function show(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->select('gastos.*, categorias.nombre as categoria_nombre, users.name as pagador_nombre, users.avatar_filename as pagador_avatar_filename, users.avatar_updated_at as pagador_avatar_updated_at, grupos.nombre as grupo_nombre')
            ->join('users', 'users.id = gastos.pagador_id')
            ->join('grupos', 'grupos.id = gastos.grupo_id')
            ->join('categorias', 'categorias.id = gastos.categoria_id', 'left')
            ->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', 'Gastito no encontrado.');
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');

        if (!$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gastito.');
        }

        $grupo = $grupoModel->find($gasto['grupo_id']);
        $rol = $grupoModel->getUserRol($gasto['grupo_id'], $userId);
        $permisos = GroupPermission::getAll($rol, $grupo['estado'], $userId, (int) $gasto['pagador_id']);

        $participantes = $gastoModel->getParticipantes($id);
        $deleteReturnTo = $this->resolveDeleteReturnContext((int) $gasto['grupo_id']);

        return view('gastos/show', [
            'gasto' => $gasto,
            'rol' => $rol,
            'permisos' => $permisos,
            'participantes' => $participantes,
            'deleteReturnTo' => $deleteReturnTo,
            'categorias' => model(Categoria::class)->getActivas(),
        ]);
    }

    public function edit(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', 'Gastito no encontrado.');
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');

        if (!$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gastito.');
        }

        $grupo = $grupoModel->find($gasto['grupo_id']);
        $rol = $grupoModel->getUserRol($gasto['grupo_id'], $userId);

        $errorPermiso = GroupPermission::check($rol, $grupo['estado'], 'gasto_edit', $userId, (int) $gasto['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to("/gastos/{$id}")->with('error', $errorPermiso);
        }

        $grupos = $grupoModel->getGruposByUser($userId);
        $miembros = $grupoModel->getMiembros($gasto['grupo_id']);
        $participantes = $gastoModel->getParticipantes($id);
        $participantesIds = array_column($participantes, 'user_id');

        $gastoDivisiones = model(\App\Models\GastoDivision::class)
            ->where('gasto_id', $id)
            ->findAll();
        $divisionValoresExistentes = [];
        foreach ($gastoDivisiones as $gd) {
            $divisionValoresExistentes[(int) $gd['user_id']] = $gd['valor'] ?? '';
        }

        return view('gastos/form', [
            'gasto' => $gasto,
            'rol' => $rol,
            'grupos' => $grupos,
            'grupoId' => $gasto['grupo_id'],
            'miembros' => $miembros,
            'participantesIds' => $participantesIds,
            'categorias' => model(Categoria::class)->getActivas(),
            'divisionValoresExistentes' => $divisionValoresExistentes,
        ]);
    }

    public function update(int $id)
    {
        $this->normalizarMonto();

        $gastoModel = new Gasto();
        $gastoExistente = $gastoModel->find($id);

        if (!$gastoExistente) {
            return redirect()->to('/gastos')->with('error', 'Gastito no encontrado.');
        }

        $participantesAnteriores = array_map(
            'intval',
            array_column($gastoModel->getParticipantes($id), 'user_id')
        );

        $rules = [
            'descripcion' => 'permit_empty|max_length[255]',
            'monto' => 'required|numeric|greater_than[0]',
            'fecha' => 'required|valid_date',
            'participantes' => 'required',
            'categoria_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $monto = (float) $this->request->getPost('monto');
        $descripcion = trim((string) $this->request->getPost('descripcion'));
        if ($descripcion === '') {
            $descripcion = 'Gasto';
        }
        $participantesIds = $this->request->getPost('participantes');

        if (!is_array($participantesIds) || count($participantesIds) < 1) {
            return redirect()->back()->withInput()->with('errors', ['participantes' => 'Debe seleccionar al menos un participante.']);
        }

        $grupoIdOriginal = (int) $gastoExistente['grupo_id'];

        $grupoModel = new Grupo();
        $userId = session()->get('userId');
        $grupo = $grupoModel->find($grupoIdOriginal);

        if (!$grupo || !$grupoModel->isMiembro($grupoIdOriginal, $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gastito.');
        }

        $rol = $grupoModel->getUserRol($grupoIdOriginal, $userId);

        $errorPermiso = GroupPermission::check($rol, $grupo['estado'], 'gasto_edit', $userId, (int) $gastoExistente['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to('/gastos')->with('error', $errorPermiso);
        }

        if ($rol !== 'admin') {
            $pagadorId = (int) $gastoExistente['pagador_id'];
        } else {
            $pagadorId = (int) $this->request->getPost('pagador_id');
        }

        if (!$grupoModel->isMiembro($grupoIdOriginal, $pagadorId)) {
            return redirect()->back()->withInput()->with('errors', ['pagador_id' => 'El pagador no pertenece al grupo.']);
        }

        $miembros = $grupoModel->getMiembros($grupoIdOriginal);
        $miembrosIds = array_column($miembros, 'user_id');

        foreach ($participantesIds as $pid) {
            if (!in_array((int) $pid, $miembrosIds)) {
                return redirect()->back()->withInput()->with('errors', ['participantes' => 'Uno de los participantes no pertenece al grupo.']);
            }
        }

        $categoriaModel = model(Categoria::class);
        $categoriaId = (int) $this->request->getPost('categoria_id');
        $catValida = $categoriaModel->find($categoriaId);
        if ($categoriaId <= 0 || !$catValida || !$catValida['activa']) {
            $categoriaId = $categoriaModel->getOtrosId();
        }

        $reciboData = $this->procesarRecibo();
        if ($reciboData === false) {
            return redirect()->back()->withInput()->with('error', UiFeedbackResolver::message('expenses.update.failed', ['reason' => 'Error al procesar el recibo. Verific&aacute; el formato y tama&ntilde;o.'], 'Error al procesar el recibo. Verific&aacute; el formato y tama&ntilde;o.'));
        }

        $divisionTipo = $this->request->getPost('division_tipo') ?: 'igualitario';
        $divisionValores = $this->request->getPost('division_valores') ?? [];
        $participantesIds = array_unique(array_map('intval', $participantesIds));

        $errorValidacion = $this->validarDivision($divisionTipo, $monto, $participantesIds, $divisionValores, $miembrosIds);
        if ($errorValidacion !== null) {
            return redirect()->back()->withInput()->with('errors', $errorValidacion);
        }

        $participantesMonto = Gasto::calcularMontosDivision($divisionTipo, $monto, $participantesIds, $divisionValores);

        $updateData = [
            'grupo_id' => $grupoIdOriginal,
            'pagador_id' => $pagadorId,
            'descripcion' => $descripcion,
            'monto' => $monto,
            'fecha' => $this->request->getPost('fecha'),
            'categoria_id' => $categoriaId,
            'division_tipo' => $divisionTipo,
            'nota' => $this->request->getPost('nota') ?: null,
        ];

        if (!empty($reciboData)) {
            if (!empty($gastoExistente['recibo_path'])) {
                $oldPath = WRITEPATH . $gastoExistente['recibo_path'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            $updateData = array_merge($updateData, $reciboData);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $gastoModel->update($id, $updateData);

            $participanteModel = new GastoParticipante();
            $participanteModel->where('gasto_id', $id)->delete();

            foreach ($participantesIds as $pid) {
                if (!$participanteModel->insert([
                    'gasto_id' => $id,
                    'user_id' => $pid,
                    'monto_asignado' => $participantesMonto[$pid],
                ])) {
                    throw new \RuntimeException('Error al insertar participante.');
                }
            }

            $divisionModel = new GastoDivision();
            $divisionModel->where('gasto_id', $id)->delete();

            $valoresRaw = [];
            if (!empty($divisionValores) && is_array($divisionValores)) {
                foreach ($divisionValores as $dv) {
                    $valoresRaw[(int) $dv['user_id']] = (float) $dv['valor'];
                }
            }
            foreach ($participantesIds as $pid) {
                if (!$divisionModel->insert([
                    'gasto_id' => $id,
                    'user_id' => $pid,
                    'tipo' => $divisionTipo,
                    'valor' => $valoresRaw[$pid] ?? null,
                    'monto_calculado' => $participantesMonto[$pid],
                ])) {
                    throw new \RuntimeException('Error al insertar division.');
                }
            }

            $db->transCommit();
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', UiFeedbackResolver::message('expenses.update.failed', ['reason' => 'Error al actualizar el gastito. Intentalo de nuevo.'], 'Error al actualizar el gastito. Intentalo de nuevo.'));
        }


        $afectados = array_values(array_unique(array_merge(
            $participantesAnteriores,
            $participantesIds,
            [(int) $gastoExistente['pagador_id'], $pagadorId]
        )));
        $actorId = (int) session()->get('userId');
        $actorName = session()->get('userName') ?? 'Usuario';
        $grupoNombre = $grupo['nombre'] ?? 'Grupo';
        $this->queueNotifications(static function (NotificationService $notifications) use (
            $grupoNombre, $actorId, $actorName, $id, $descripcion, $monto, $afectados
        ): void {
            $notifications->notifyExpenseUpdated(
                $grupoNombre, $actorId, $actorName, $id, $descripcion, $monto, $afectados
            );
        });

        return redirect()->to('/grupos/' . $grupoIdOriginal)->with('success', UiFeedbackResolver::message('expenses.update.completed', [], 'Gastito actualizado correctamente.'));
    }

    public function delete(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', UiFeedbackResolver::message('expenses.delete.failed', ['reason' => 'Gastito no encontrado.'], 'Gastito no encontrado.'));
        }

        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($gasto['grupo_id']);
        if (!$grupo || !$grupoModel->isMiembro($gasto['grupo_id'], session()->get('userId'))) {
            return redirect()->to('/gastos')->with('error', UiFeedbackResolver::message('expenses.delete.failed', ['reason' => 'No tenés acceso a este gastito.'], 'No tenés acceso a este gastito.'));
        }

        $userId = session()->get('userId');
        $rol = $grupoModel->getUserRol($gasto['grupo_id'], $userId);

        $errorPermiso = GroupPermission::check($rol, $grupo['estado'], 'gasto_delete', $userId, (int) $gasto['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to('/gastos')->with('error', $errorPermiso);
        }


        $afectados = array_values(array_unique(array_merge(
            array_map('intval', array_column($gastoModel->getParticipantes($id), 'user_id')),
            [(int) $gasto['pagador_id']]
        )));

        // Eliminar archivo de recibo si existe
        if (!empty($gasto['recibo_path'])) {
            $path = WRITEPATH . $gasto['recibo_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $gastoModel->delete($id);

        $actorId = (int) session()->get('userId');
        $actorName = session()->get('userName') ?? 'Usuario';
        $grupoNombre = $grupo['nombre'] ?? 'Grupo';
        $descripcion = (string) ($gasto['descripcion'] ?? 'Gasto');
        $monto = (float) $gasto['monto'];
        $grupoId = (int) $gasto['grupo_id'];
        $this->queueNotifications(static function (NotificationService $notifications) use (
            $grupoId, $grupoNombre, $actorId, $actorName, $descripcion, $monto, $afectados
        ): void {
            $notifications->notifyExpenseDeleted(
                $grupoId, $grupoNombre, $actorId, $actorName, $descripcion, $monto, $afectados
            );
        });

        $returnTo = match ((string) $this->request->getPost('return_to')) {
            'grupo' => '/grupos/' . $grupoId,
            'notificaciones' => '/notificaciones',
            default => '/dashboard',
        };

        return redirect()->to($returnTo)->with('success', UiFeedbackResolver::message('expenses.delete.completed', [], 'Gastito eliminado correctamente.'));
    }

    private function resolveDeleteReturnContext(int $grupoId): string
    {
        $explicit = (string) $this->request->getGet('return_to');
        if (in_array($explicit, ['grupo', 'notificaciones', 'home'], true)) {
            return $explicit;
        }

        $referer = (string) $this->request->getServer('HTTP_REFERER');
        $path = (string) (parse_url($referer, PHP_URL_PATH) ?? '');

        if ($path !== '' && str_contains($path, '/notificaciones')) {
            return 'notificaciones';
        }

        if ($path !== '' && preg_match('#/grupos/' . preg_quote((string) $grupoId, '#') . '(?:/|$)#', $path)) {
            return 'grupo';
        }

        return 'home';
    }

    private function procesarRecibo(): array|false
    {
        $file = $this->request->getFile('recibo');
        if (!$file || !$file->isValid()) {
            return []; // sin recibo adjunto
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return false;
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            return false;
        }

        $clientName = $file->getClientName();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $extension = $file->getExtension();

        $year = date('Y');
        $month = date('m');
        $dir = WRITEPATH . 'uploads/recibos/' . $year . '/' . $month;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        $relativePath = 'uploads/recibos/' . $year . '/' . $month . '/' . $safeName;

        $file->move($dir, $safeName);

        return [
            'recibo_path' => $relativePath,
            'recibo_nombre' => $clientName,
            'recibo_mime' => $mimeType,
            'recibo_size' => $fileSize,
        ];
    }

    public function recibo(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->find($id);
        if (!$gasto || empty($gasto['recibo_path'])) {
            return redirect()->to('/gastos')->with('error', 'Recibo no encontrado.');
        }

        $userId = session()->get('userId');
        $grupoModel = new Grupo();
        if (!$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este recibo.');
        }

        $path = WRITEPATH . $gasto['recibo_path'];
        if (!file_exists($path)) {
            return redirect()->to('/gastos')->with('error', 'El archivo del recibo ya no existe.');
        }

        $mime = $gasto['recibo_mime'] ?? mime_content_type($path);
        return $this->response->download($path, null)->setContentType($mime);
    }

    public function deleteRecibo(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->find($id);
        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', UiFeedbackResolver::message('expenses.receipt.delete.failed', ['reason' => 'Gastito no encontrado.'], 'Gastito no encontrado.'));
        }

        $userId = session()->get('userId');
        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($gasto['grupo_id']);
        if (!$grupo || !$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', UiFeedbackResolver::message('expenses.receipt.delete.failed', ['reason' => 'No tenés acceso a este gastito.'], 'No tenés acceso a este gastito.'));
        }

        $rol = $grupoModel->getUserRol($gasto['grupo_id'], $userId);
        $errorPermiso = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'gasto_edit', $userId, (int) $gasto['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to('/gastos')->with('error', $errorPermiso);
        }

        if (!empty($gasto['recibo_path'])) {
            $path = WRITEPATH . $gasto['recibo_path'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $gastoModel->update($id, [
            'recibo_path' => null,
            'recibo_nombre' => null,
            'recibo_mime' => null,
            'recibo_size' => null,
        ]);

        return redirect()->back()->with('success', UiFeedbackResolver::message('expenses.receipt.delete.completed', [], 'Recibo eliminado correctamente.'));
    }

    private function validarDivision(string $divisionTipo, float $monto, array $participantesIds, array $divisionValores, array $miembrosIds): ?array
    {
        $tiposValidos = ['igualitario', 'monto_fijo', 'porcentaje'];
        if (!in_array($divisionTipo, $tiposValidos, true)) {
            return ['division' => 'Tipo de divisi&oacute;n inv&aacute;lido.'];
        }

        if ($divisionTipo === 'igualitario') {
            return null;
        }

        $valores = array_values($divisionValores);
        $totalValor = 0;

        if (count($valores) !== count($participantesIds)) {
            return ['division' => 'La cantidad de valores de divisi&oacute;n no coincide con los participantes.'];
        }

        $valUserIds = array_map('intval', array_column($valores, 'user_id'));
        $diff = array_merge(
            array_diff($valUserIds, $participantesIds),
            array_diff($participantesIds, $valUserIds)
        );
        if (!empty($diff)) {
            return ['division' => 'Los participantes en la divisi&oacute;n no coinciden con los seleccionados.'];
        }

        foreach ($valores as $v) {
            if (empty($v['user_id'])) {
                return ['division' => 'Falta user_id en un valor de divisi&oacute;n.'];
            }
            if (!isset($v['valor']) || !is_numeric($v['valor'])) {
                return ['division' => 'Valor de divisi&oacute;n inv&aacute;lido para un participante.'];
            }
            if (!in_array((int) $v['user_id'], $miembrosIds)) {
                return ['division' => 'Un usuario de la divisi&oacute;n no pertenece al grupo.'];
            }
            $valorNumerico = (float) $v['valor'];
            if ($valorNumerico < 0) {
                return ['division' => 'No se permiten valores negativos.'];
            }
            $totalValor += $valorNumerico;
        }

        if ($divisionTipo === 'monto_fijo' && abs($monto - $totalValor) > 0.01) {
            return ['division' => 'La suma de montos fijos no coincide con el total del gastito.'];
        }
        if ($divisionTipo === 'porcentaje' && abs($totalValor - 100) > 0.1) {
            return ['division' => 'Los porcentajes deben sumar 100%.'];
        }

        return null;
    }
}
