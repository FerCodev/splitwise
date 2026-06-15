<?php

namespace App\Controllers;

use App\Models\Gasto;
use App\Models\GastoDivision;
use App\Models\Grupo;
use App\Models\GastoParticipante;
use App\Models\Categoria;
use App\Services\GroupPermission;

class Gastos extends BaseController
{
    public function index()
    {
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));

        $gastoModel = new Gasto();
        $filters = [];

        if ($this->request->getGet('grupo_id')) {
            $filters['grupo_id'] = (int) $this->request->getGet('grupo_id');
        }
        if ($this->request->getGet('pagador_id')) {
            $filters['pagador_id'] = (int) $this->request->getGet('pagador_id');
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

        $gastos = $gastoModel->getGastosWithFilters($filters, 10);
        $pager = $gastoModel->pager;

        return view('gastos/index', [
            'gastos' => $gastos,
            'grupos' => $grupos,
            'filters' => $filters,
            'categorias' => model(Categoria::class)->getActivas(),
            'pager' => $pager,
        ]);
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

        return view('gastos/form', [
            'grupos' => $grupos,
            'grupoId' => $grupoId,
            'miembros' => $miembros,
            'participantesIds' => $participantesIds,
            'pagadorPorDefecto' => session()->get('userId'),
            'categorias' => model(Categoria::class)->getActivas(),
        ]);
    }

    public function create()
    {
        $rules = [
            'descripcion' => 'required|min_length[2]|max_length[255]',
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

        $participantesIds = array_unique(array_map('intval', $participantesIds));
        $porcion = round($monto / count($participantesIds), 2);

        $diferencias = round($monto - ($porcion * count($participantesIds)), 2);

        $gastoModel = new Gasto();
        $categoriaModel = model(Categoria::class);
        $categoriaId = (int) $this->request->getPost('categoria_id');
        $catValida = $categoriaModel->find($categoriaId);
        if ($categoriaId <= 0 || !$catValida || !$catValida['activa']) {
            $categoriaId = $categoriaModel->getOtrosId();
        }

        $divisionTipo = $this->request->getPost('division_tipo') ?: 'igualitario';
        $divisionValores = $this->request->getPost('division_valores') ?? [];

        $tiposValidos = ['igualitario', 'monto_fijo', 'porcentaje', 'partes', 'ajuste'];
        if (!in_array($divisionTipo, $tiposValidos, true)) {
            return redirect()->back()->withInput()->with('errors', ['division' => 'Tipo de divisi&oacute;n inv&aacute;lido.']);
        }

        if ($divisionTipo !== 'igualitario') {
            $valores = array_values($divisionValores);
            $totalValor = array_sum(array_column($valores, 'valor'));

            if (count($valores) !== count($participantesIds)) {
                return redirect()->back()->withInput()->with('errors', ['division' => 'La cantidad de valores de divisi&oacute;n no coincide con los participantes.']);
            }

            $valUserIds = array_map('intval', array_column($valores, 'user_id'));
            $diff = array_merge(
                array_diff($valUserIds, $participantesIds),
                array_diff($participantesIds, $valUserIds)
            );
            if (!empty($diff)) {
                return redirect()->back()->withInput()->with('errors', ['division' => 'Los participantes en la divisi&oacute;n no coinciden con los seleccionados.']);
            }

            foreach ($valores as $v) {
                if (!in_array((int) $v['user_id'], $miembrosIds)) {
                    return redirect()->back()->withInput()->with('errors', ['division' => 'Un usuario de la divisi&oacute;n no pertenece al grupo.']);
                }
            }

            if ($divisionTipo === 'monto_fijo' && abs($monto - $totalValor) > 0.01) {
                return redirect()->back()->withInput()->with('errors', ['division' => 'La suma de montos fijos no coincide con el total del gasto.']);
            }
            if ($divisionTipo === 'porcentaje' && abs($totalValor - 100) > 0.1) {
                return redirect()->back()->withInput()->with('errors', ['division' => 'Los porcentajes deben sumar 100%.']);
            }
            if ($divisionTipo === 'partes' && $totalValor < 1) {
                return redirect()->back()->withInput()->with('errors', ['division' => 'Debe haber al menos 1 parte en total.']);
            }
            if ($divisionTipo === 'ajuste') {
                if (abs($totalValor) > 0.01) {
                    return redirect()->back()->withInput()->with('errors', ['division' => 'Los ajustes deben sumar $0.']);
                }
                $porcionBase = round($monto / count($participantesIds), 2);
                foreach ($valores as $v) {
                    $calc = round($porcionBase + (float) ($v['valor'] ?? 0), 2);
                    if ($calc < 0) {
                        return redirect()->back()->withInput()->with('errors', ['division' => 'Un ajuste gener&oacute; un monto negativo para un participante.']);
                    }
                }
            }
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $gastoId = $gastoModel->insert([
                'grupo_id' => $grupoId,
                'pagador_id' => $pagadorId,
                'descripcion' => $this->request->getPost('descripcion'),
                'monto' => $monto,
                'fecha' => $this->request->getPost('fecha'),
                'categoria_id' => $categoriaId,
                'division_tipo' => $divisionTipo,
            ]);

            if (!$gastoId) {
                throw new \RuntimeException('Error al crear el gasto.');
            }

            $participanteModel = new GastoParticipante();
            foreach ($participantesIds as $i => $pid) {
                $asignado = $porcion;
                if ($i === array_key_last($participantesIds)) {
                    $asignado += $diferencias;
                }
                if (!$participanteModel->insert([
                    'gasto_id' => $gastoId,
                    'user_id' => $pid,
                    'monto_asignado' => round($asignado, 2),
                ])) {
                    throw new \RuntimeException('Error al insertar participante.');
                }
            }

            $valoresMap = [];
            if (!empty($divisionValores) && is_array($divisionValores)) {
                foreach ($divisionValores as $dv) {
                    $valoresMap[(int) $dv['user_id']] = (float) $dv['valor'];
                }
            }
            if (!GastoDivision::generarDivisionesIgualitarias($gastoId, $monto, $participantesIds, $divisionTipo, $valoresMap)) {
                throw new \RuntimeException('Error al insertar divisiones.');
            }

            $db->transCommit();
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Error al crear el gasto. Intentalo de nuevo.');
        }

        return redirect()->to('/grupos/' . $grupoId)->with('success', 'Gasto creado correctamente.');
    }

    public function show(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->select('gastos.*, categorias.nombre as categoria_nombre, users.name as pagador_nombre, grupos.nombre as grupo_nombre')
            ->join('users', 'users.id = gastos.pagador_id')
            ->join('grupos', 'grupos.id = gastos.grupo_id')
            ->join('categorias', 'categorias.id = gastos.categoria_id', 'left')
            ->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');

        if (!$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gasto.');
        }

        $grupo = $grupoModel->find($gasto['grupo_id']);
        $rol = $grupoModel->getUserRol($gasto['grupo_id'], $userId);
        $permisos = GroupPermission::getAll($rol, $grupo['estado'], $userId, (int) $gasto['pagador_id']);

        $participantes = $gastoModel->getParticipantes($id);

        return view('gastos/show', [
            'gasto' => $gasto,
            'rol' => $rol,
            'permisos' => $permisos,
            'participantes' => $participantes,
            'categorias' => model(Categoria::class)->getActivas(),
        ]);
    }

    public function edit(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $grupoModel = new Grupo();
        $userId = session()->get('userId');

        if (!$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gasto.');
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

        return view('gastos/form', [
            'gasto' => $gasto,
            'rol' => $rol,
            'grupos' => $grupos,
            'grupoId' => $gasto['grupo_id'],
            'miembros' => $miembros,
            'participantesIds' => $participantesIds,
            'categorias' => model(Categoria::class)->getActivas(),
        ]);
    }

    public function update(int $id)
    {
        $gastoModel = new Gasto();
        $gastoExistente = $gastoModel->find($id);

        if (!$gastoExistente) {
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $rules = [
            'descripcion' => 'required|min_length[2]|max_length[255]',
            'monto' => 'required|numeric|greater_than[0]',
            'fecha' => 'required|valid_date',
            'participantes' => 'required',
            'categoria_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $monto = (float) $this->request->getPost('monto');
        $participantesIds = $this->request->getPost('participantes');

        if (!is_array($participantesIds) || count($participantesIds) < 1) {
            return redirect()->back()->withInput()->with('errors', ['participantes' => 'Debe seleccionar al menos un participante.']);
        }

        $grupoIdOriginal = (int) $gastoExistente['grupo_id'];

        $grupoModel = new Grupo();
        $userId = session()->get('userId');
        $grupo = $grupoModel->find($grupoIdOriginal);

        if (!$grupo || !$grupoModel->isMiembro($grupoIdOriginal, $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gasto.');
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

        $gastoModel->update($id, [
            'grupo_id' => $grupoIdOriginal,
            'pagador_id' => $pagadorId,
            'descripcion' => $this->request->getPost('descripcion'),
            'monto' => $monto,
            'fecha' => $this->request->getPost('fecha'),
            'categoria_id' => $categoriaId,
        ]);

        $participanteModel = new GastoParticipante();
        $participanteModel->where('gasto_id', $id)->delete();

        $participantesIds = array_unique(array_map('intval', $participantesIds));
        $porcion = round($monto / count($participantesIds), 2);
        $diferencias = round($monto - ($porcion * count($participantesIds)), 2);

        foreach ($participantesIds as $i => $pid) {
            $asignado = $porcion;
            if ($i === array_key_last($participantesIds)) {
                $asignado += $diferencias;
            }
            $participanteModel->insert([
                'gasto_id' => $id,
                'user_id' => $pid,
                'monto_asignado' => round($asignado, 2),
            ]);
        }

        return redirect()->to('/gastos')->with('success', 'Gasto actualizado correctamente.');
    }

    public function delete(int $id)
    {
        $gastoModel = new Gasto();
        $gasto = $gastoModel->find($id);

        if (!$gasto) {
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($gasto['grupo_id']);
        if (!$grupo || !$grupoModel->isMiembro($gasto['grupo_id'], session()->get('userId'))) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gasto.');
        }

        $userId = session()->get('userId');
        $rol = $grupoModel->getUserRol($gasto['grupo_id'], $userId);

        $errorPermiso = GroupPermission::check($rol, $grupo['estado'], 'gasto_delete', $userId, (int) $gasto['pagador_id']);
        if ($errorPermiso) {
            return redirect()->to('/gastos')->with('error', $errorPermiso);
        }

        $gastoModel->delete($id);

        return redirect()->to('/gastos')->with('success', 'Gasto eliminado correctamente.');
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
