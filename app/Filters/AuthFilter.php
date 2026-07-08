<?php

namespace App\Filters;

use App\Models\User;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Tu sesi&oacute;n venci&oacute;. Inici&aacute; sesi&oacute;n nuevamente.');
        }
        $uri = $request->getUri();
        $path = $uri ? trim($uri->getPath(), '/') : '';
        $isUsernameConfirmation = $path === 'perfil/confirmar-usuario'
            || str_ends_with($path, '/perfil/confirmar-usuario');
        $isLogout = $path === 'logout' || str_ends_with($path, '/logout');
        if (!$isUsernameConfirmation && !$isLogout) {
            $confirmed = session()->get('usernameConfirmed');
            if ($confirmed === null) {
                $user = (new User())->find((int) session()->get('userId'));
                $confirmed = !empty($user['username_confirmed_at']);
                session()->set('usernameConfirmed', $confirmed);
            }
            if (!$confirmed) return redirect()->to('/perfil/confirmar-usuario');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
