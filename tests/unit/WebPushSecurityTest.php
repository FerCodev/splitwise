<?php

use App\Services\EndpointValidator;
use App\Services\WebPushSender;
use App\Controllers\Notificaciones;
use App\Models\NotificationOutbox;
use App\Models\NotificationDelivery;

/**
 * @internal
 */
final class WebPushSecurityTest extends \CodeIgniter\Test\CIUnitTestCase
{
    // ─── DNS / SSRF ──────────────────────────────────────────────────

    public function testRejectsHostnameResolvingToLocalhost(): void
    {
        $validator = new EndpointValidator(function (string $host) {
            if ($host === 'evil.local') return '127.0.0.1';
            return gethostbyname($host);
        });
        $this->assertFalse($validator->isValid('https://evil.local/push'));
    }

    public function testRejectsHostnameResolvingToPrivateIp(): void
    {
        $validator = new EndpointValidator(function (string $host) {
            if ($host === 'priv.example.com') return '192.168.1.100';
            return gethostbyname($host);
        });
        $this->assertFalse($validator->isValid('https://priv.example.com/push'));
    }

    public function testRejectsHostnameResolvingTo10xX(): void
    {
        $validator = new EndpointValidator(function (string $host) {
            if ($host === 'internal.db') return '10.0.0.55';
            return gethostbyname($host);
        });
        $this->assertFalse($validator->isValid('https://internal.db/push'));
    }

    public function testRejectsDnsFailure(): void
    {
        $validator = new EndpointValidator(function (string $host) {
            return false;
        });
        $this->assertFalse($validator->isValid('https://nxdomain.test/push'));
    }

    public function testRejectsDnsReturnsSameHost(): void
    {
        $validator = new EndpointValidator(function (string $host) {
            return $host;
        });
        $this->assertFalse($validator->isValid('https://fail.example/push'));
    }

    public function testAcceptsHostResolvingToPublicIp(): void
    {
        $validator = new EndpointValidator(function (string $host) {
            return '8.8.8.8';
        });
        $this->assertTrue($validator->isValid('https://fcm.googleapis.com/fcm/send/x'));
    }

    public function testRejectsCredentialsInUrl(): void
    {
        $validator = new EndpointValidator(function (string $host) {
            return '8.8.8.8';
        });
        $this->assertFalse($validator->isValid('https://user:pass@host.com/push'));
    }

    public function testRejectsLiteralLocalhost(): void
    {
        $validator = new EndpointValidator();
        $this->assertFalse($validator->isValid('https://localhost/push'));
    }

    public function testRejectsLiteral127001(): void
    {
        $validator = new EndpointValidator();
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

    // ─── classifyError (real tests) ──────────────────────────────────

    public function testClassifyExpiredByMethod(): void
    {
        $report = $this->createMockReport(true, 200);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_EXPIRED, $sender->classifyError($report));
    }

    public function testClassifyExpired404(): void
    {
        $report = $this->createMockReport(false, 404);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_EXPIRED, $sender->classifyError($report));
    }

    public function testClassifyExpired410(): void
    {
        $report = $this->createMockReport(false, 410);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_EXPIRED, $sender->classifyError($report));
    }

    public function testClassifyTransient429(): void
    {
        $report = $this->createMockReport(false, 429);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_TRANSIENT_429, $sender->classifyError($report));
    }

    public function testClassifyTransient500(): void
    {
        $report = $this->createMockReport(false, 500);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_TRANSIENT_5XX, $sender->classifyError($report));
    }

    public function testClassifyPermanent400(): void
    {
        $report = $this->createMockReport(false, 400);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_PERMANENT_4XX, $sender->classifyError($report));
    }

    public function testClassifyPermanent401(): void
    {
        $report = $this->createMockReport(false, 401);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_PERMANENT_4XX, $sender->classifyError($report));
    }

    public function testClassifyPermanent403(): void
    {
        $report = $this->createMockReport(false, 403);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_PERMANENT_4XX, $sender->classifyError($report));
    }

    public function testClassifyTransportNoResponse(): void
    {
        $report = $this->createMockReport(false, 0);
        $sender = new WebPushSender();
        $this->assertSame(WebPushSender::ERROR_TRANSPORT, $sender->classifyError($report));
    }

    private function createMockReport(bool $expired, int $statusCode): \Minishlink\WebPush\MessageSentReport
    {
        $response = null;
        if ($statusCode > 0) {
            $response = new \GuzzleHttp\Psr7\Response($statusCode);
        }

        $report = $this->getMockBuilder(\Minishlink\WebPush\MessageSentReport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $report->method('isSubscriptionExpired')->willReturn($expired);
        $report->method('isSuccess')->willReturn(false);
        $report->method('getResponse')->willReturn($response);

        return $report;
    }

    // ─── Input type validation ───────────────────────────────────────

    public function testIsValidBase64UrlDecodes(): void
    {
        $controller = new Notificaciones();
        $method = new \ReflectionMethod(Notificaciones::class, 'isValidBase64Url');
        $method->setAccessible(true);

        $valid = rtrim(strtr(base64_encode(random_bytes(65)), '+/', '-_'), '=');
        $this->assertTrue($method->invoke($controller, $valid, 65, 256));

        $tooShort = rtrim(strtr(base64_encode(random_bytes(10)), '+/', '-_'), '=');
        $this->assertFalse($method->invoke($controller, $tooShort, 65, 256));

        $invalid = '!!!notbase64!!!';
        $this->assertFalse($method->invoke($controller, $invalid, 10, 100));

        $undecodable = '!@#$%^&*()===';
        $decoded = base64_decode(strtr($undecodable, '-_', '+/'), true);
        $this->assertFalse($decoded);
        $this->assertFalse($method->invoke($controller, $undecodable, 1, 100));
    }

    // ─── Outbox claim / orphan ───────────────────────────────────────

    public function testClaimForProcessingAffectedRows(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'name' => 'TCX', 'email' => 'tcx9991@t.com', 'password' => 'x',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $uid = $db->insertID();
        $db->table('notifications')->insert([
            'user_id' => $uid, 'event_type' => 'x', 'title' => 'T', 'body' => 'B',
            'target_url' => '/g/1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        if ($job) {
            $this->assertTrue($outbox->claimForProcessing($job['id']));
            $this->assertFalse($outbox->claimForProcessing($job['id']));
        }
        $db->transRollback();
    }

    // ─── NotificationDelivery ────────────────────────────────────────

    public function testDeliveryEnsureCreatesOnce(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'name' => 'DU', 'email' => 'du@t.com', 'password' => 'x', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $uid = $db->insertID();
        $db->table('notifications')->insert([
            'user_id' => $uid, 'event_type' => 'x', 'title' => 'T', 'body' => 'B',
            'target_url' => '/g/1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();

        // insert mock push_subscription
        $db->table('push_subscriptions')->insert([
            'user_id' => $uid, 'endpoint' => 'https://example.com/push', 'endpoint_hash' => hash('sha256', 'https://example.com/push'),
            'public_key' => 'a', 'auth_token' => 'b', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $sid = $db->insertID();

        $deliveryModel = new NotificationDelivery();
        $deliveryModel->ensureForNotification($nid, [$sid]);
        $deliveryModel->ensureForNotification($nid, [$sid]);

        $all = $deliveryModel->where('notification_id', $nid)->findAll();
        $this->assertCount(1, $all);

        $db->transRollback();
    }

    public function testDeliverySuccessMarksComplete(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'name' => 'DS', 'email' => 'ds@t.com', 'password' => 'x', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $uid = $db->insertID();
        $db->table('notifications')->insert([
            'user_id' => $uid, 'event_type' => 'x', 'title' => 'T', 'body' => 'B',
            'target_url' => '/g/1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert([
            'user_id' => $uid, 'endpoint' => 'https://e1.com/p', 'endpoint_hash' => hash('sha256', 'https://e1.com/p'),
            'public_key' => 'a', 'auth_token' => 'b', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $sid = $db->insertID();

        $m = new NotificationDelivery();
        $m->ensureForNotification($nid, [$sid]);
        $del = $m->where('notification_id', $nid)->where('push_subscription_id', $sid)->first();
        $this->assertNotNull($del);

        $m->markSuccess($del['id']);
        $delAfter = $m->find($del['id']);
        $this->assertSame(NotificationDelivery::STATUS_SUCCESS, $delAfter['status']);
        $this->assertNotNull($delAfter['processed_at']);

        $db->transRollback();
    }
}
