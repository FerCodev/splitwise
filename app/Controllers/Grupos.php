<?php

namespace App\Controllers;

use App\Models\Gasto;
use App\Models\Pago;
use App\Models\Grupo;
use App\Models\GrupoMiembro;

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
        return view('grupos/form');
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

        return view('grupos/show', [
            'grupo' => $acceso['grupo'],
            'rol' => $acceso['rol'],
            'miembros' => $miembros,
            'gastos' => $gastos,
            'balance' => $balance,
            'deudas' => $deudas,
            'totalGastado' => $totalGastado,
            'pagos' => $pagos,
            'totalPagado' => $totalPagado,
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

        $miembros = $grupoModel->getMiembros($id);
        $balance = $gastoModel->getBalanceByGrupo($id);
        $deudas = $gastoModel->getDeudasByGrupo($id);
        $totalGastado = $grupoModel->getTotalGastado($id);
        $totalPagado = $pagoModel->getTotalPagadoByGrupo($id);

        return view('grupos/balance', [
            'grupo' => $acceso['grupo'],
            'rol' => $acceso['rol'],
            'miembros' => $miembros,
            'balance' => $balance,
            'deudas' => $deudas,
            'totalGastado' => $totalGastado,
            'totalPagado' => $totalPagado,
        ]);
    }

    public function cambiarEstado(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        if ($acceso['rol'] !== 'admin') {
            return redirect()->to("/grupos/$id")->with('error', 'Solo los administradores pueden cambiar el estado del grupo.');
        }

        $nuevoEstado = $this->request->getPost('estado');
        $estadoActual = $acceso['grupo']['estado'] ?? 'activo';

        if (!Grupo::transicionValida($estadoActual, $nuevoEstado)) {
            return redirect()->to("/grupos/$id")->with('error', "No se puede cambiar de \"$estadoActual\" a \"$nuevoEstado\".");
        }

        if ($nuevoEstado === 'liquidado') {
            $gastoModel = new Gasto();
            $deudas = $gastoModel->getDeudasByGrupo($id);
            if (!empty($deudas)) {
                return redirect()->to("/grupos/$id")->with('error', 'No se puede liquidar el grupo porque hay deudas pendientes.');
            }
        }

        $grupoModel = new Grupo();
        $grupoModel->update($id, ['estado' => $nuevoEstado]);

        $mensajes = [
            'cerrado' => 'Grupo cerrado correctamente.',
            'activo' => 'Grupo reabierto correctamente.',
            'liquidado' => 'Grupo liquidado correctamente.',
        ];

        return redirect()->to("/grupos/$id")->with('success', $mensajes[$nuevoEstado] ?? 'Estado actualizado.');
    }

    public function edit(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        if ($acceso['rol'] !== 'admin') {
            return redirect()->to("/grupos/$id")->with('error', 'Solo los administradores pueden editar el grupo.');
        }

        $bloqueo = Grupo::restriccionEstado($acceso['grupo']['estado'], 'grupo_edit');
        if ($bloqueo) {
            return redirect()->to("/grupos/$id")->with('error', $bloqueo);
        }

        $gastoModel = new \App\Models\Gasto();
        $deudas = $gastoModel->getDeudasByGrupo($id);

        return view('grupos/form', [
            'grupo' => $acceso['grupo'],
            'deudas' => $deudas,
        ]);
    }

    public function update(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        if ($acceso['rol'] !== 'admin') {
            return redirect()->to("/grupos/$id")->with('error', 'Solo los administradores pueden editar el grupo.');
        }

        $bloqueo = Grupo::restriccionEstado($acceso['grupo']['estado'], 'grupo_edit');
        if ($bloqueo) {
            return redirect()->to("/grupos/$id")->with('error', $bloqueo);
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

        return redirect()->to('/grupos')->with('success', 'Grupo actualizado correctamente.');
    }

    public function delete(int $id)
    {
        $acceso = $this->verificarAcceso($id);

        if ($acceso === null) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado o no tenés acceso.');
        }

        if ($acceso['rol'] !== 'admin') {
            return redirect()->to("/grupos/$id")->with('error', 'Solo los administradores pueden eliminar el grupo.');
        }

        $bloqueo = Grupo::restriccionEstado($acceso['grupo']['estado'], 'grupo_delete');
        if ($bloqueo) {
            return redirect()->to("/grupos/$id")->with('error', $bloqueo);
        }

        $grupoModel = new Grupo();
        $grupoModel->delete($id);

        return redirect()->to('/grupos')->with('success', 'Grupo eliminado correctamente.');
    }
}
