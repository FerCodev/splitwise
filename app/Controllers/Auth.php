<?php

namespace App\Controllers;

use App\Models\User;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('login');
    }

    public function doLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = model(User::class)->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            session()->set([
                'isLoggedIn' => true,
                'userId'     => $user['id'],
                'userName'   => $user['name'],
                'userEmail'  => $user['email'],
                'userRole'   => $user['role'] ?? 'user',
            ]);
            return redirect()->to(base_url('dashboard'));
        }

        return redirect()->back()->with('error', 'Credenciales inválidas');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'));
    }
}
