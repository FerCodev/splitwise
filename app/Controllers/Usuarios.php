<?php

namespace App\Controllers;

use App\Models\User;

class Usuarios extends BaseController
{
    public function index()
    {
        $userModel = new User();
        $users = $userModel->orderBy('name', 'ASC')->findAll();

        return view('usuarios/index', ['users' => $users]);
    }

    public function new()
    {
        return view('usuarios/form');
    }

    public function create()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[3]',
            'role' => 'required|in_list[user,admin]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel = new User();
        $role = $this->request->getPost('role');
        if (!in_array($role, ['user', 'admin'], true)) {
            $role = 'user';
        }
        $userModel->insert([
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => $role,
        ]);

        return redirect()->to('/usuarios')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(int $id)
    {
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/usuarios')->with('error', 'Usuario no encontrado.');
        }

        return view('usuarios/form', ['user' => $user]);
    }

    public function update(int $id)
    {
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/usuarios')->with('error', 'Usuario no encontrado.');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'role' => 'required|in_list[user,admin]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $newRole = $this->request->getPost('role');
        if (!in_array($newRole, ['user', 'admin'], true)) {
            $newRole = 'user';
        }

        if ($user['role'] === 'admin' && $newRole !== 'admin') {
            $adminCount = $userModel->where('role', 'admin')->where('id !=', $id)->countAllResults();
            if ($adminCount === 0) {
                return redirect()->back()->withInput()->with('error', 'No se puede quitar el último administrador del sistema.');
            }
        }

        $data = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role' => $newRole,
        ];

        $userModel->update($id, $data);

        if ((int) $id === (int) session()->get('userId')) {
            session()->set('userRole', $newRole);
        }

        return redirect()->to('/usuarios')->with('success', 'Usuario actualizado correctamente.');
    }

    public function password(int $id)
    {
        $userModel = new User();
        $user = $userModel->find($id);

        if (!$user) {
            return redirect()->to('/usuarios')->with('error', 'Usuario no encontrado.');
        }

        $rules = [
            'password' => 'required|min_length[3]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userModel->update($id, [
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/usuarios')->with('success', 'Contraseña actualizada correctamente.');
    }
}
