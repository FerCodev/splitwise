<?php

namespace App\Controllers;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\PushSubscription;
use App\Services\WebPushSender;

class Notificaciones extends BaseController
{
    private function jsonResponse(array $data, int $status = 200): \CodeIgniter\HTTP\ResponseInterface
    {
        $data['csrfToken'] = csrf_token();
        $data['csrfHash'] = csrf_hash();
        return $this->response->setStatusCode($status)->setJSON($data);
    }

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

        $targetUrl = $notification['target_url'] ?? base_url('notificaciones');

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

        return $this->jsonResponse([
            'publicKey' => $pushConfig->publicKey,
            'configured' => $pushConfig->isConfigured(),
        ]);
    }

    private function isValidSubscriptionEndpoint(string $endpoint): bool
    {
        $url = filter_var($endpoint, FILTER_VALIDATE_URL);
        if (!$url) {
            return false;
        }

        if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host || $host === '') {
            return false;
        }

        $hostLower = strtolower($host);

        if (in_array($hostLower, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        return true;
    }

    private function isValidBase64Url(string $value, int $minLen, int $maxLen): bool
    {
        $len = strlen($value);
        if ($len < $minLen || $len > $maxLen) {
            return false;
        }
        return (bool) preg_match('/^[A-Za-z0-9\-_]+$/', $value);
    }

    public function suscribir()
    {
        $userId = (int) session()->get('userId');

        $endpoint = trim((string) ($this->request->getPost('endpoint') ?? ''));
        $p256dh = trim((string) ($this->request->getPost('keys')['p256dh'] ?? ''));
        $auth = trim((string) ($this->request->getPost('keys')['auth'] ?? ''));

        if ($endpoint === '' || $p256dh === '' || $auth === '') {
            return $this->jsonResponse(['error' => 'Datos de suscripción inválidos.'], 400);
        }

        if (strlen($endpoint) > 1024) {
            return $this->jsonResponse(['error' => 'Endpoint demasiado largo.'], 400);
        }

        if (!$this->isValidSubscriptionEndpoint($endpoint)) {
            return $this->jsonResponse(['error' => 'Endpoint inválido o no permitido.'], 400);
        }

        if (!$this->isValidBase64Url($p256dh, 44, 255)) {
            return $this->jsonResponse(['error' => 'Clave p256dh inválida.'], 400);
        }

        if (!$this->isValidBase64Url($auth, 16, 255)) {
            return $this->jsonResponse(['error' => 'Token auth inválido.'], 400);
        }

        $contentEncoding = $this->request->getPost('content_encoding') ?? 'aes128gcm';
        if (!in_array($contentEncoding, ['aes128gcm', 'aesgcm'], true)) {
            $contentEncoding = 'aes128gcm';
        }

        $subscriptionData = [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => $p256dh, 'auth' => $auth],
            'content_encoding' => $contentEncoding,
            'user_agent' => substr((string) ($this->request->getUserAgent() ?? ''), 0, 500) ?: null,
        ];

        $subsModel = new PushSubscription();
        $subsModel->upsertForUser($userId, $subscriptionData);

        return $this->jsonResponse(['success' => true]);
    }

    public function eliminarSuscripcion()
    {
        $userId = (int) session()->get('userId');
        $endpoint = $this->request->getPost('endpoint') ?? '';

        if ($endpoint === '') {
            return $this->jsonResponse(['error' => 'Datos incompletos.'], 400);
        }

        $hash = hash('sha256', $endpoint);
        $subsModel = new PushSubscription();
        $sub = $subsModel->findByEndpointHash($hash);

        if ($sub && (int) $sub['user_id'] === $userId) {
            $subsModel->disable($sub['id'], $userId);
            return $this->jsonResponse(['success' => true]);
        }

        return $this->jsonResponse(['error' => 'Suscripción no encontrada.'], 404);
    }

    public function prueba()
    {
        $userId = (int) session()->get('userId');

        if ((session()->get('push_test_sent') ?? 0) > time() - 60) {
            return $this->jsonResponse(['error' => 'Esperá un minuto antes de enviar otra prueba.'], 429);
        }

        $sender = new WebPushSender();
        if (!$sender->isConfigured()) {
            return $this->jsonResponse(['error' => 'Web Push no está configurado.'], 503);
        }

        $subsModel = new PushSubscription();
        $subscriptions = $subsModel->findEnabledByUser($userId);

        if (empty($subscriptions)) {
            return $this->jsonResponse(['error' => 'No tenés dispositivos activos.'], 400);
        }

        $payload = [
            'title' => 'Gastito',
            'body' => 'Notificación de prueba. Todo funciona correctamente.',
            'url' => base_url('notificaciones'),
            'icon' => base_url('assets/pwa/icon-192.png'),
            'badge' => base_url('assets/pwa/icon-192.png'),
            'tag' => 'test-' . time(),
        ];

        $result = $sender->sendToAll($subscriptions, $payload);
        session()->set('push_test_sent', time());

        return $this->jsonResponse($result);
    }

    public function contador()
    {
        $userId = (int) session()->get('userId');
        $count = (new Notification())->countUnread($userId);

        return $this->jsonResponse(['count' => $count]);
    }

    private function isInternalUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        if (str_contains($url, '@') || str_starts_with($url, '//')) {
            return false;
        }

        $parsed = parse_url($url);

        if (!empty($parsed['scheme']) && !in_array($parsed['scheme'], ['http', 'https'], true)) {
            return false;
        }

        $baseUrl = rtrim(base_url(), '/');
        $baseParsed = parse_url($baseUrl);
        $baseHost = $baseParsed['host'] ?? '';
        $basePort = $baseParsed['port'] ?? null;
        $basePath = $baseParsed['path'] ?? '';

        if (!empty($parsed['host'])) {
            $host = $parsed['host'];
            $port = $parsed['port'] ?? null;
            $scheme = $parsed['scheme'] ?? 'https';

            if (strtolower($host) !== strtolower($baseHost)) {
                return false;
            }

            if ($port !== $basePort) {
                return false;
            }

            if ($scheme !== ($baseParsed['scheme'] ?? 'https')) {
                return false;
            }
        }

        $path = $parsed['path'] ?? '';

        if ($path !== '' && !str_starts_with($path, '/')) {
            return false;
        }

        if ($basePath !== '' && $basePath !== '/' && $path !== '' && !str_starts_with($path, $basePath . '/') && $path !== $basePath) {
            return false;
        }

        return true;
    }
}
