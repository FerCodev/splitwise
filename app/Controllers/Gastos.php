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

        if ($this->request->getGet('partial')) {
            return view('gastos/_items', [
                'gastos' => $gastos,
                'filters' => $filters,
            ]);
        }

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

    private function normalizarMonto(): void
    {
        $raw = $this->request->getPost('monto');
        $visual = $this->request->getPost('monto_visual');

        // Si monto hidden viene vacio, usar monto_visual como fallback
        $candidato = $raw;
        $deVisual = false;
        if ($candidato === null || $candidato === '') {
            $candidato = $visual;
            $deVisual = true;
        }
        if ($candidato === null || $candidato === '') {
            return;
        }

        $limpio = $candidato;

        // Si tiene coma, es formato argentino: 1.999,87 -> 1999.87
        if (str_contains($limpio, ',')) {
            $limpio = str_replace('.', '', $limpio);
            $limpio = str_replace(',', '.', $limpio);
        } elseif ($deVisual) {
            // Viene de monto_visual sin coma: punto es separador de miles
            $limpio = str_replace('.', '', $limpio);
        }
        // Si viene de monto hidden sin coma, ya esta normalizado o es invalido

        if (is_numeric($limpio)) {
            $this->request->setGlobal('post', array_merge($this->request->getPost() ?: [], ['monto' => $limpio]));
        }
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
                return redirect()->back()->withInput()->with('error', 'Error al procesar el recibo. Verific&aacute; el formato y tama&ntilde;o.');
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
                throw new \RuntimeException('Error al crear el gasto.');
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
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

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

        $reciboData = $this->procesarRecibo();
        if ($reciboData === false) {
            return redirect()->back()->withInput()->with('error', 'Error al procesar el recibo. Verific&aacute; el formato y tama&ntilde;o.');
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
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el gasto. Intentalo de nuevo.');
        }

        return redirect()->to('/grupos/' . $grupoIdOriginal)->with('success', 'Gasto actualizado correctamente.');
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

        // Eliminar archivo de recibo si existe
        if (!empty($gasto['recibo_path'])) {
            $path = WRITEPATH . $gasto['recibo_path'];
            if (file_exists($path)) {
                unlink($path);
            }
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

        $year = date('Y');
        $month = date('m');
        $dir = WRITEPATH . 'uploads/recibos/' . $year . '/' . $month;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $extension = $file->getExtension();
        $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
        $relativePath = 'uploads/recibos/' . $year . '/' . $month . '/' . $safeName;

        $file->move($dir, $safeName);

        return [
            'recibo_path' => $relativePath,
            'recibo_nombre' => $file->getClientName(),
            'recibo_mime' => $file->getMimeType(),
            'recibo_size' => $file->getSize(),
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
            return redirect()->to('/gastos')->with('error', 'Gasto no encontrado.');
        }

        $userId = session()->get('userId');
        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($gasto['grupo_id']);
        if (!$grupo || !$grupoModel->isMiembro($gasto['grupo_id'], $userId)) {
            return redirect()->to('/gastos')->with('error', 'No tenés acceso a este gasto.');
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

        return redirect()->back()->with('success', 'Recibo eliminado correctamente.');
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
            return ['division' => 'La suma de montos fijos no coincide con el total del gasto.'];
        }
        if ($divisionTipo === 'porcentaje' && abs($totalValor - 100) > 0.1) {
            return ['division' => 'Los porcentajes deben sumar 100%.'];
        }

        return null;
    }
}
