<?php

namespace App\Controllers;

use App\Models\GroupInvitation;
use App\Models\GrupoMiembro;
use App\Models\User;

class Invitaciones extends BaseController
{
    public function registro(string $token = '')
    {
        if (session()->get('isLoggedIn')) return redirect()->to('/dashboard')->with('error', 'Cerrá sesión para aceptar una invitación con una cuenta nueva.');
        $invitation = (new GroupInvitation())->validateToken($token);
        if (!$invitation) return redirect()->to('/login')->with('error', 'La invitación es inválida, fue cancelada o ya expiró.');
        return view('invitaciones/registro', compact('invitation', 'token'));
    }

    public function crearCuenta(string $token = '')
    {
        if (session()->get('isLoggedIn')) return redirect()->to('/dashboard');
        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'password' => 'required|min_length[8]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());

        $model = new GroupInvitation();
        $db = db_connect();
        $db->transBegin();
        try {
            $db->query('SELECT id FROM group_invitations WHERE token_hash = ? FOR UPDATE', [hash('sha256', $token)]);
            $invitation = $model->validateToken($token);
            if (!$invitation) throw new \RuntimeException('La invitación ya no está disponible.');
            if ((new User())->where('email', $invitation['email'])->first()) throw new \RuntimeException('Ya existe una cuenta con el email invitado.');

            $userId = (new User())->insert([
                'name' => trim((string) $this->request->getPost('name')),
                'email' => $invitation['email'],
                'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
                'role' => 'user', 'color' => 'auto',
            ]);
            if (!$userId) throw new \RuntimeException('No se pudo crear la cuenta.');
            if (!(new GrupoMiembro())->insert(['grupo_id' => $invitation['group_id'], 'user_id' => $userId, 'rol' => 'member'])) {
                throw new \RuntimeException('No se pudo agregar la cuenta al grupo.');
            }
            if (!$model->update((int) $invitation['id'], ['status' => 'accepted', 'accepted_by_id' => $userId, 'accepted_at' => date('Y-m-d H:i:s')])) {
                throw new \RuntimeException('No se pudo completar la invitación.');
            }
            if (!$db->transCommit()) throw new \RuntimeException('No se pudo confirmar el registro.');

            session()->set(['isLoggedIn' => true, 'userId' => $userId, 'userName' => trim((string) $this->request->getPost('name')), 'userEmail' => $invitation['email'], 'avatarFilename' => null, 'avatarUpdatedAt' => null, 'userRole' => 'user']);
            return redirect()->to('/grupos/' . $invitation['group_id'])->with('success', 'Cuenta creada. Ya sos miembro de ' . $invitation['group_name'] . '.');
        } catch (\Throwable $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
