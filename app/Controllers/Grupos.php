<?php

namespace App\Controllers;

use App\Models\Grupo;
use App\Models\GrupoMiembro;

class Grupos extends BaseController
{
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

        $grupoModel = new Grupo();
        $grupoId = $grupoModel->insert([
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'created_by' => session()->get('userId'),
        ]);

        $miembroModel = new GrupoMiembro();
        $miembroModel->insert([
            'grupo_id' => $grupoId,
            'user_id' => session()->get('userId'),
            'rol' => 'admin',
        ]);

        return redirect()->to('/grupos')->with('success', 'Grupo creado correctamente.');
    }

    public function edit(int $id)
    {
        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($id);

        if (!$grupo) {
            return redirect()->to('/grupos')->with('error', 'Grupo no encontrado.');
        }

        return view('grupos/form', ['grupo' => $grupo]);
    }

    public function update(int $id)
    {
        $rules = [
            'nombre' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $grupoModel = new Grupo();
        $grupoModel->update($id, [
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
        ]);

        return redirect()->to('/grupos')->with('success', 'Grupo actualizado correctamente.');
    }

    public function delete(int $id)
    {
        $grupoModel = new Grupo();
        $grupoModel->delete($id);

        return redirect()->to('/grupos')->with('success', 'Grupo eliminado correctamente.');
    }
}
