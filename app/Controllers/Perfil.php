<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\UiFeedbackResolver;
use App\Services\UserColor;

class Perfil extends BaseController
{
    public function index()
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->to('/logout');
        }

        return view('perfil/index', [
            'user'  => $user,
            'palette' => UserColor::PALETTE,
        ]);
    }

    public function editar()
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->to('/logout');
        }

        return view('perfil/editar', [
            'user'    => $user,
            'palette' => UserColor::PALETTE,
        ]);
    }

    public function actualizar()
    {
        $rules = [
            'name'  => 'required|min_length[2]|max_length[255]',
            'email' => 'required|valid_email',
            'color' => 'permit_empty|max_length[32]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = session()->get('userId');
        $userModel = new User();
        $name  = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $color = UserColor::sanitizeInput($this->request->getPost('color'));

        if ($color === null) {
            return redirect()->back()->withInput()->with('error', 'Color inv&aacute;lido. Eleg&iacute; uno de la paleta.');
        }

        $existing = $userModel->where('email', $email)->where('id !=', $userId)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', UiFeedbackResolver::message('profile.update.failed', ['reason' => 'El email ya est&aacute; en uso.'], 'Este email ya est&aacute; en uso por otro usuario.'));
        }

        $userModel->update($userId, [
            'name'  => $name,
            'email' => $email,
            'color' => $color,
        ]);
        session()->set('userName', $name);
        session()->set('userEmail', $email);

        return redirect()->to('/perfil')->with('success', UiFeedbackResolver::message('profile.update.completed', [], 'Perfil actualizado correctamente.'));
    }

    public function password()
    {
        return view('perfil/password');
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
            return redirect()->back()->with('error', UiFeedbackResolver::message('profile.password.change.failed', ['reason' => 'La contrase&ntilde;a actual no es correcta.'], 'La contrase&ntilde;a actual no es correcta.'));
        }

        $newPassword = $this->request->getPost('new_password');
        $userModel->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        return redirect()->to('/perfil')->with('success', UiFeedbackResolver::message('profile.password.change.completed', [], 'Contrase&ntilde;a actualizada correctamente.'));
    }

    private function currentUser(): ?array
    {
        $userId = session()->get('userId');
        $userModel = new User();

        return $userModel->find($userId);
    }
}