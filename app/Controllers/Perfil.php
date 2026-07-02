<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AvatarImageProcessor;
use App\Services\AvatarPersistence;
use App\Services\AvatarStorage;
use App\Services\UiFeedbackResolver;
use App\Services\UserColor;
use RuntimeException;
use Throwable;

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

    public function avatarForm()
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->to('/logout');
        }

        return view('perfil/avatar', ['user' => $user]);
    }

    public function avatarUpload()
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->to('/logout');
        }

        $file = $this->request->getFile('avatar');
        if (!$file || $file->getError() !== UPLOAD_ERR_OK || !$file->isValid()) {
            $message = $file && $file->getError() === UPLOAD_ERR_INI_SIZE
                ? 'La imagen no puede superar 5 MB.'
                : 'No se pudo recibir la imagen.';
            return redirect()->back()->with('error', $message);
        }

        $storage = new AvatarStorage();
        try {
            $filename = (new AvatarImageProcessor($storage))->process($file->getTempName(), $file->getSize());
            $now = date('Y-m-d H:i:s');
            $model = new User();
            $db = db_connect();
            (new AvatarPersistence($storage))->replace($user['avatar_filename'] ?? null, $filename, function () use ($model, $db, $user, $filename, $now): void {
                $db->transBegin();
                try {
                    if (!$model->update((int) $user['id'], ['avatar_filename' => $filename, 'avatar_updated_at' => $now])
                        || !$db->transCommit()) {
                        throw new RuntimeException('No se pudo guardar la foto. Intent&aacute; nuevamente.');
                    }
                } catch (Throwable $e) {
                    $db->transRollback();
                    throw $e;
                }
            });
            session()->set(['avatarFilename' => $filename, 'avatarUpdatedAt' => $now]);

            return redirect()->to('/perfil')->with('success', 'Foto de perfil actualizada.');
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Throwable) {
            return redirect()->back()->with('error', 'No se pudo guardar la foto. Intent&aacute; nuevamente.');
        }
    }

    public function avatarDelete()
    {
        $user = $this->currentUser();
        if (!$user) {
            return redirect()->to('/logout');
        }

        $storage = new AvatarStorage();
        try {
            $model = new User();
            $db = db_connect();
            (new AvatarPersistence($storage))->remove($user['avatar_filename'] ?? null, function () use ($model, $db, $user): void {
                $db->transBegin();
                try {
                    if (!$model->update((int) $user['id'], ['avatar_filename' => null, 'avatar_updated_at' => null])
                        || !$db->transCommit()) {
                        throw new RuntimeException('No se pudo eliminar la foto.');
                    }
                } catch (Throwable $e) {
                    $db->transRollback();
                    throw $e;
                }
            });
            session()->set(['avatarFilename' => null, 'avatarUpdatedAt' => null]);

            return redirect()->to('/perfil')->with('success', 'Foto de perfil eliminada.');
        } catch (Throwable) {
            return redirect()->back()->with('error', 'No se pudo eliminar la foto.');
        }
    }

    public function avatar(int $id)
    {
        $viewerId = (int) session()->get('userId');
        $user = (new User())->find($id);
        if (!$user || !$this->canViewAvatar($viewerId, $id)) {
            return $this->response->setStatusCode(404);
        }

        $storage = new AvatarStorage();
        $path = $storage->pathFor((string) ($user['avatar_filename'] ?? ''));
        if (!$path || !is_file($path) || !is_readable($path)) {
            return $this->avatarFallback((string) ($user['name'] ?? '?'));
        }

        $etag = '"' . hash('sha256', ($user['avatar_filename'] ?? '') . '|' . ($user['avatar_updated_at'] ?? '')) . '"';
        if (trim($this->request->getHeaderLine('If-None-Match')) === $etag) {
            return $this->response->setStatusCode(304)->setHeader('ETag', $etag);
        }

        $info = @getimagesize($path);
        if ($info === false || !in_array($info['mime'] ?? '', ['image/webp', 'image/jpeg'], true)) {
            return $this->avatarFallback((string) ($user['name'] ?? '?'));
        }

        return $this->response
            ->setHeader('Content-Type', $info['mime'])
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setHeader('ETag', $etag)
            ->setBody((string) file_get_contents($path));
    }

    private function canViewAvatar(int $viewerId, int $targetId): bool
    {
        if ($viewerId === $targetId || session()->get('userRole') === 'admin') {
            return true;
        }

        return db_connect()->table('grupo_miembros viewer')
            ->join('grupo_miembros target', 'target.grupo_id = viewer.grupo_id')
            ->where('viewer.user_id', $viewerId)
            ->where('target.user_id', $targetId)
            ->countAllResults() > 0;
    }

    private function avatarFallback(string $name)
    {
        $initial = htmlspecialchars(mb_strtoupper(mb_substr(trim($name) ?: '?', 0, 1)), ENT_QUOTES | ENT_XML1, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256">'
            . '<rect width="256" height="256" rx="128" fill="#2563eb"/>'
            . '<text x="128" y="145" text-anchor="middle" font-family="sans-serif" font-size="112" font-weight="700" fill="white">'
            . $initial . '</text></svg>';

        return $this->response
            ->setHeader('Content-Type', 'image/svg+xml; charset=UTF-8')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody($svg);
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