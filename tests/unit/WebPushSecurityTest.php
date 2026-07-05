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
    // ─── DNS ─────────────────────────────────────────────────────────

    public function testRejectsLoopbackOnResolve(): void
    {$v = new EndpointValidator(fn () => ['127.0.0.1']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsPrivateIpOnResolve(): void
    {$v = new EndpointValidator(fn () => ['192.168.1.100']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsIpv6Loopback(): void
    {$v = new EndpointValidator(fn () => ['::1']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsIpv6LinkLocal(): void
    {$v = new EndpointValidator(fn () => ['fe80::1']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsPrivateIpv6(): void
    {$v = new EndpointValidator(fn () => ['fc00::1']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsMixedV4PublicV6Private(): void
    {$v = new EndpointValidator(fn () => ['8.8.8.8', '::1']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testAcceptsPublicIpv4(): void
    {$v = new EndpointValidator(fn () => ['8.8.8.8']); $this->assertTrue($v->isValid('https://x.com/p'));}
    public function testAcceptsPublicIpv6(): void
    {$v = new EndpointValidator(fn () => ['2001:4860:4860::8888']); $this->assertTrue($v->isValid('https://x.com/p'));}
    public function testRejectsEmptyDnsResult(): void
    {$v = new EndpointValidator(fn () => []); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsDnsFailure(): void
    {$v = new EndpointValidator(fn () => false); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsEmptyStringInResults(): void
    {$v = new EndpointValidator(fn () => ['']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsInvalidIpInResults(): void
    {$v = new EndpointValidator(fn () => ['not-an-ip']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsPublicPlusEmptyString(): void
    {$v = new EndpointValidator(fn () => ['8.8.8.8', '']); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testRejectsLiteralLoopback(): void
    {$v = new EndpointValidator(); $this->assertFalse($v->isValid('https://localhost/p')); $this->assertFalse($v->isValid('https://127.0.0.1/p'));}
    public function testRejectsLiteralPrivate(): void
    {$v = new EndpointValidator(); $this->assertFalse($v->isValid('https://192.168.1.1/p'));}
    public function testRejectsCredentialsInUrl(): void
    {$v = new EndpointValidator(fn () => ['8.8.8.8']); $this->assertFalse($v->isValid('https://u:p@h.com/p'));}
    public function testRejectsNonHttps(): void
    {$v = new EndpointValidator(); $this->assertFalse($v->isValid('http://x.com/p'));}

    // ─── classifyError ──────────────────────────────────────────────

    private function assertClassify(string $expected, bool $expired, int $code): void
    {
        $response = $code > 0 ? new \GuzzleHttp\Psr7\Response($code) : null;
        $report = $this->getMockBuilder(\Minishlink\WebPush\MessageSentReport::class)
            ->disableOriginalConstructor()->getMock();
        $report->method('isSubscriptionExpired')->willReturn($expired);
        $report->method('isSuccess')->willReturn(false);
        $report->method('getResponse')->willReturn($response);
        $this->assertSame($expected, (new WebPushSender())->classifyError($report));
    }
    public function testClassifyExpiredByMethod(): void {$this->assertClassify(WebPushSender::ERROR_EXPIRED, true, 200);}
    public function testClassifyExpired404(): void {$this->assertClassify(WebPushSender::ERROR_EXPIRED, false, 404);}
    public function testClassifyExpired410(): void {$this->assertClassify(WebPushSender::ERROR_EXPIRED, false, 410);}
    public function testClassifyTransient429(): void {$this->assertClassify(WebPushSender::ERROR_TRANSIENT_429, false, 429);}
    public function testClassifyTransient500(): void {$this->assertClassify(WebPushSender::ERROR_TRANSIENT_5XX, false, 500);}
    public function testClassifyTransient502(): void {$this->assertClassify(WebPushSender::ERROR_TRANSIENT_5XX, false, 502);}
    public function testClassifyPermanent400(): void {$this->assertClassify(WebPushSender::ERROR_PERMANENT_4XX, false, 400);}
    public function testClassifyPermanent401(): void {$this->assertClassify(WebPushSender::ERROR_PERMANENT_4XX, false, 401);}
    public function testClassifyPermanent403(): void {$this->assertClassify(WebPushSender::ERROR_PERMANENT_4XX, false, 403);}
    public function testClassifyTransport(): void {$this->assertClassify(WebPushSender::ERROR_TRANSPORT, false, 0);}

    // ─── Partial delivery with spy sender ───────────────────────────

    public function testPartialDeliveryOnlySendsPendingSubs(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'PD','email'=>'pd@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://a.com/p','endpoint_hash'=>hash('sha256','https://a.com/p'),'public_key'=>'a','auth_token'=>'b','created_at'=>$now,'updated_at'=>$now]);
        $subA = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://b.com/p','endpoint_hash'=>hash('sha256','https://b.com/p'),'public_key'=>'c','auth_token'=>'d','created_at'=>$now,'updated_at'=>$now]);
        $subB = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $receivedIds = [];
        $fake = $this->createSpySender($receivedIds, [
            'success' => [['pid' => $subA]],
            'details' => [
                ['push_subscription_id' => $subA, 'status' => 'success'],
                ['push_subscription_id' => $subB, 'status' => WebPushSender::ERROR_TRANSIENT_5XX],
            ],
        ]);

        $dispatcher = new NotificationDispatcher($fake);
        $r1 = $dispatcher->dispatch(10);
        $this->assertSame(1, $r1['sent']);
        $this->assertSame(1, $r1['retried']);

        $call1 = array_unique($receivedIds);
        sort($call1);
        $this->assertEquals([$subA, $subB], $call1, 'Primer envio debe incluir A y B');

        $deliveryModel = new NotificationDelivery();
        $delA = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$subA)->first();
        $this->assertSame(NotificationDelivery::STATUS_SUCCESS, $delA['status']);
        $delB = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$subB)->first();
        $this->assertSame(NotificationDelivery::STATUS_RETRY, $delB['status']);

        // vencer backoff
        $db->query('UPDATE notification_deliveries SET available_at=? WHERE id=?', [date('Y-m-d H:i:s'), $delB['id']]);
        $db->query('UPDATE notification_outbox SET available_at=? WHERE notification_id=?', [date('Y-m-d H:i:s'), $nid]);

        $receivedIds = [];
        $fake2 = $this->createSpySender($receivedIds, [
            'success' => [['pid' => $subB]],
            'details' => [['push_subscription_id' => $subB, 'status' => 'success']],
        ]);

        $r2 = (new NotificationDispatcher($fake2))->dispatch(10);
        $this->assertSame(1, $r2['sent']);

        $call2 = array_unique($receivedIds);
        $this->assertEquals([$subB], $call2, 'Segundo envio debe incluir solo B');
        $this->assertNotContains($subA, $call2, 'A no debe reenviarse');

        $delB2 = $deliveryModel->find($delB['id']);
        $this->assertSame(NotificationDelivery::STATUS_SUCCESS, $delB2['status']);
        $this->assertFalse($deliveryModel->hasUnfinishedForNotification($nid));

        $db->transRollback();
    }

    // ─── Outbox not completed when retry is future ──────────────────

    public function testOutboxStaysRetryWhileFutureDeliveryExists(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'FR','email'=>'fr@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://f.com/p','endpoint_hash'=>hash('sha256','https://f.com/p'),'public_key'=>'a','auth_token'=>'b','created_at'=>$now,'updated_at'=>$now]);
        $subF = $db->insertID();
        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        // poner delivery retry a 10 min en el futuro
        $deliveryModel = new NotificationDelivery();
        $deliveryModel->ensureForNotification($nid, [$subF]);
        $del = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$subF)->first();
        $db->query('UPDATE notification_deliveries SET status=?, available_at=? WHERE id=?', [NotificationDelivery::STATUS_RETRY, date('Y-m-d H:i:s', time() + 600), $del['id']]);

        $called = false;
        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () use (&$called) { $called = true; return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]]; });

        (new NotificationDispatcher($fake))->dispatch(10);
        $this->assertFalse($called, 'Sender no debe ser llamado si delivery no esta listo');

        $deliveryStatus = $deliveryModel->find($del['id'])['status'];
        $this->assertSame(NotificationDelivery::STATUS_RETRY, $deliveryStatus);
        $this->assertTrue($deliveryModel->hasUnfinishedForNotification($nid));

        $outboxJob = $outbox->where('notification_id', $nid)->first();
        $this->assertContains($outboxJob['status'], ['retry', 'processing']);
        $this->assertTrue(strtotime($outboxJob['available_at']) >= time());

        // vencer ambos y reenviar
        $db->query('UPDATE notification_deliveries SET available_at=? WHERE id=?', [date('Y-m-d H:i:s'), $del['id']]);
        $db->query('UPDATE notification_outbox SET status=?, available_at=? WHERE id=?', [NotificationOutbox::STATUS_RETRY, date('Y-m-d H:i:s'), $outboxJob['id']]);

        $called = false;
        $fake2 = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake2->method('isConfigured')->willReturn(true);
        $fake2->method('sendToAll')->willReturnCallback(function () use (&$called) { $called = true; return ['success'=>1,'expired'=>0,'failed'=>0,'details'=>[['push_subscription_id'=>99999,'status'=>'success']]]; });

        (new NotificationDispatcher($fake2))->dispatch(10);
        $this->assertTrue($called, 'Tras vencer backoff el sender debe ser llamado');

        $db->transRollback();
    }

    // ─── Disabled/absent subscriptions ──────────────────────────────

    public function testDisabledSubscriptionMarksDeliveryFailed(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'DS','email'=>'uds@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://ds.com/p','endpoint_hash'=>hash('sha256','https://ds.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $sid = $db->insertID();

        $db->table('notification_deliveries')->insert([
            'notification_id' => $nid, 'push_subscription_id' => $sid,
            'status' => NotificationDelivery::STATUS_PENDING, 'attempts' => 0,
            'available_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $db->table('push_subscriptions')->where('id', $sid)->update(['enabled' => 0]);

        (new NotificationDispatcher($this->createFakeSender(['success'=>[],'details'=>[]])))->dispatch(10);

        $status = $db->table('notification_deliveries')->select('status')->where('notification_id', $nid)->where('push_subscription_id', $sid)->get()->getRow();
        $this->assertNotNull($status, 'Row must exist');
        $this->assertSame(NotificationDelivery::STATUS_FAILED, $status->status, 'Delivery debe marcarse failed si sub esta disabled');

        $db->transRollback();
    }

    // ─── Cross-user isolation ───────────────────────────────────────

    public function testCrossUserIsolation(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'Owner','email'=>'ownercr@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $ownerId = $db->insertID();
        $db->table('users')->insert(['name'=>'Other','email'=>'othercr@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $otherId = $db->insertID();

        $db->table('notifications')->insert(['user_id'=>$ownerId,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$otherId,'endpoint'=>'https://othercr.com/p','endpoint_hash'=>hash('sha256','https://othercr.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $otherSubId = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        // Simula un delivery huerfano que apunta a sub de otro usuario
        $db->table('notification_deliveries')->insert([
            'notification_id' => $nid,
            'push_subscription_id' => $otherSubId,
            'status' => NotificationDelivery::STATUS_PENDING,
            'attempts' => 0,
            'available_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $sentIds = [];
        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function ($subs) use (&$sentIds) {
            foreach ($subs as $s) $sentIds[] = (int) $s['id'];
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        (new NotificationDispatcher($fake))->dispatch(10);

        $this->assertEmpty($sentIds, 'No debe enviar a suscripciones de otro usuario');

        $status = $db->table('notification_deliveries')->select('status')
            ->where('notification_id', $nid)->where('push_subscription_id', $otherSubId)
            ->get()->getRow();
        $this->assertNotNull($status);
        $this->assertSame(NotificationDelivery::STATUS_FAILED, $status->status, 'Delivery cross-user debe marcarse failed');

        $db->transRollback();
    }

    // ─── New device not added on retry ──────────────────────────────

    public function testNewDeviceNotAddedOnRetry(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'ND2','email'=>'nd2@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://a2.com/p','endpoint_hash'=>hash('sha256','https://a2.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subA = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $receivedIds = [];
        $fake1 = $this->createSpySender($receivedIds, [
            'success' => [],
            'details' => [['push_subscription_id' => $subA, 'status' => 'success']],
        ]);
        (new NotificationDispatcher($fake1))->dispatch(10);
        $this->assertContains($subA, $receivedIds);

        // agregar nuevo dispositivo C despues del primer envio
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://c2.com/p','endpoint_hash'=>hash('sha256','https://c2.com/p'),'public_key'=>'c','auth_token'=>'d','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subC = $db->insertID();

        $db->query('UPDATE notification_outbox SET status=?, available_at=? WHERE notification_id=?', [NotificationOutbox::STATUS_RETRY, date('Y-m-d H:i:s'), $nid]);

        $receivedIds2 = [];
        $fake2 = $this->createSpySender($receivedIds2, ['success'=>[],'details'=>[]]);
        (new NotificationDispatcher($fake2))->dispatch(10);

        $this->assertNotContains($subC, $receivedIds2, 'Segundo envio no debe incluir C agregado despues');
        $this->assertEmpty($receivedIds2, 'No debe haber envios (outbox completada)');

        $db->transRollback();
    }

    // ─── Outbox attempts independent from deliveries ────────────────

    public function testOutboxDoesNotCutDeliveryRetries(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'OC','email'=>'oc@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://oc.com/p','endpoint_hash'=>hash('sha256','https://oc.com/p'),'public_key'=>'a','auth_token'=>'b','created_at'=>$now,'updated_at'=>$now]);
        $sid = $db->insertID();
        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $deliveryModel = new NotificationDelivery();
        $deliveryModel->ensureForNotification($nid, [$sid]);

        $del = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$sid)->first();

        $totalClaims = 0;
        for ($i = 0; $i < 7; $i++) {
            $status = $deliveryModel->find($del['id'])['status'];
            if (!in_array($status, [NotificationDelivery::STATUS_PENDING, NotificationDelivery::STATUS_RETRY], true)) break;

            // simular que el delivery listo
            $db->query('UPDATE notification_deliveries SET available_at=? WHERE id=?', [date('Y-m-d H:i:s'), $del['id']]);
            $db->query('UPDATE notification_outbox SET status=?, available_at=? WHERE notification_id=?', [NotificationOutbox::STATUS_RETRY, date('Y-m-d H:i:s'), $nid]);

            $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
            $fake->method('isConfigured')->willReturn(true);
            $fake->method('sendToAll')->willReturnCallback(function ($subs) use ($sid) {
                return ['success'=>0,'expired'=>0,'failed'=>1,'details'=>[['push_subscription_id'=>$sid,'status'=>WebPushSender::ERROR_TRANSIENT_5XX]]];
            });

            $r = (new NotificationDispatcher($fake))->dispatch(10);
            $totalClaims += $r['processed'];
        }

        $final = $deliveryModel->find($del['id']);
        $this->assertSame(NotificationDelivery::STATUS_FAILED, $final['status'], 'Tras 5+ intentos delivery debe estar failed');
        $this->assertGreaterThanOrEqual(5, $totalClaims, 'Outbox debe permitir al menos 5 claims');
        $this->assertFalse($deliveryModel->hasUnfinishedForNotification($nid));

        $db->transRollback();
    }

    // ─── Base64Url ──────────────────────────────────────────────────

    public function testIsValidBase64UrlDecodes(): void
    {
        $c = new Notificaciones();
        $m = new \ReflectionMethod(Notificaciones::class, 'isValidBase64Url');
        $m->setAccessible(true);
        $this->assertTrue($m->invoke($c, rtrim(strtr(base64_encode(random_bytes(65)),'+/','-_'),'='), 65, 256));
        $this->assertFalse($m->invoke($c, rtrim(strtr(base64_encode(random_bytes(10)),'+/','-_'),'='), 65, 256));
        $this->assertFalse($m->invoke($c, '!@#', 1, 100));
    }

    // ─── helper ─────────────────────────────────────────────────────

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

    private function createSpySender(array &$receivedIds, array $config): WebPushSender
    {
        $fake = $this->getMockBuilder(WebPushSender::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isConfigured', 'sendToAll'])
            ->getMock();

        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function ($subs, $payload) use (&$receivedIds, $config) {
            foreach ($subs as $s) {
                $receivedIds[] = (int) $s['id'];
            }
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
}
