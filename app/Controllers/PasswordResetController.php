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

        $response = redirect()->back()->with('success', $mensajeGenerico);

        if (ENVIRONMENT === 'development') {
            $response->with('dev_reset_link', $link);
        }

        if (PasswordReset::smtpConfigurado()) {
            $enviado = PasswordReset::enviarEmail(
                $user['email'],
                'Recuperación de contraseña - SplitWise',
                "Hola {$user['name']},\n\n"
                . "Recibimos una solicitud para restablecer tu contraseña.\n\n"
                . "Hacé clic en el siguiente enlace para crear una nueva contraseña:\n"
                . "{$link}\n\n"
                . "Este enlace expira en 60 minutos.\n\n"
                . "Si no solicitaste este cambio, ignorá este mensaje.\n\n"
                . "— SplitWise"
            );

            if (!$enviado) {
                log_message('error', 'Fallo envio de email de recuperacion');
            }
        } else {
            log_message('info', 'SMTP no configurado. Email de recuperacion no enviado.');
        }

        return $response;
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

    public function cambiarPassword(string $token = '')
    {
        $password = $this->request->getPost('password');
        $confirm = $this->request->getPost('password_confirm');

        if (empty($token) || empty($password) || empty($confirm)) {
            return redirect()->back()->with('error', 'Completá todos los campos.');
        }

        if (strlen($password) < 8) {
            return redirect()->back()->with('error', 'La contraseña debe tener al menos 8 caracteres.');
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

        // Aviso opcional de cambio de contraseña por email (no bloqueante)
        $updatedUser = $userModel->find((int) $row['user_id']);
        if ($updatedUser && PasswordReset::smtpConfigurado()) {
            PasswordReset::enviarEmail(
                $updatedUser['email'],
                'Tu contraseña fue cambiada - SplitWise',
                "Hola {$updatedUser['name']},\n\n"
                . "Tu contraseña de SplitWise fue cambiada correctamente.\n\n"
                . "Si no realizaste este cambio, contactá al administrador del sistema.\n\n"
                . "— SplitWise"
            );
        }

        return redirect()->to('/login')->with('success', 'Contraseña actualizada correctamente. Iniciá sesión con tu nueva contraseña.');
    }
}
