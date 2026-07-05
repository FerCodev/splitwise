<?php

use App\Services\EndpointValidator;
use App\Services\WebPushSender;
use App\Services\NotificationDispatcher;
use App\Models\NotificationOutbox;
use App\Models\NotificationDelivery;
use App\Controllers\Notificaciones;

/**
 * @internal
 */
final class WebPushSecurityTest extends \CodeIgniter\Test\CIUnitTestCase
{
    // ─── DNS A/AAAA ─────────────────────────────────────────────────

    public function testDnsResolveReturnsArray(): void
    {
        $validator = new EndpointValidator();
        $result = $validator->dnsResolve('google.com');
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        foreach ($result as $ip) {
            $this->assertNotFalse(filter_var($ip, FILTER_VALIDATE_IP));
        }
    }

    public function testRejectsHostnameResolvingToLoopback(): void
    {
        $validator = new EndpointValidator(fn () => ['127.0.0.1']);
        $this->assertFalse($validator->isValid('https://evil.local/push'));
    }

    public function testRejectsHostnameResolvingToPrivateIp(): void
    {
        $validator = new EndpointValidator(fn () => ['192.168.1.100']);
        $this->assertFalse($validator->isValid('https://priv.example.com/push'));
    }

    public function testRejectsHostnameResolvingTo10xX(): void
    {
        $validator = new EndpointValidator(fn () => ['10.0.0.55']);
        $this->assertFalse($validator->isValid('https://internal.db/push'));
    }

    public function testRejectsPublicIpv4ButPrivateIpv6(): void
    {
        $validator = new EndpointValidator(fn () => ['8.8.8.8', '::1']);
        $this->assertFalse($validator->isValid('https://mixed.example.com/push'));
    }

    public function testRejectsIpv6Loopback(): void
    {
        $validator = new EndpointValidator(fn () => ['::1']);
        $this->assertFalse($validator->isValid('https://v6loopback.example/push'));
    }

    public function testRejectsIpv6LinkLocal(): void
    {
        $validator = new EndpointValidator(fn () => ['fe80::1']);
        $this->assertFalse($validator->isValid('https://linklocal.example/push'));
    }

    public function testRejectsPrivateIpv6(): void
    {
        $validator = new EndpointValidator(fn () => ['fc00::1']);
        $this->assertFalse($validator->isValid('https://privatev6.example/push'));
    }

    public function testAcceptsPublicIpv4Only(): void
    {
        $validator = new EndpointValidator(fn () => ['8.8.8.8']);
        $this->assertTrue($validator->isValid('https://fcm.googleapis.com/fcm/send/x'));
    }

    public function testAcceptsPublicIpv6Only(): void
    {
        $validator = new EndpointValidator(fn () => ['2001:4860:4860::8888']);
        $this->assertTrue($validator->isValid('https://v6public.example/push'));
    }

    public function testRejectsEmptyDnsResult(): void
    {
        $validator = new EndpointValidator(fn () => []);
        $this->assertFalse($validator->isValid('https://no-result.example/push'));
    }

    public function testRejectsDnsFailure(): void
    {
        $validator = new EndpointValidator(fn () => false);
        $this->assertFalse($validator->isValid('https://nxdomain.test/push'));
    }

    public function testRejectsLiteralLoopback(): void
    {
        $validator = new EndpointValidator();
        $this->assertFalse($validator->isValid('https://localhost/push'));
        $this->assertFalse($validator->isValid('https://127.0.0.1/push'));
    }

    public function testRejectsLiteralPrivateIp(): void
    {
        $validator = new EndpointValidator();
        $this->assertFalse($validator->isValid('https://192.168.1.1/push'));
        $this->assertFalse($validator->isValid('https://10.0.0.1/push'));
        $this->assertFalse($validator->isValid('https://172.16.0.1/push'));
    }

    public function testRejectsNonHttps(): void
    {
        $validator = new EndpointValidator();
        $this->assertFalse($validator->isValid('http://push.example.com/'));
    }

    public function testRejectsCredentialsInUrl(): void
    {
        $validator = new EndpointValidator(fn () => ['8.8.8.8']);
        $this->assertFalse($validator->isValid('https://user:pass@host.com/push'));
    }

    // ─── classifyError real tests ───────────────────────────────────

    public function testClassifyExpiredByMethod(): void { $this->assertClassify(WebPushSender::ERROR_EXPIRED, true, 200); }
    public function testClassifyExpired404(): void { $this->assertClassify(WebPushSender::ERROR_EXPIRED, false, 404); }
    public function testClassifyExpired410(): void { $this->assertClassify(WebPushSender::ERROR_EXPIRED, false, 410); }
    public function testClassifyTransient429(): void { $this->assertClassify(WebPushSender::ERROR_TRANSIENT_429, false, 429); }
    public function testClassifyTransient500(): void { $this->assertClassify(WebPushSender::ERROR_TRANSIENT_5XX, false, 500); }
    public function testClassifyTransient502(): void { $this->assertClassify(WebPushSender::ERROR_TRANSIENT_5XX, false, 502); }
    public function testClassifyPermanent400(): void { $this->assertClassify(WebPushSender::ERROR_PERMANENT_4XX, false, 400); }
    public function testClassifyPermanent401(): void { $this->assertClassify(WebPushSender::ERROR_PERMANENT_4XX, false, 401); }
    public function testClassifyPermanent403(): void { $this->assertClassify(WebPushSender::ERROR_PERMANENT_4XX, false, 403); }
    public function testClassifyTransportNoResponse(): void { $this->assertClassify(WebPushSender::ERROR_TRANSPORT, false, 0); }

    private function assertClassify(string $expected, bool $expired, int $code): void
    {
        $response = $code > 0 ? new \GuzzleHttp\Psr7\Response($code) : null;
        $report = $this->getMockBuilder(\Minishlink\WebPush\MessageSentReport::class)
            ->disableOriginalConstructor()->getMock();
        $report->method('isSubscriptionExpired')->willReturn($expired);
        $report->method('isSuccess')->willReturn(false);
        $report->method('getResponse')->willReturn($response);

        $sender = new WebPushSender();
        $this->assertSame($expected, $sender->classifyError($report));
    }

    // ─── Partial delivery flow ──────────────────────────────────────

    public function testPartialDeliveryOnlyRetriesPendingSubscriptions(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'name' => 'PU', 'email' => 'pu@t.com', 'password' => 'x',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $uid = $db->insertID();
        $db->table('notifications')->insert([
            'user_id' => $uid, 'event_type' => 'x', 'title' => 'T', 'body' => 'B',
            'target_url' => '/g/1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert([
            'user_id' => $uid, 'endpoint' => 'https://a.com/p', 'endpoint_hash' => hash('sha256', 'https://a.com/p'),
            'public_key' => 'a', 'auth_token' => 'b', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $subA = $db->insertID();
        $db->table('push_subscriptions')->insert([
            'user_id' => $uid, 'endpoint' => 'https://b.com/p', 'endpoint_hash' => hash('sha256', 'https://b.com/p'),
            'public_key' => 'c', 'auth_token' => 'd', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $subB = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $deliveryModel = new NotificationDelivery();
        $deliveryModel->ensureForNotification($nid, [$subA, $subB]);

        // fake sender: A succeeds, B fails transient
        $fake = $this->createFakeSender([
            'success' => [['push_subscription_id' => $subA, 'status' => 'success']],
            'details' => [
                ['push_subscription_id' => $subA, 'status' => 'success'],
                ['push_subscription_id' => $subB, 'status' => WebPushSender::ERROR_TRANSIENT_5XX],
            ],
        ]);

        $dispatcher = new NotificationDispatcher($fake);
        $result = $dispatcher->dispatch(10);

        $this->assertSame(1, $result['processed']);
        $this->assertSame(1, $result['sent']);
        $this->assertSame(1, $result['retried']);

        $delA = $deliveryModel->where('notification_id', $nid)->where('push_subscription_id', $subA)->first();
        $this->assertSame(NotificationDelivery::STATUS_SUCCESS, $delA['status']);

        $delB = $deliveryModel->where('notification_id', $nid)->where('push_subscription_id', $subB)->first();
        $this->assertSame(NotificationDelivery::STATUS_RETRY, $delB['status']);

        // Simular que el backoff ya vencio
        $db->query('UPDATE notification_deliveries SET available_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $delB['id']]);
        $db->query('UPDATE notification_outbox SET available_at = ? WHERE notification_id = ?', [date('Y-m-d H:i:s'), $nid]);

        // segundo dispatch: solo B pendiente
        $fake2 = $this->createFakeSender([
            'success' => [['push_subscription_id' => $subB, 'status' => 'success']],
            'details' => [['push_subscription_id' => $subB, 'status' => 'success']],
        ]);

        $dispatcher2 = new NotificationDispatcher($fake2);
        $result2 = $dispatcher2->dispatch(10);

        $this->assertSame(1, $result2['sent']);

        $delA2 = $deliveryModel->find($delA['id']);
        $this->assertSame(NotificationDelivery::STATUS_SUCCESS, $delA2['status']);

        $delB2 = $deliveryModel->find($delB['id']);
        $this->assertSame(NotificationDelivery::STATUS_SUCCESS, $delB2['status']);

        $this->assertFalse($deliveryModel->hasPendingForNotification($nid));

        $db->transRollback();
    }

    public function testPermanentErrorDoesNotRetry(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'name' => 'PE', 'email' => 'pe@t.com', 'password' => 'x',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $uid = $db->insertID();
        $db->table('notifications')->insert([
            'user_id' => $uid, 'event_type' => 'x', 'title' => 'T', 'body' => 'B',
            'target_url' => '/g/1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert([
            'user_id' => $uid, 'endpoint' => 'https://c.com/p', 'endpoint_hash' => hash('sha256', 'https://c.com/p'),
            'public_key' => 'a', 'auth_token' => 'b', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $subC = $db->insertID();
        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $deliveryModel = new NotificationDelivery();
        $deliveryModel->ensureForNotification($nid, [$subC]);

        $fake = $this->createFakeSender([
            'success' => [],
            'details' => [['push_subscription_id' => $subC, 'status' => WebPushSender::ERROR_PERMANENT_4XX]],
        ]);

        $dispatcher = new NotificationDispatcher($fake);
        $result = $dispatcher->dispatch(10);

        $del = $deliveryModel->where('notification_id', $nid)->where('push_subscription_id', $subC)->first();
        $this->assertSame(NotificationDelivery::STATUS_FAILED, $del['status']);
        $this->assertFalse($deliveryModel->hasPendingForNotification($nid));
        $this->assertSame(0, $result['retried']);

        $db->transRollback();
    }

    public function testExpiredDoesNotRetry(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'name' => 'PX', 'email' => 'px@t.com', 'password' => 'x',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $uid = $db->insertID();
        $db->table('notifications')->insert([
            'user_id' => $uid, 'event_type' => 'x', 'title' => 'T', 'body' => 'B',
            'target_url' => '/g/1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert([
            'user_id' => $uid, 'endpoint' => 'https://d.com/p', 'endpoint_hash' => hash('sha256', 'https://d.com/p'),
            'public_key' => 'a', 'auth_token' => 'b', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $subD = $db->insertID();
        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $deliveryModel = new NotificationDelivery();
        $deliveryModel->ensureForNotification($nid, [$subD]);

        $fake = $this->createFakeSender([
            'success' => [],
            'details' => [['push_subscription_id' => $subD, 'status' => WebPushSender::ERROR_EXPIRED]],
        ]);

        $dispatcher = new NotificationDispatcher($fake);
        $dispatcher->dispatch(10);

        $del = $deliveryModel->where('notification_id', $nid)->where('push_subscription_id', $subD)->first();
        $this->assertSame(NotificationDelivery::STATUS_EXPIRED, $del['status']);

        // segundo dispatch: no deberia haber deliveries pendientes
        $hasPending = $deliveryModel->hasPendingForNotification($nid);
        $this->assertFalse($hasPending);

        $db->transRollback();
    }

    public function testSuccessNeverReSent(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'name' => 'SNR', 'email' => 'snr@t.com', 'password' => 'x',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $uid = $db->insertID();
        $db->table('notifications')->insert([
            'user_id' => $uid, 'event_type' => 'x', 'title' => 'T', 'body' => 'B',
            'target_url' => '/g/1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert([
            'user_id' => $uid, 'endpoint' => 'https://e.com/p', 'endpoint_hash' => hash('sha256', 'https://e.com/p'),
            'public_key' => 'a', 'auth_token' => 'b', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $subE = $db->insertID();
        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $deliveryModel = new NotificationDelivery();
        $deliveryModel->ensureForNotification($nid, [$subE]);

        // dispatch 1: success
        $fake1 = $this->createFakeSender([
            'success' => [['push_subscription_id' => $subE, 'status' => 'success']],
            'details' => [['push_subscription_id' => $subE, 'status' => 'success']],
        ]);
        (new NotificationDispatcher($fake1))->dispatch(10);

        $del = $deliveryModel->where('notification_id', $nid)->where('push_subscription_id', $subE)->first();
        $this->assertSame(NotificationDelivery::STATUS_SUCCESS, $del['status']);

        // dispatch 2: no pending deliveries, should skip
        $fake2 = $this->createFakeSender(['success' => [], 'details' => []]);
        $result = (new NotificationDispatcher($fake2))->dispatch(10);
        $this->assertSame(0, $result['processed']);

        $db->transRollback();
    }

    // ─── helpers ────────────────────────────────────────────────────

    private function createFakeSender(array $config): WebPushSender
    {
        $fake = $this->getMockBuilder(WebPushSender::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isConfigured', 'sendToAll'])
            ->getMock();

        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () use ($config) {
            $r = ['success' => 0, 'expired' => 0, 'failed' => 0, 'details' => []];
            foreach ($config['details'] as $d) {
                $s = $d['status'];
                if ($s === 'success') $r['success']++;
                elseif ($s === 'expired') $r['expired']++;
                else $r['failed']++;
                $r['details'][] = $d;
            }
            return $r;
        });

        return $fake;
    }

    // ─── Base64Url decode validation ────────────────────────────────

    public function testIsValidBase64UrlDecodes(): void
    {
        $controller = new Notificaciones();
        $method = new \ReflectionMethod(Notificaciones::class, 'isValidBase64Url');
        $method->setAccessible(true);

        $valid = rtrim(strtr(base64_encode(random_bytes(65)), '+/', '-_'), '=');
        $this->assertTrue($method->invoke($controller, $valid, 65, 256));

        $tooShort = rtrim(strtr(base64_encode(random_bytes(10)), '+/', '-_'), '=');
        $this->assertFalse($method->invoke($controller, $tooShort, 65, 256));

        $invalid = '!@#$%^&*()===';
        $this->assertFalse($method->invoke($controller, $invalid, 1, 100));
    }
}
