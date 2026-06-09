<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\PasswordReset;

class PasswordResetController extends BaseController
{
    public function olvidada()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        return view('password/olvidada');
    }

    public function enviarEnlace()
    {
        $email = $this->request->getPost('email');
        if (!$email) {
            return redirect()->back()->with('error', 'Ingresá tu email.');
        }

        $user = model(User::class)->where('email', $email)->first();

        $mensajeGenerico = 'Si el email está registrado, recibirás un enlace para recuperar tu contraseña.';

        if (!$user) {
            return redirect()->back()->with('success', $mensajeGenerico);
        }

        $resetModel = new PasswordReset();
        $token = $resetModel->generarToken((int) $user['id']);

        $link = base_url("password/reset/{$token}");

        // TODO: En produccion enviar $link por email.
        // En development se muestra en pantalla.
        return redirect()->back()->with('success', $mensajeGenerico)
            ->with('dev_reset_link', $link);
    }

    public function reset(string $token = '')
    {
        if (empty($token)) {
            return redirect()->to('/password/olvidada')->with('error', 'Enlace inválido.');
        }

        if (session()->get('isLoggedIn')) {
            return redirect()->to('/dashboard');
        }

        $resetModel = new PasswordReset();
        $row = $resetModel->validarToken($token);

        if (!$row) {
            return redirect()->to('/password/olvidada')->with('error', 'El enlace es inválido o ya expiró. Solicitá uno nuevo.');
        }

        return view('password/reset', ['token' => $token]);
    }

    public function cambiarPassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirm = $this->request->getPost('password_confirm');

        if (empty($token) || empty($password) || empty($confirm)) {
            return redirect()->back()->with('error', 'Completá todos los campos.');
        }

        if (strlen($password) < 6) {
            return redirect()->back()->with('error', 'La contraseña debe tener al menos 6 caracteres.');
        }

        if ($password !== $confirm) {
            return redirect()->back()->with('error', 'Las contraseñas no coinciden.');
        }

        $resetModel = new PasswordReset();
        $row = $resetModel->validarToken($token);

        if (!$row) {
            return redirect()->to('/password/olvidada')->with('error', 'El enlace es inválido o ya expiró.');
        }

        $userModel = new User();
        $userModel->update((int) $row['user_id'], [
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $resetModel->marcarUsado((int) $row['id']);

        return redirect()->to('/login')->with('success', 'Contraseña actualizada correctamente. Iniciá sesión con tu nueva contraseña.');
    }
}
