<?php

namespace App\Controllers;

use App\Models\User;

class Perfil extends BaseController
{
    public function index()
    {
        $userId = session()->get('userId');
        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/logout');
        }

        return view('perfil/index', ['user' => $user]);
    }

    public function editarNombre()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = session()->get('userId');
        $userModel = new User();
        $name = $this->request->getPost('name');

        $userModel->update($userId, ['name' => $name]);
        session()->set('userName', $name);

        return redirect()->to('/perfil')->with('success', 'Nombre actualizado correctamente.');
    }

    public function cambiarPassword()
    {
        $rules = [
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]',
            'new_password_confirm' => 'required|matches[new_password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = session()->get('userId');
        $userModel = new User();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->to('/logout');
        }

        $currentPassword = $this->request->getPost('current_password');
        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->back()->with('error', 'La contraseña actual no es correcta.');
        }

        $newPassword = $this->request->getPost('new_password');
        $userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/perfil')->with('success', 'Contraseña actualizada correctamente.');
    }
}
