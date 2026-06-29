<?php

namespace App\Controllers;

use App\Models\Gasto;
use App\Models\Pago;
use App\Models\Grupo;
use App\Models\Categoria;
use App\Models\GrupoMiembro;
use App\Models\UserPaymentMethod;
use App\Models\UserGroupColorOverride;
use App\Services\GroupPermission;
use App\Services\UiFeedbackResolver;
use App\Services\UserColor;

class Grupos extends BaseController
{
    private function verificarAcceso(int $id): ?array
    {
        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($id);

        if (!$grupo) {
            return null;
        }

        $userId = session()->get('userId');

        if (!$grupoModel->isMiembro($id, $userId)) {
            return null;
        }

        $rol = $grupoModel->getUserRol($id, $userId);

        return [
            'grupo' => $grupo,
            'rol' => $rol,
            'userId' => $userId,
        ];
    }

    public function index()
    {
        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser(session()->get('userId'));

        return view('grupos/index', ['grupos' => $grupos]);
    }

    public function new()
    {
        $userModel = new \App\Models\User();
        $usuarios = $userModel->select('id, name, email')->where('id !=', session()->get('userId'))->where('role !=', 'admin')->orderBy('name', 'ASC')->findAll();

        return view('grupos/form', ['usuarios' => $usuarios]);
    }

    public function create()
    {
        $rules = [
            'nombre' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nombre = $this->request->getPost('nombre');
        $userId = session()->get('userId');

        $grupoModel = new Grupo();

        if ($grupoModel->existsByNameForUser($nombre, $userId)) {
            return redirect()->back()->withInput()->with('errors', ['nombre' => 'Ya tenés un grupo con ese nombre.']);
        }

        $miembrosPost = $this->request->getPost('miembros');
        $miembrosValidos = [];

        if (is_array($miembrosPost)) {
            $miembrosIds = array_unique(array_map('intval', $miembrosPost));
            $miembrosIds = array_values(array_filter($miembrosIds, fn($mid) => $mid > 0));

            if (!empty($miembrosIds)) {
                $userModel = new \App\Models\User();
                $existentes = $userModel->select('id')->whereIn('id', $miembrosIds)->findAll();
                $idsValidos = array_map('intval', array_column($existentes, 'id'));

                $invalidos = array_diff($miembrosIds, $idsValidos);
                if (!empty($invalidos)) {
                    return redirect()->back()->withInput()->with('errors', ['miembros' => 'Uno de los usuarios seleccionados no existe.']);
                }

                $miembrosValidos = $idsValidos;
            }
        }

        $grupoId = $grupoModel->insert([
            'nombre' => $nombre,
            'descripcion' => $this->request->getPost('descripcion'),
            'created_by' => $userId,
        ]);

        $miembroModel = new GrupoMiembro();
        $miembroModel->insert([
            'grupo_id' => $grupoId,
            'user_id' => $userId,
            'rol' => 'admin',
        ]);

        foreach ($miembrosValidos as $mid) {
            if ($mid !== $userId) {
                $miembroModel->insert([
                    'grupo_id' => $grupoId,
                    'user_id' => $mid,
                    'rol' => 'member',
                ]);
            }
        }

        return redirect()->to('/grupos')->with('success', UiFeedbackResolver::message('groups.create.completed', ['group_name' => $nombre], 'Grupo creado correctamente.'));
    }

    public function show(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $grupoModel = new Grupo();
        $miembros = $grupoModel->getMiembros($id);

        $gastoModel = new Gasto();
        $gastos = $gastoModel->getGastosByGrupo($id);
        $balance = $gastoModel->getBalanceByGrupo($id);
        $deudas = $gastoModel->getDeudasByGrupo($id);
        $totalGastado = $grupoModel->getTotalGastado($id);

        $pagoModel = new Pago();
        $pagos = $pagoModel->getPagosByGrupo($id);
        $totalPagado = $pagoModel->getTotalPagadoByGrupo($id);
        $movimientoFilters = $this->getMovimientoFilters($acceso['grupo'], $gastos, $pagos);
        $movimientos = $this->buildMovimientosGrupo($gastos, $pagos, $movimientoFilters);
        $categorias = model(Categoria::class)->getActivas();

        $permisos = GroupPermission::getAll($acceso['rol'], $acceso['grupo']['estado'], $acceso['userId']);

        $usuariosDisponibles = $permisos['puede_agregar_miembro']
            ? $grupoModel->getUsuariosDisponibles($id)
            : [];

        $colorMap = $this->buildColorMapForGrupo($acceso['userId'], $id, $miembros, $movimientos);

        return view('grupos/show', [
            'grupo' => $acceso['grupo'],
            'rol' => $acceso['rol'],
            'permisos' => $permisos,
            'miembros' => $miembros,
            'gastos' => $gastos,
            'balance' => $balance,
            'deudas' => $deudas,
            'totalGastado' => $totalGastado,
            'pagos' => $pagos,
            'movimientos' => $movimientos,
            'movimientoFilters' => $movimientoFilters,
            'categorias' => $categorias,
            'totalPagado' => $totalPagado,
            'usuariosDisponibles' => $usuariosDisponibles,
            'colorMap' => $colorMap,
        ]);
    }

    /**
     * Resuelve un mapa [userId => colorKey] para todos los miembros del
     * grupo, mas las personas que aparecen en movimientos pero no son
     * miembros (caso limite: gastos viejos tras quitar un miembro).
     *
     * Tambien devuelve un mapa [movimientoId => colorKey] para que la
     * vista pueda pintar cada tarjeta con el color del pagador sin
     * recalcular. Los pagos conservan color reservado (no aparecen en
     * el mapa, la vista usa el reserved correspondiente).
     *
     * @return array{miembros: array<int,string>, movimientos: array<int,string>}
     */
    private function buildColorMapForGrupo(int $viewerId, int $grupoId, array $miembros, array $movimientos): array
    {
        $overrideModel = new UserGroupColorOverride();
        $overrides = $overrideModel->getOverridesForGroup($viewerId, $grupoId);

        $targetIds = [];
        foreach ($miembros as $m) {
            $targetIds[(int) $m['user_id']] = true;
        }
        foreach ($movimientos as $m) {
            if (($m['tipo'] ?? '') === 'gasto') {
                $targetIds[(int) ($m['persona_id'] ?? 0)] = true;
            }
        }
        $targetIds = array_filter(array_keys($targetIds));

        $globalMap = [];
        if (!empty($targetIds)) {
            $rows = db_connect()->table('users')
                ->select('id, color')
                ->whereIn('id', $targetIds)
                ->get()
                ->getResultArray();
            foreach ($rows as $row) {
                $globalMap[(int) $row['id']] = (string) ($row['color'] ?? UserColor::DEFAULT_KEY);
            }
        }

        $memberColors = UserColor::resolveMap($overrides, $globalMap);

        $movColors = [];
        foreach ($movimientos as $m) {
            if (($m['tipo'] ?? '') !== 'gasto') {
                continue;
            }
            $tid = (int) ($m['persona_id'] ?? 0);
            if ($tid <= 0) {
                continue;
            }
            $movColors[(int) $m['id']] = $memberColors[$tid] ?? UserColor::DEFAULT_KEY;
        }

        return [
            'miembros'    => $memberColors,
            'movimientos' => $movColors,
        ];
    }

    public function actualizarColorMiembro(int $grupoId, int $targetId)
    {
        $acceso = $this->verificarAcceso($grupoId);
        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        // El target debe ser miembro actual del grupo.
        $grupoModel = new Grupo();
        if (! $grupoModel->isMiembro($grupoId, $targetId)) {
            return redirect()->to("/grupos/{$grupoId}")->with('error', 'La persona seleccionada ya no pertenece al grupo.');
        }

        $action        = (string) $this->request->getPost('action');
        $rawColor      = $this->request->getPost('color');
        $overrideModel = new UserGroupColorOverride();
        $viewerId      = (int) $acceso['userId'];

        // Toda la decision es pura; vive en UserColor::classifyOverrideSubmit.
        $decision = UserColor::classifyOverrideSubmit($action, $rawColor);

        // La configuracion de colores vive en la pantalla de edicion
        // del grupo, no en el detalle principal.
        $redirectTo = "/grupos/{$grupoId}/editar#colores";
        $respond = function (string $type, string $message) use ($grupoId, $redirectTo) {
            if ($this->request->isAJAX()) {
                session()->setFlashdata($type, $message);

                return $this->response->setJSON([
                    'ok' => $type === 'success',
                    'redirect' => base_url("grupos/{$grupoId}/editar") . '#colores',
                ]);
            }

            return redirect()->to($redirectTo)->with($type, $message);
        };

        if ($decision['action'] === UserColor::SUBMIT_RESET) {
            $overrideModel->clearOverride($viewerId, $grupoId, $targetId);
            return $respond('success', 'Color restaurado al valor global.');
        }

        if ($decision['action'] === UserColor::SUBMIT_ERROR) {
            $message = ($decision['reason'] ?? '') === UserColor::REASON_EMPTY
                ? 'Debés seleccionar un color o usar el botón "Global" para volver al valor por defecto.'
                : 'Color inválido.';
            return $respond('error', $message);
        }

        // SUBMIT_SET
        try {
            $overrideModel->setOverride($viewerId, $grupoId, $targetId, (string) $decision['colorKey']);
        } catch (\InvalidArgumentException $e) {
            return $respond('error', $e->getMessage());
        }

        return $respond('success', 'Color guardado para este grupo.');
    }

    private function getMovimientoFilters(array $grupo, array $gastos = [], array $pagos = []): array
    {
        $fechaDesde = trim((string) $this->request->getGet('fecha_desde'));
        $fechaHasta = trim((string) $this->request->getGet('fecha_hasta'));

        if (!$this->request->getGet('fecha_desde') && !$this->request->getGet('fecha_hasta')) {
            [$fechaDesde, $fechaHasta] = $this->getDefaultMovimientoDateRange($grupo, $gastos, $pagos);
        }

        return [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
            'categoria_id' => trim((string) $this->request->getGet('categoria_id')),
            'persona_id' => trim((string) $this->request->getGet('persona_id')),
            'q' => trim((string) $this->request->getGet('q')),
        ];
    }

    private function getDefaultMovimientoDateRange(array $grupo, array $gastos, array $pagos): array
    {
        $fechas = [];

        foreach ($gastos as $gasto) {
            if (!empty($gasto['fecha'])) {
                $fechas[] = $gasto['fecha'];
            }
        }

        foreach ($pagos as $pago) {
            if (!empty($pago['fecha'])) {
                $fechas[] = $pago['fecha'];
            }
        }

        sort($fechas);
        $fechaBase = $fechas[0] ?? ($grupo['created_at'] ?? date('Y-m-d'));

        try {
            $mes = new \DateTimeImmutable($fechaBase);
        } catch (\Exception $e) {
            $mes = new \DateTimeImmutable();
        }

        return [
            $mes->modify('first day of this month')->format('Y-m-d'),
            $mes->modify('last day of this month')->format('Y-m-d'),
        ];
    }

    private function buildMovimientosGrupo(array $gastos, array $pagos, array $filters): array
    {
        $movimientos = [];

        foreach ($gastos as $g) {
            $movimientos[] = [
                'tipo' => 'gasto',
                'fecha' => $g['fecha'],
                'descripcion' => $g['descripcion'],
                'monto' => $g['monto'],
                'persona' => $g['pagador_nombre'],
                'persona_id' => (int) $g['pagador_id'],
                'id' => $g['id'],
                'categoria_id' => (int) ($g['categoria_id'] ?? 0),
                'categoria_nombre' => $g['categoria_nombre'] ?? null,
                'created_at' => $g['created_at'] ?? $g['fecha'],
            ];
        }

        foreach ($pagos as $p) {
            $movimientos[] = [
                'tipo' => 'pago',
                'fecha' => $p['fecha'],
                'descripcion' => $p['descripcion'] ?: 'Pago',
                'monto' => $p['monto'],
                'persona' => $p['pagador_nombre'] . ' pagó a ' . $p['receptor_nombre'],
                'persona_id' => (int) $p['pagador_id'],
                'id' => $p['id'],
                'categoria_id' => null,
                'categoria_nombre' => null,
                'created_at' => $p['created_at'] ?? $p['fecha'],
            ];
        }

        $q = mb_strtolower($filters['q'] ?? '');
        $movimientos = array_values(array_filter($movimientos, static function (array $m) use ($filters, $q): bool {
            if (!empty($filters['fecha_desde']) && $m['fecha'] < $filters['fecha_desde']) {
                return false;
            }
            if (!empty($filters['fecha_hasta']) && $m['fecha'] > $filters['fecha_hasta']) {
                return false;
            }
            if (!empty($filters['categoria_id']) && (int) $m['categoria_id'] !== (int) $filters['categoria_id']) {
                return false;
            }
            if (!empty($filters['persona_id']) && (int) $m['persona_id'] !== (int) $filters['persona_id']) {
                return false;
            }
            if ($q !== '') {
                $texto = mb_strtolower(($m['descripcion'] ?? '') . ' ' . ($m['persona'] ?? '') . ' ' . ($m['categoria_nombre'] ?? ''));
                if (!str_contains($texto, $q)) {
                    return false;
                }
            }

            return true;
        }));

        usort($movimientos, static fn($a, $b) => strcmp($b['fecha'] . ' ' . $b['created_at'], $a['fecha'] . ' ' . $a['created_at']));

        return $movimientos;
    }

    public function balance(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $gastoModel = new Gasto();
        $pagoModel = new Pago();
        $grupoModel = new Grupo();
        $medioModel = new UserPaymentMethod();

        $miembros = $grupoModel->getMiembros($id);
        $balance = $gastoModel->getBalanceByGrupo($id);
        $deudas = $gastoModel->getDeudasByGrupo($id);
        $totalGastado = $grupoModel->getTotalGastado($id);
        $totalPagado = $pagoModel->getTotalPagadoByGrupo($id);
        $gastosPorCategoria = $gastoModel->getMontosPorCategoria($id);

        $mediosPorAcreedor = [];
        foreach ($deudas as $d) {
            $acreedorId = (int) $d['acreedor_id'];
            if (!isset($mediosPorAcreedor[$acreedorId])) {
                $medios = $medioModel->getActivosByUser($acreedorId);
                $mediosPorAcreedor[$acreedorId] = $medios;
            }
        }

        return view('grupos/balance', [
            'grupo' => $acceso['grupo'],
            'rol' => $acceso['rol'],
            'miembros' => $miembros,
            'balance' => $balance,
            'deudas' => $deudas,
            'totalGastado' => $totalGastado,
            'totalPagado' => $totalPagado,
            'gastosPorCategoria' => $gastosPorCategoria,
            'mediosPorAcreedor' => $mediosPorAcreedor,
        ]);
    }

    public function cambiarEstado(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $errorPermiso = GroupPermission::check($acceso['rol'], $acceso['grupo']['estado'], 'grupo_estado');
        if ($errorPermiso) {
            return redirect()->to("/grupos/$id/editar")->with('error', $errorPermiso);
        }

        $nuevoEstado = $this->request->getPost('estado');
        $estadoActual = $acceso['grupo']['estado'] ?? 'activo';

        if (!Grupo::transicionValida($estadoActual, $nuevoEstado)) {
            return redirect()->to("/grupos/$id/editar")->with('error', "No se puede cambiar de \"$estadoActual\" a \"$nuevoEstado\".");
        }

        if ($nuevoEstado === 'liquidado') {
            $gastoModel = new Gasto();
            $deudas = $gastoModel->getDeudasByGrupo($id);
            if (!empty($deudas)) {
                return redirect()->to("/grupos/$id/editar")->with('error', UiFeedbackResolver::message('groups.liquidate.failed', [], 'No se puede liquidar el grupo porque hay deudas pendientes.'));
            }
        }

        $grupoModel = new Grupo();
        $grupoModel->update($id, ['estado' => $nuevoEstado]);

        $actionKey = match ($nuevoEstado) {
            'cerrado' => 'groups.close.completed',
            'activo' => 'groups.reopen.completed',
            'liquidado' => 'groups.liquidate.completed',
            default => null,
        };

        $fallback = match ($nuevoEstado) {
            'cerrado' => 'Grupo cerrado correctamente.',
            'activo' => 'Grupo reabierto correctamente.',
            'liquidado' => 'Grupo liquidado correctamente.',
            default => 'Estado actualizado.',
        };

        if ($actionKey === null) {
            return redirect()->to("/grupos/$id/editar")->with('success', $fallback);
        }

        return redirect()->to("/grupos/$id/editar")->with('success', UiFeedbackResolver::message($actionKey, [], $fallback));
    }

    public function edit(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $grupoModel = new Grupo();
        $gastoModel = new \App\Models\Gasto();
        $deudas = $gastoModel->getDeudasByGrupo($id);
        $miembros = $grupoModel->getMiembros($id);
        $permisos = GroupPermission::getAll($acceso['rol'], $acceso['grupo']['estado'], $acceso['userId']);
        $usuariosDisponibles = $permisos['puede_agregar_miembro']
            ? $grupoModel->getUsuariosDisponibles($id)
            : [];

        $colorMap = $this->buildColorMapForGrupo($acceso['userId'], $id, $miembros, []);

        return view('grupos/form', [
            'grupo' => $acceso['grupo'],
            'rol' => $acceso['rol'],
            'deudas' => $deudas,
            'miembros' => $miembros,
            'permisos' => $permisos,
            'usuariosDisponibles' => $usuariosDisponibles,
            'colorMap' => $colorMap,
            'colorPalette' => UserColor::PALETTE,
        ]);
    }

    public function update(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $errorPermiso = GroupPermission::check($acceso['rol'], $acceso['grupo']['estado'], 'grupo_edit');
        if ($errorPermiso) {
            return redirect()->to("/grupos/$id/editar")->with('error', $errorPermiso);
        }

        $rules = [
            'nombre' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $nombre = $this->request->getPost('nombre');
        $userId = session()->get('userId');

        $grupoModel = new Grupo();

        if ($grupoModel->existsByNameForUser($nombre, $userId, $id)) {
            return redirect()->back()->withInput()->with('errors', ['nombre' => 'Ya tenés un grupo con ese nombre.']);
        }

        $grupoModel->update($id, [
            'nombre' => $nombre,
            'descripcion' => $this->request->getPost('descripcion'),
        ]);

        return redirect()->to("/grupos/$id/editar")->with('success', UiFeedbackResolver::message('groups.update.completed', ['group_name' => $nombre], 'Grupo actualizado correctamente.'));
    }

    public function delete(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $errorPermiso = GroupPermission::check($acceso['rol'], $acceso['grupo']['estado'], 'grupo_delete');
        if ($errorPermiso) {
            return redirect()->to("/grupos/$id/editar")->with('error', $errorPermiso);
        }

        $grupoModel = new Grupo();
        $grupoModel->delete($id);

        return redirect()->to('/grupos')->with('success', UiFeedbackResolver::message('groups.delete.completed', [], 'Grupo eliminado correctamente.'));
    }

    public function agregarMiembro(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $errorPermiso = GroupPermission::check($acceso['rol'], $acceso['grupo']['estado'], 'miembro_create');
        if ($errorPermiso) {
            return redirect()->to("/grupos/$id/editar")->with('error', $errorPermiso);
        }

        $userId = (int) $this->request->getPost('user_id');

        if ($userId <= 0) {
            return redirect()->to("/grupos/$id/editar")->with('error', UiFeedbackResolver::message('groups.member.add.failed', ['reason' => 'Usuario inválido.'], 'Usuario inválido.'));
        }

        $userModel = new \App\Models\User();
        $user = $userModel->find($userId);
        if (!$user) {
            return redirect()->to("/grupos/$id/editar")->with('error', UiFeedbackResolver::message('groups.member.add.failed', ['reason' => 'Usuario no encontrado.'], 'Usuario no encontrado.'));
        }

        if (($user['role'] ?? '') === 'admin') {
            return redirect()->to("/grupos/$id/editar")->with('error', UiFeedbackResolver::message('groups.member.add.failed', ['reason' => 'Los administradores globales no pueden ser miembros de grupos.'], 'Los administradores globales no pueden ser miembros de grupos.'));
        }

        $grupoModel = new Grupo();
        if ($grupoModel->isMiembro($id, $userId)) {
            return redirect()->to("/grupos/$id/editar")->with('error', UiFeedbackResolver::message('groups.member.add.failed', ['reason' => 'El usuario ya pertenece al grupo.'], 'El usuario ya pertenece al grupo.'));
        }

        $miembroModel = new GrupoMiembro();
        $miembroModel->insert([
            'grupo_id' => $id,
            'user_id' => $userId,
            'rol' => 'member',
        ]);

        return redirect()->to("/grupos/$id/editar")->with('success', UiFeedbackResolver::message('groups.member.add.completed', [], 'Miembro agregado correctamente.'));
    }

    public function cambiarRol(int $id, int $userId)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $errorPermiso = GroupPermission::check($acceso['rol'], $acceso['grupo']['estado'], 'miembro_role');
        if ($errorPermiso) {
            return redirect()->to("/grupos/$id/editar")->with('error', $errorPermiso);
        }

        $nuevoRol = $this->request->getPost('rol');

        if (!in_array($nuevoRol, ['admin', 'member'], true)) {
            return redirect()->to("/grupos/$id/editar")->with('error', 'Rol inválido.');
        }

        $grupoModel = new Grupo();
        $rolActual = $grupoModel->getUserRol($id, $userId);

        $error = Grupo::puedeCambiarRol($grupoModel->countAdmins($id), $rolActual, $nuevoRol);
        if ($error) {
            return redirect()->to("/grupos/$id/editar")->with('error', $error);
        }

        $miembroModel = new GrupoMiembro();
        $miembroModel->where('grupo_id', $id)
            ->where('user_id', $userId)
            ->set(['rol' => $nuevoRol])
            ->update();

        return redirect()->to("/grupos/$id/editar")->with('success', 'Rol actualizado correctamente.');
    }

    public function quitarMiembro(int $id, int $userId)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $errorPermiso = GroupPermission::check($acceso['rol'], $acceso['grupo']['estado'], 'miembro_delete');
        if ($errorPermiso) {
            return redirect()->to("/grupos/$id/editar")->with('error', $errorPermiso);
        }

        if ($userId === session()->get('userId')) {
            return redirect()->to("/grupos/$id/editar")->with('error', UiFeedbackResolver::message('groups.member.remove.failed', ['reason' => 'No podés eliminarte a vos mismo del grupo.'], 'No podés eliminarte a vos mismo del grupo.'));
        }

        $grupoModel = new Grupo();

        if (!$grupoModel->isMiembro($id, $userId)) {
            return redirect()->to("/grupos/$id/editar")->with('error', UiFeedbackResolver::message('groups.member.remove.failed', ['reason' => 'El usuario no pertenece al grupo.'], 'El usuario no pertenece al grupo.'));
        }

        $rol = $grupoModel->getUserRol($id, $userId);
        $tieneMovimientos = $grupoModel->miembroTieneMovimientos($id, $userId);
        $totalAdmins = $grupoModel->countAdmins($id);

        $error = Grupo::puedeQuitarMiembro($totalAdmins, $rol, $tieneMovimientos);
        if ($error) {
            return redirect()->to("/grupos/$id/editar")->with('error', UiFeedbackResolver::message('groups.member.remove.failed', ['reason' => $error], $error));
        }

        $miembroModel = new GrupoMiembro();
        $miembroModel->where('grupo_id', $id)
            ->where('user_id', $userId)
            ->delete();

        return redirect()->to("/grupos/$id/editar")->with('success', UiFeedbackResolver::message('groups.member.remove.completed', [], 'Miembro quitado correctamente.'));
    }
}
