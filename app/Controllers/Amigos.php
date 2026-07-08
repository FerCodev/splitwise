<?php

namespace App\Controllers;

use App\Models\Friendship;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\Username;
use RuntimeException;

class Amigos extends BaseController
{
    public function index()
    {
        $userId = (int) session()->get('userId');
        $found = null;
        $query = trim((string) $this->request->getGet('query'));
        if ($query !== '') {
            $users = (new User())->select('id, name, username, avatar_filename, avatar_updated_at, role')
                ->where('id !=', $userId)->where('role !=', 'admin');
            $found = str_contains($query, '@') && !str_starts_with($query, '@')
                ? $users->where('email', strtolower($query))->first()
                : $users->where('username', Username::normalize($query))->first();
            if ($found) {
                $found['relationship'] = (new Friendship())->between($userId, (int) $found['id']);
                session()->set('friendSearchPreviewId', (int) $found['id']);
            } else {
                session()->remove('friendSearchPreviewId');
            }
        }
        return view('amigos/index', array_merge((new Friendship())->dashboard($userId), [
            'found' => $found, 'searched' => $query !== '', 'query' => $query,
        ]));
    }

    public function enviar()
    {
        $userId = (int) session()->get('userId');
        $targetId = (int) $this->request->getPost('user_id');
        try {
            (new Friendship())->request($userId, $targetId);
            $actor = (string) session()->get('userName');
            $this->queueNotifications(static fn(NotificationService $n) => $n->notifyFriendRequest($targetId, $userId, $actor));
            return redirect()->to('/amigos')->with('success', 'Solicitud enviada.');
        } catch (RuntimeException $e) {
            return redirect()->to('/amigos')->with('error', $e->getMessage());
        }
    }

    public function aceptar(int $id) { return $this->responder($id, true); }
    public function rechazar(int $id) { return $this->responder($id, false); }

    private function responder(int $id, bool $accept)
    {
        try {
            $row = (new Friendship())->respond($id, (int) session()->get('userId'), $accept);
            if ($accept) {
                $targetId = (int) $row['requested_by_id'];
                $actorId = (int) session()->get('userId');
                $actor = (string) session()->get('userName');
                $this->queueNotifications(static fn(NotificationService $n) => $n->notifyFriendAccepted($targetId, $actorId, $actor));
            }
            return redirect()->to('/amigos')->with('success', $accept ? 'Ahora son amigos.' : 'Solicitud rechazada.');
        } catch (RuntimeException $e) {
            return redirect()->to('/amigos')->with('error', $e->getMessage());
        }
    }

    public function cancelar(int $id)
    {
        $ok = (new Friendship())->cancel($id, (int) session()->get('userId'));
        return redirect()->to('/amigos')->with($ok ? 'success' : 'error', $ok ? 'Solicitud cancelada.' : 'No se pudo cancelar la solicitud.');
    }

    public function eliminar(int $id)
    {
        $ok = (new Friendship())->unfriend($id, (int) session()->get('userId'));
        return redirect()->to('/amigos')->with($ok ? 'success' : 'error', $ok ? 'Amistad eliminada.' : 'No se pudo eliminar la amistad.');
    }
}
