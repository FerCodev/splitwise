<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\UiFeedbackResolver;

class Auth extends BaseController
{
    public function login()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('login');
    }

    public function doLogin()
    {
        $identifier = trim((string) $this->request->getPost('identifier'));
        $password = $this->request->getPost('password');

        $normalized = strtolower(ltrim($identifier, '@'));
        $user = str_contains($identifier, '@') && !str_starts_with($identifier, '@')
            ? model(User::class)->where('email', strtolower($identifier))->first()
            : model(User::class)->where('username', $normalized)->first();

        if ($user && password_verify($password, $user['password'])) {
            session()->set([
                'isLoggedIn' => true,
                'userId'     => $user['id'],
                'userName'   => $user['name'],
                'userEmail'  => $user['email'],
                'userUsername' => $user['username'] ?? null,
                'usernameConfirmed' => !empty($user['username_confirmed_at']),
                'avatarFilename' => $user['avatar_filename'] ?? null,
                'avatarUpdatedAt' => $user['avatar_updated_at'] ?? null,
                'userRole'   => $user['role'] ?? 'user',
            ]);
            return redirect()->to('/dashboard');
        }

        return redirect()->back()->with('error', UiFeedbackResolver::message('auth.login.failed', [], 'Credenciales inv&aacute;lidas'));
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
