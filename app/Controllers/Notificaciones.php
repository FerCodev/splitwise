<?php

namespace App\Controllers;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\PushSubscription;
use App\Services\WebPushSender;
use App\Services\UiFeedbackResolver;

class Notificaciones extends BaseController
{
    public function index()
    {
        $userId = (int) session()->get('userId');
        $notificationModel = new Notification();

        $notifications = $notificationModel->getByUser($userId);
        $pager = $notificationModel->pager;

        $unreadCount = $notificationModel->countUnread($userId);

        return view('notificaciones/index', [
            'notifications' => $notifications,
            'pager' => $pager,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function abrir(int $id)
    {
        $userId = (int) session()->get('userId');
        $notificationModel = new Notification();

        $notification = $notificationModel->find($id);

        if (!$notification || (int) $notification['user_id'] !== $userId) {
            return redirect()->to('/notificaciones')->with('error', 'Notificación no encontrada.');
        }

        $notificationModel->markAsRead($id, $userId);

        $targetUrl = $notification['target_url'] ?? '/notificaciones';

        if (!$this->isInternalUrl($targetUrl)) {
            return redirect()->to('/notificaciones');
        }

        return redirect()->to($targetUrl);
    }

    public function marcarTodasLeidas()
    {
        $userId = (int) session()->get('userId');
        (new Notification())->markAllAsRead($userId);

        return redirect()->to('/notificaciones')->with('success', 'Todas las notificaciones marcadas como leídas.');
    }

    public function configuracion()
    {
        $userId = (int) session()->get('userId');
        $prefModel = new NotificationPreference();
        $prefs = $prefModel->getForUser($userId);

        $pushConfig = config(\Config\Push::class);
        $pushConfigured = $pushConfig->isConfigured();
        $publicKey = $pushConfig->publicKey;

        if ($this->request->getMethod() === 'post') {
            $prefModel->saveForUser($userId, [
                'push_enabled' => $this->request->getPost('push_enabled') ? true : false,
                'expense_created' => $this->request->getPost('expense_created') ? true : false,
                'show_amounts' => $this->request->getPost('show_amounts') ? true : false,
            ]);

            return redirect()->to('/notificaciones/configuracion')->with('success', 'Preferencias actualizadas.');
        }

        return view('notificaciones/configuracion', [
            'prefs' => $prefs,
            'pushConfigured' => $pushConfigured,
            'publicKey' => $publicKey,
        ]);
    }

    public function clavePublica()
    {
        $pushConfig = config(\Config\Push::class);

        return $this->response->setJSON([
            'publicKey' => $pushConfig->publicKey,
            'configured' => $pushConfig->isConfigured(),
        ]);
    }

    public function suscribir()
    {
        $userId = (int) session()->get('userId');

        $endpoint = trim((string) ($this->request->getPost('endpoint') ?? $this->request->getVar('endpoint') ?? ''));
        $p256dh = trim((string) ($this->request->getPost('keys')['p256dh'] ?? $this->request->getVar('keys[p256dh]') ?? ''));
        $auth = trim((string) ($this->request->getPost('keys')['auth'] ?? $this->request->getVar('keys[auth]') ?? ''));

        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Datos de suscripción inválidos.']);
        }

        if (!str_starts_with($endpoint, 'https://')) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Endpoint inválido.']);
        }

        if (strlen($endpoint) > 1024) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Endpoint demasiado largo.']);
        }

        $subscriptionData = [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => $p256dh,
                'auth' => $auth,
            ],
            'content_encoding' => 'aes128gcm',
            'user_agent' => substr((string) ($this->request->getUserAgent() ?? ''), 0, 500) ?: null,
        ];

        $subsModel = new PushSubscription();
        $subsModel->upsertForUser($userId, $subscriptionData);

        return $this->response->setJSON(['success' => true, 'csrfToken' => csrf_token(), 'csrfHash' => csrf_hash()]);
    }

    public function eliminarSuscripcion()
    {
        $userId = (int) session()->get('userId');
        $endpoint = $this->request->getPost('endpoint') ?? $this->request->getVar('endpoint');

        if (!$endpoint) {
            return redirect()->back()->with('error', 'Datos incompletos.');
        }

        $hash = hash('sha256', $endpoint);
        $subsModel = new PushSubscription();
        $sub = $subsModel->findByEndpointHash($hash);

        if ($sub && (int) $sub['user_id'] === $userId) {
            $subsModel->disable($sub['id'], $userId);
            return redirect()->to('/notificaciones/configuracion')->with('success', 'Dispositivo desactivado.');
        }

        return redirect()->to('/notificaciones/configuracion')->with('error', 'Suscripción no encontrada.');
    }

    public function prueba()
    {
        $userId = (int) session()->get('userId');

        if ((session()->get('push_test_sent') ?? 0) > time() - 60) {
            return $this->response->setJSON(['error' => 'Esperá un minuto antes de enviar otra prueba.']);
        }

        $sender = new WebPushSender();
        if (!$sender->isConfigured()) {
            return $this->response->setJSON(['error' => 'Web Push no está configurado.']);
        }

        $subsModel = new PushSubscription();
        $subscriptions = $subsModel->findEnabledByUser($userId);

        if (empty($subscriptions)) {
            return $this->response->setJSON(['error' => 'No tenés dispositivos activos. Activá las notificaciones primero.']);
        }

        $payload = [
            'title' => 'Gastito',
            'body' => '¡Notificación de prueba! Todo funciona correctamente.',
            'url' => '/notificaciones',
            'icon' => '/assets/pwa/icon-192.png',
            'badge' => '/assets/pwa/icon-192.png',
            'tag' => 'test-' . time(),
        ];

        $result = $sender->sendToAll($subscriptions, $payload);
        session()->set('push_test_sent', time());

        return $this->response->setJSON($result);
    }

    public function contador()
    {
        $userId = (int) session()->get('userId');
        $count = (new Notification())->countUnread($userId);

        return $this->response->setJSON(['count' => $count]);
    }

    private function isInternalUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        $parsed = parse_url($url);

        if (!empty($parsed['scheme']) && $parsed['scheme'] !== 'http' && $parsed['scheme'] !== 'https') {
            return false;
        }

        if (!empty($parsed['host'])) {
            $baseHost = parse_url(base_url(), PHP_URL_HOST);
            if ($parsed['host'] !== $baseHost) {
                return false;
            }
        }

        $path = $parsed['path'] ?? '';
        if (!str_starts_with($path, '/')) {
            return false;
        }

        return true;
    }
}
