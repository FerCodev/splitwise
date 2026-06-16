<?php

namespace App\Controllers;

use App\Models\Gasto;
use App\Models\Pago;
use App\Models\Grupo;
use App\Models\GrupoMiembro;
use App\Models\UserPaymentMethod;
use App\Services\GroupPermission;

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
        $usuarios = $userModel->select('id, name, email')->where('id !=', session()->get('userId'))->orderBy('name', 'ASC')->findAll();

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

        return redirect()->to('/grupos')->with('success', 'Grupo creado correctamente.');
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

        $permisos = GroupPermission::getAll($acceso['rol'], $acceso['grupo']['estado'], $acceso['userId']);

        $usuariosDisponibles = $permisos['puede_agregar_miembro']
            ? $grupoModel->getUsuariosDisponibles($id)
            : [];

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
            'totalPagado' => $totalPagado,
            'usuariosDisponibles' => $usuariosDisponibles,
        ]);
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
                return redirect()->to("/grupos/$id/editar")->with('error', 'No se puede liquidar el grupo porque hay deudas pendientes.');
            }
        }

        $grupoModel = new Grupo();
        $grupoModel->update($id, ['estado' => $nuevoEstado]);

        $mensajes = [
            'cerrado' => 'Grupo cerrado correctamente.',
            'activo' => 'Grupo reabierto correctamente.',
            'liquidado' => 'Grupo liquidado correctamente.',
        ];

        return redirect()->to("/grupos/$id/editar")->with('success', $mensajes[$nuevoEstado] ?? 'Estado actualizado.');
    }

    public function edit(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        $errorPermiso = GroupPermission::check($acceso['rol'], $acceso['grupo']['estado'], 'grupo_edit');
        if ($errorPermiso) {
            return redirect()->to("/grupos/$id")->with('error', $errorPermiso);
        }

        $grupoModel = new Grupo();
        $gastoModel = new \App\Models\Gasto();
        $deudas = $gastoModel->getDeudasByGrupo($id);
        $miembros = $grupoModel->getMiembros($id);
        $permisos = GroupPermission::getAll($acceso['rol'], $acceso['grupo']['estado'], $acceso['userId']);
        $usuariosDisponibles = $permisos['puede_agregar_miembro']
            ? $grupoModel->getUsuariosDisponibles($id)
            : [];

        return view('grupos/form', [
            'grupo' => $acceso['grupo'],
            'deudas' => $deudas,
            'miembros' => $miembros,
            'permisos' => $permisos,
            'usuariosDisponibles' => $usuariosDisponibles,
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

        return redirect()->to("/grupos/$id/editar")->with('success', 'Grupo actualizado correctamente.');
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

        return redirect()->to('/grupos')->with('success', 'Grupo eliminado correctamente.');
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
            return redirect()->to("/grupos/$id/editar")->with('error', 'Usuario inválido.');
        }

        $userModel = new \App\Models\User();
        if (!$userModel->find($userId)) {
            return redirect()->to("/grupos/$id/editar")->with('error', 'Usuario no encontrado.');
        }

        $grupoModel = new Grupo();
        if ($grupoModel->isMiembro($id, $userId)) {
            return redirect()->to("/grupos/$id/editar")->with('error', 'El usuario ya pertenece al grupo.');
        }

        $miembroModel = new GrupoMiembro();
        $miembroModel->insert([
            'grupo_id' => $id,
            'user_id' => $userId,
            'rol' => 'member',
        ]);

        return redirect()->to("/grupos/$id/editar")->with('success', 'Miembro agregado correctamente.');
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
            return redirect()->to("/grupos/$id/editar")->with('error', 'No podés eliminarte a vos mismo del grupo.');
        }

        $grupoModel = new Grupo();

        if (!$grupoModel->isMiembro($id, $userId)) {
            return redirect()->to("/grupos/$id/editar")->with('error', 'El usuario no pertenece al grupo.');
        }

        $rol = $grupoModel->getUserRol($id, $userId);
        $tieneMovimientos = $grupoModel->miembroTieneMovimientos($id, $userId);
        $totalAdmins = $grupoModel->countAdmins($id);

        $error = Grupo::puedeQuitarMiembro($totalAdmins, $rol, $tieneMovimientos);
        if ($error) {
            return redirect()->to("/grupos/$id/editar")->with('error', $error);
        }

        $miembroModel = new GrupoMiembro();
        $miembroModel->where('grupo_id', $id)
            ->where('user_id', $userId)
            ->delete();

        return redirect()->to("/grupos/$id/editar")->with('success', 'Miembro quitado correctamente.');
    }
}
