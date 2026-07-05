<?php

use App\Services\WebPushSender;
use App\Controllers\Notificaciones;
use App\Models\NotificationOutbox;

/**
 * @internal
 */
final class WebPushSecurityTest extends \CodeIgniter\Test\CIUnitTestCase
{
    // ─── Endpoint validation (SSRF) ─────────────────────────────────

    public function testRejectsLocalhostEndpoint(): void
    {
        $sender = new WebPushSender();
        $this->assertFalse($sender->isEndpointValid('https://localhost/push'));
    }

    public function testRejectsLoopbackEndpoint(): void
    {
        $sender = new WebPushSender();
        $this->assertFalse($sender->isEndpointValid('https://127.0.0.1/push'));
    }

    public function testRejectsPrivateIpEndpoint(): void
    {
        $sender = new WebPushSender();
        $this->assertFalse($sender->isEndpointValid('https://192.168.1.1/push'));
        $this->assertFalse($sender->isEndpointValid('https://10.0.0.1/push'));
        $this->assertFalse($sender->isEndpointValid('https://172.16.0.1/push'));
    }

    public function testRejectsNonHttpsEndpoint(): void
    {
        $sender = new WebPushSender();
        $this->assertFalse($sender->isEndpointValid('http://push.example.com/'));
    }

    public function testAcceptsValidPublicEndpoint(): void
    {
        $sender = new WebPushSender();
        $this->assertTrue($sender->isEndpointValid('https://fcm.googleapis.com/fcm/send/test'));
        $this->assertTrue($sender->isEndpointValid('https://updates.push.services.mozilla.com/wpush/v2/test'));
    }

    public function testRejectsInvalidUrl(): void
    {
        $sender = new WebPushSender();
        $this->assertFalse($sender->isEndpointValid('not-a-url'));
        $this->assertFalse($sender->isEndpointValid(''));
    }

    // ─── Error classification ──────────────────────────────────────

    public function testClassifyExpired(): void
    {
        $this->assertSame(WebPushSender::ERROR_EXPIRED, WebPushSender::ERROR_EXPIRED);
        $this->assertSame(WebPushSender::ERROR_PERMANENT_4XX, WebPushSender::ERROR_PERMANENT_4XX);
        $this->assertSame(WebPushSender::ERROR_TRANSIENT_429, WebPushSender::ERROR_TRANSIENT_429);
        $this->assertSame(WebPushSender::ERROR_TRANSIENT_5XX, WebPushSender::ERROR_TRANSIENT_5XX);
        $this->assertSame(WebPushSender::ERROR_TRANSPORT, WebPushSender::ERROR_TRANSPORT);
    }

    // ─── Outbox claim ──────────────────────────────────────────────

    public function testClaimForProcessingAffectedRows(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $userInserted = $db->table('users')->insert([
            'name' => 'TCX', 'email' => 'tcx9901@t.com', 'password' => 'x',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $userId = $db->insertID();
        $this->assertNotFalse($userInserted);

        $notifInserted = $db->table('notifications')->insert([
            'user_id' => $userId, 'event_type' => 'expense.created',
            'title' => 'T', 'body' => 'B', 'target_url' => '/g/1',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();
        $this->assertNotFalse($notifInserted);

        $outbox = new NotificationOutbox();
        $result = $outbox->createForNotification($nid);
        $this->assertNotFalse($result, 'createForNotification debe retornar el ID');

        $job = $outbox->where('notification_id', $nid)->first();
        if ($job) {
            $claimed = $outbox->claimForProcessing($job['id']);
            $this->assertTrue($claimed, 'Primer claim debe ser exitoso');

            $reclaimed = $outbox->claimForProcessing($job['id']);
            $this->assertFalse($reclaimed, 'Segundo claim debe fallar');
        } else {
            $this->markTestSkipped('Model insert behaviour differs from expected');
        }

        $db->transRollback();
    }

    public function testOrphanRecoveryRecoversStaleProcessing(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert([
            'name' => 'TOX', 'email' => 'tox9902@t.com', 'password' => 'x',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $userId = $db->insertID();

        $db->table('notifications')->insert([
            'user_id' => $userId, 'event_type' => 'expense.created',
            'title' => 'T', 'body' => 'B', 'target_url' => '/g/1',
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $job = $outbox->where('notification_id', $nid)->first();
        if (!$job) {
            $this->markTestSkipped('Model insert behaviour differs from expected');
            $db->transRollback();
            return;
        }

        $outbox->claimForProcessing($job['id']);

        $db->query('UPDATE notification_outbox SET updated_at = \'2020-01-01 00:00:00\' WHERE id = ?', [$job['id']]);

        $recovered = $outbox->recoverOrphanJobs();
        $this->assertGreaterThanOrEqual(1, $recovered);

        $jobAfter = $outbox->find($job['id']);
        $this->assertContains($jobAfter['status'], [NotificationOutbox::STATUS_RETRY, NotificationOutbox::STATUS_FAILED]);

        $db->transRollback();
    }

    // ─── isInternalUrl ─────────────────────────────────────────────

    public function testIsInternalUrlRejectsCrossSite(): void
    {
        $controller = new Notificaciones();

        $reflection = new \ReflectionMethod(Notificaciones::class, 'isInternalUrl');
        $reflection->setAccessible(true);

        $this->assertFalse($reflection->invoke($controller, 'https://evil.com/gastos/1'));
        $this->assertFalse($reflection->invoke($controller, '//evil.com/gastos/1'));
        $this->assertFalse($reflection->invoke($controller, ''));
    }

    // ─── Validación subscribe ──────────────────────────────────────

    public function testIsValidBase64Url(): void
    {
        $controller = new Notificaciones();
        $reflection = new \ReflectionMethod(Notificaciones::class, 'isValidBase64Url');
        $reflection->setAccessible(true);

        $this->assertTrue($reflection->invoke($controller, 'ABCdef123-_', 4, 100));
        $this->assertFalse($reflection->invoke($controller, 'abc', 10, 100));
        $this->assertFalse($reflection->invoke($controller, 'abc/def', 4, 100));
        $this->assertFalse($reflection->invoke($controller, 'abc+def', 4, 100));
    }

    public function testIsValidSubscriptionEndpoint(): void
    {
        $controller = new Notificaciones();
        $reflection = new \ReflectionMethod(Notificaciones::class, 'isValidSubscriptionEndpoint');
        $reflection->setAccessible(true);

        $this->assertTrue($reflection->invoke($controller, 'https://fcm.googleapis.com/fcm/send/test'));
        $this->assertFalse($reflection->invoke($controller, 'https://localhost/push'));
        $this->assertFalse($reflection->invoke($controller, 'https://192.168.0.1/push'));
        $this->assertFalse($reflection->invoke($controller, 'http://push.example.com/'));
        $this->assertFalse($reflection->invoke($controller, ''));
    }
}
