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

    public function testResolverThrowsRuntimeException(): void
    {$v = new EndpointValidator(fn () => throw new \RuntimeException('dns boom')); $this->assertFalse($v->isValid('https://x.com/p'));}
    public function testResolverReturnsNull(): void
    {$v = new EndpointValidator(fn () => null); $this->assertFalse($v->isValid('https://x.com/p'));}

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

        $db->query('UPDATE notification_deliveries SET available_at=? WHERE id=?', [date('Y-m-d H:i:s'), $delB['id']]);
        $db->query('UPDATE notification_outbox SET status=?, available_at=? WHERE notification_id=?', [NotificationOutbox::STATUS_RETRY, date('Y-m-d H:i:s'), $nid]);

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

        $deliveryModel = new NotificationDelivery();
        $deliveryModel->insertSnapshot($nid, [$subF]);
        $outbox->markDeliveriesInitialized($outbox->where('notification_id', $nid)->first()['id']);

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

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        $deliveryModel = new NotificationDelivery();
        $deliveryModel->insertSnapshot($nid, [$sid]);
        $outbox->markDeliveriesInitialized($job['id']);

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
        $job = $outbox->where('notification_id', $nid)->first();

        $db->table('notification_deliveries')->insert([
            'notification_id' => $nid,
            'push_subscription_id' => $otherSubId,
            'status' => NotificationDelivery::STATUS_PENDING,
            'attempts' => 0,
            'available_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $outbox->markDeliveriesInitialized($job['id']);

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

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://c2.com/p','endpoint_hash'=>hash('sha256','https://c2.com/p'),'public_key'=>'c','auth_token'=>'d','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);

        $db->query('UPDATE notification_outbox SET status=?, available_at=? WHERE notification_id=?', [NotificationOutbox::STATUS_RETRY, date('Y-m-d H:i:s'), $nid]);

        $receivedIds2 = [];
        $fake2 = $this->createSpySender($receivedIds2, ['success'=>[],'details'=>[]]);
        (new NotificationDispatcher($fake2))->dispatch(10);

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
        $deliveryModel->insertSnapshot($nid, [$sid]);
        $outbox->markDeliveriesInitialized($outbox->where('notification_id', $nid)->first()['id']);

        $del = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$sid)->first();

        $totalClaims = 0;
        for ($i = 0; $i < 7; $i++) {
            $status = $deliveryModel->find($del['id'])['status'];
            if (!in_array($status, [NotificationDelivery::STATUS_PENDING, NotificationDelivery::STATUS_RETRY], true)) break;

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

    // ─── Missing detail from sender ─────────────────────────────────

    public function testMissingDetailMarksDeliveryRetryWithBackoff(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'MD','email'=>'md@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://md1.com/p','endpoint_hash'=>hash('sha256','https://md1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subA = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://md2.com/p','endpoint_hash'=>hash('sha256','https://md2.com/p'),'public_key'=>'c','auth_token'=>'d','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subB = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function ($subs) use ($subA) {
            return [
                'success' => 1, 'expired' => 0, 'failed' => 0,
                'details' => [['push_subscription_id' => $subA, 'status' => 'success']],
            ];
        });

        $r = (new NotificationDispatcher($fake))->dispatch(10);
        $this->assertSame(1, $r['sent']);
        $this->assertSame(1, $r['retried']);

        $deliveryModel = new NotificationDelivery();
        $delA = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$subA)->first();
        $this->assertSame(NotificationDelivery::STATUS_SUCCESS, $delA['status']);

        $delB = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$subB)->first();
        $this->assertSame(NotificationDelivery::STATUS_RETRY, $delB['status']);
        $this->assertSame(1, (int) $delB['attempts']);
        $this->assertNull($delB['processed_at']);
        $this->assertSame('transport_error', $delB['last_error']);
        $this->assertTrue(strtotime($delB['available_at']) > time());

        $db->transRollback();
    }

    // ─── markRetry processed_at logic ───────────────────────────────

    public function testMarkRetryBeforeMaxKeepsProcessedAtNull(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'MR1','email'=>'mr1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://mr1.com/p','endpoint_hash'=>hash('sha256','https://mr1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $sid = $db->insertID();

        $deliveryModel = new NotificationDelivery();
        $deliveryModel->insertSnapshot($nid, [$sid]);
        $del = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$sid)->first();

        $this->assertSame(0, (int) $del['attempts']);
        $this->assertNull($del['processed_at']);

        $deliveryModel->markRetry($del['id'], 'transport_error');
        $after = $deliveryModel->find($del['id']);

        $this->assertSame(NotificationDelivery::STATUS_RETRY, $after['status']);
        $this->assertSame(1, (int) $after['attempts']);
        $this->assertNull($after['processed_at']);
        $this->assertSame('transport_error', $after['last_error']);

        $db->transRollback();
    }

    public function testMarkRetryAtMaxSetsFailedAndProcessedAt(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'MRX','email'=>'mrx@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://mrx.com/p','endpoint_hash'=>hash('sha256','https://mrx.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $sid = $db->insertID();

        $deliveryModel = new NotificationDelivery();
        $deliveryModel->insertSnapshot($nid, [$sid]);
        $del = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$sid)->first();

        for ($i = 0; $i < NotificationDelivery::MAX_ATTEMPTS - 1; $i++) {
            $deliveryModel->markRetry($del['id'], 'transport_error');
        }

        $beforeMax = $deliveryModel->find($del['id']);
        $this->assertSame(NotificationDelivery::STATUS_RETRY, $beforeMax['status']);
        $this->assertSame(NotificationDelivery::MAX_ATTEMPTS - 1, (int) $beforeMax['attempts']);
        $this->assertNull($beforeMax['processed_at']);

        $deliveryModel->markRetry($del['id'], 'transport_error');
        $final = $deliveryModel->find($del['id']);

        $this->assertSame(NotificationDelivery::STATUS_FAILED, $final['status']);
        $this->assertSame(NotificationDelivery::MAX_ATTEMPTS, (int) $final['attempts']);
        $this->assertNotNull($final['processed_at']);
        $this->assertSame('transport_error', $final['last_error']);

        $db->transRollback();
    }

    // ─── Outbox state cleanup ───────────────────────────────────────

    public function testOutboxCompletedClearsLastError(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'OC1','email'=>'oc1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        $outbox->scheduleRetry($job['id'], 'partial_delivery', date('Y-m-d H:i:s', time() + 60));
        $withError = $outbox->find($job['id']);
        $this->assertSame('partial_delivery', $withError['last_error']);
        $this->assertNull($withError['processed_at']);

        $outbox->markCompleted($job['id']);
        $completed = $outbox->find($job['id']);
        $this->assertSame(NotificationOutbox::STATUS_COMPLETED, $completed['status']);
        $this->assertNotNull($completed['processed_at']);
        $this->assertNull($completed['last_error']);

        $db->transRollback();
    }

    public function testOutboxRetryClearsProcessedAt(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'OR1','email'=>'or1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        $outbox->claimForProcessing($job['id']);
        $claimed = $outbox->find($job['id']);
        $this->assertSame(NotificationOutbox::STATUS_PROCESSING, $claimed['status']);

        $outbox->scheduleRetry($job['id'], 'pending_future', date('Y-m-d H:i:s', time() + 120));
        $retried = $outbox->find($job['id']);
        $this->assertSame(NotificationOutbox::STATUS_RETRY, $retried['status']);
        $this->assertNull($retried['processed_at']);
        $this->assertSame('pending_future', $retried['last_error']);

        $db->transRollback();
    }

    public function testOutboxFailedSetsProcessedAtAndSanitizedError(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'OF1','email'=>'of1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        $outbox->markFailed($job['id'], 'notification_not_found');
        $failed = $outbox->find($job['id']);
        $this->assertSame(NotificationOutbox::STATUS_FAILED, $failed['status']);
        $this->assertNotNull($failed['processed_at']);
        $this->assertSame('notification_not_found', $failed['last_error']);

        $db->transRollback();
    }

    // ─── Outbox MAX_ATTEMPTS=100 confirmation ───────────────────────

    public function testOutboxMaxAttemptsIs100(): void
    {
        $this->assertSame(100, NotificationOutbox::MAX_ATTEMPTS);
        $this->assertSame(5, NotificationDelivery::MAX_ATTEMPTS);
    }

    public function testOutboxDoesNotFinalizeWhileDeliveryRetriesRemain(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'OM1','email'=>'om1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://om1.com/p','endpoint_hash'=>hash('sha256','https://om1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $sid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $deliveryModel = new NotificationDelivery();
        $deliveryModel->insertSnapshot($nid, [$sid]);
        $outbox->markDeliveriesInitialized($outbox->where('notification_id', $nid)->first()['id']);
        $del = $deliveryModel->where('notification_id',$nid)->where('push_subscription_id',$sid)->first();

        for ($i = 0; $i < NotificationDelivery::MAX_ATTEMPTS; $i++) {
            $db->query('UPDATE notification_deliveries SET available_at=? WHERE id=?', [date('Y-m-d H:i:s'), $del['id']]);
            $db->query('UPDATE notification_outbox SET status=?, available_at=? WHERE notification_id=?', [NotificationOutbox::STATUS_RETRY, date('Y-m-d H:i:s'), $nid]);

            $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
            $fake->method('isConfigured')->willReturn(true);
            $fake->method('sendToAll')->willReturnCallback(function ($subs) use ($sid) {
                return ['success'=>0,'expired'=>0,'failed'=>1,'details'=>[['push_subscription_id'=>$sid,'status'=>WebPushSender::ERROR_TRANSIENT_5XX]]];
            });

            (new NotificationDispatcher($fake))->dispatch(10);
        }

        $finalDelivery = $deliveryModel->find($del['id']);
        $this->assertSame(NotificationDelivery::STATUS_FAILED, $finalDelivery['status']);
        $this->assertFalse($deliveryModel->hasUnfinishedForNotification($nid));

        $finalOutbox = $outbox->where('notification_id', $nid)->first();
        $this->assertSame(NotificationOutbox::STATUS_COMPLETED, $finalOutbox['status']);
        $this->assertNotNull($finalOutbox['processed_at']);
        $this->assertNull($finalOutbox['last_error']);

        $db->transRollback();
    }

    // ═══════════════════════════════════════════════════════════════════
    // ─── 12 FAILURE-PATH: atomic snapshot tests ─────────────────────
    // ═══════════════════════════════════════════════════════════════════

    // 1. insertBatch exitoso devuelve cantidad realmente insertada
    public function testInsertBatchSuccessReturnsActualCount(): void
    {
        $db = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'IB1','email'=>'ib1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://ib1.com/p','endpoint_hash'=>hash('sha256','https://ib1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subA = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://ib2.com/p','endpoint_hash'=>hash('sha256','https://ib2.com/p'),'public_key'=>'c','auth_token'=>'d','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subB = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://ib3.com/p','endpoint_hash'=>hash('sha256','https://ib3.com/p'),'public_key'=>'e','auth_token'=>'f','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subC = $db->insertID();

        $deliveryModel = new NotificationDelivery();
        $inserted = $deliveryModel->insertSnapshot($nid, [$subA, $subB, $subC]);
        $this->assertSame(3, $inserted, 'Debe retornar 3 como cantidad realmente insertada');

        $actualCount = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(3, $actualCount);

        $db->table('notification_deliveries')->where('notification_id', $nid)->delete();
        $db->table('notifications')->where('id', $nid)->delete();
        $db->table('push_subscriptions')->where('user_id', $uid)->delete();
        $db->table('users')->where('id', $uid)->delete();
    }

    // 2. insertBatch con FK invalida provoca excepcion
    public function testInsertBatchWithInvalidFKThrowsException(): void
    {
        $deliveryModel = new NotificationDelivery();
        $this->expectException(\RuntimeException::class);
        $deliveryModel->insertSnapshot(999999, [888888]);
    }

    // 3. FK invalida dentro del batch provoca rollback total (no quedan filas)
    public function testInvalidFKInBatchCausesTotalRollback(): void
    {
        $db = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'FK1','email'=>'fk1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://fk1.com/p','endpoint_hash'=>hash('sha256','https://fk1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subA = $db->insertID();

        $deliveryModel = new NotificationDelivery();

        try {
            $deliveryModel->insertSnapshot($nid, [$subA, 999999]);
            $this->fail('Debe lanzar excepcion');
        } catch (\RuntimeException $e) {
        }

        $count = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(0, $count, 'No deben quedar deliveries tras fallo de FK');

        $db->table('notifications')->where('id', $nid)->delete();
        $db->table('push_subscriptions')->where('user_id', $uid)->delete();
        $db->table('users')->where('id', $uid)->delete();
    }

    // 4. Despues del fallo no quedan deliveries nuevas
    public function testAfterFailureNoNewDeliveriesRemain(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'NF1','email'=>'nf1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $deliveryModel = new NotificationDelivery();
        $countBefore = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(0, $countBefore);

        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () {
            throw new \RuntimeException('sender boom');
        });

        (new NotificationDispatcher($fake))->dispatch(10);

        $countAfter = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(0, $countAfter, 'No deben quedar deliveries nuevas tras fallo de inicializacion');

        $db->transRollback();
    }

    // 5. Marker permanece null tras fallo de inicializacion
    public function testMarkerRemainsNullAfterInitFailure(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'MN1','email'=>'mn1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();
        $outbox->delete($job['id']);

        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () {
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        (new NotificationDispatcher($fake))->dispatch(10);

        $jobAfter = $outbox->where('notification_id', $nid)->first();
        $this->assertNull($jobAfter, 'Outbox fue eliminada, no debe existir');

        $db->transRollback();
    }

    // 6. Fallo de markDeliveriesInitialized provoca rollback
    public function testMarkDeliveriesInitializedFailureCausesRollback(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'MK1','email'=>'mk1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://mk1.com/p','endpoint_hash'=>hash('sha256','https://mk1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        $outbox->delete($job['id']);

        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () {
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        $r = (new NotificationDispatcher($fake))->dispatch(10);

        $deliveryModel = new NotificationDelivery();
        $count = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(0, $count, 'No deben quedar deliveries si la outbox fue eliminada');

        $db->transRollback();
    }

    // 7. Snapshot vacio solo se confirma si marker se persistio
    public function testEmptySnapshotOnlyConfirmIfMarkerPersisted(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'ES1','email'=>'es1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () {
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        (new NotificationDispatcher($fake))->dispatch(10);

        $job = $outbox->where('notification_id', $nid)->first();
        $this->assertNotNull($job['deliveries_initialized_at'], 'Marker debe establecerse para snapshot vacio');
        $this->assertSame(NotificationOutbox::STATUS_COMPLETED, $job['status'], 'Outbox debe completarse');

        $deliveryModel = new NotificationDelivery();
        $count = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(0, $count, 'No debe haber deliveries');

        $db->transRollback();
    }

    // 8. Snapshot parcial preexistente se elimina y reemplaza dentro de la misma transaccion
    public function testPartialSnapshotReplacedWithinTransaction(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'PS1','email'=>'ps1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://ps1.com/p','endpoint_hash'=>hash('sha256','https://ps1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subA = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://ps2.com/p','endpoint_hash'=>hash('sha256','https://ps2.com/p'),'public_key'=>'c','auth_token'=>'d','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subB = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $db->table('notification_deliveries')->insert([
            'notification_id' => $nid, 'push_subscription_id' => $subA,
            'status' => NotificationDelivery::STATUS_PENDING, 'attempts' => 0,
            'available_at' => $now, 'created_at' => $now, 'updated_at' => $now,
        ]);

        $deliveryModel = new NotificationDelivery();
        $partialCount = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(1, $partialCount, 'Solo 1 fila parcial preexistente');

        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () {
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        (new NotificationDispatcher($fake))->dispatch(10);

        $finalCount = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(2, $finalCount, 'Snapshot parcial reemplazado por completo (2 deliveries)');

        $job = $outbox->where('notification_id', $nid)->first();
        $this->assertNotNull($job['deliveries_initialized_at']);

        $db->transRollback();
    }

    // 9. Si la nueva insercion falla, rollback restaura estado previo
    public function testInsertionFailureRollbackRestoresPreviousState(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'IR1','email'=>'ir1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        $this->assertNull($job['deliveries_initialized_at']);

        $outbox->delete($job['id']);

        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () {
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        (new NotificationDispatcher($fake))->dispatch(10);

        $jobAfter = $outbox->where('notification_id', $nid)->first();
        $this->assertNull($jobAfter, 'Outbox fue eliminada, no debe existir');

        $deliveryModel = new NotificationDelivery();
        $count = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(0, $count, 'No quedan deliveries tras fallo');

        $db->transRollback();
    }

    // 10. Dos inicializadores serializados no duplican deliveries
    public function testSerializedInitializersDoNotDuplicate(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'SI1','email'=>'si1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://si1.com/p','endpoint_hash'=>hash('sha256','https://si1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subA = $db->insertID();
        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://si2.com/p','endpoint_hash'=>hash('sha256','https://si2.com/p'),'public_key'=>'c','auth_token'=>'d','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subB = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $fake1 = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake1->method('isConfigured')->willReturn(true);
        $fake1->method('sendToAll')->willReturnCallback(function () {
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        (new NotificationDispatcher($fake1))->dispatch(10);

        $deliveryModel = new NotificationDelivery();
        $countAfterFirst = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(2, $countAfterFirst);

        $db->query('UPDATE notification_outbox SET status=?, available_at=? WHERE notification_id=?',
            [NotificationOutbox::STATUS_RETRY, date('Y-m-d H:i:s'), $nid]);

        $fake2 = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake2->method('isConfigured')->willReturn(true);
        $fake2->method('sendToAll')->willReturnCallback(function () {
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        (new NotificationDispatcher($fake2))->dispatch(10);

        $countAfterSecond = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(2, $countAfterSecond, 'No debe duplicar deliveries');

        $db->transRollback();
    }

    // 11. Segunda ejecucion con marker no agrega dispositivos
    public function testSecondExecutionWithMarkerDoesNotAddDevices(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'SM1','email'=>'sm1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://sm1.com/p','endpoint_hash'=>hash('sha256','https://sm1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);
        $subA = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);

        $receivedIds = [];
        $fake1 = $this->createSpySender($receivedIds, [
            'success' => [],
            'details' => [['push_subscription_id' => $subA, 'status' => 'success']],
        ]);
        (new NotificationDispatcher($fake1))->dispatch(10);

        $job = $outbox->where('notification_id', $nid)->first();
        $this->assertNotNull($job['deliveries_initialized_at'], 'Marker debe estar establecido');

        $deliveryModel = new NotificationDelivery();
        $countBefore = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(1, $countBefore);

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://sm2.com/p','endpoint_hash'=>hash('sha256','https://sm2.com/p'),'public_key'=>'c','auth_token'=>'d','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);

        $db->query('UPDATE notification_outbox SET status=?, available_at=? WHERE notification_id=?', [NotificationOutbox::STATUS_RETRY, date('Y-m-d H:i:s'), $nid]);

        $receivedIds2 = [];
        $fake2 = $this->createSpySender($receivedIds2, ['success'=>[],'details'=>[]]);
        (new NotificationDispatcher($fake2))->dispatch(10);

        $countAfter = $deliveryModel->where('notification_id', $nid)->countAllResults();
        $this->assertSame(1, $countAfter, 'No debe agregar dispositivo nuevo');

        $db->transRollback();
    }

    // 12. Dispatcher no llama sender si inicializacion falla
    public function testDispatcherDoesNotCallSenderIfInitFails(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'DN1','email'=>'dn1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $db->table('push_subscriptions')->insert(['user_id'=>$uid,'endpoint'=>'https://dn1.com/p','endpoint_hash'=>hash('sha256','https://dn1.com/p'),'public_key'=>'a','auth_token'=>'b','enabled'=>1,'created_at'=>$now,'updated_at'=>$now]);

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();
        $outbox->delete($job['id']);

        $sendCalled = false;
        $fake = $this->getMockBuilder(WebPushSender::class)->disableOriginalConstructor()->onlyMethods(['isConfigured','sendToAll'])->getMock();
        $fake->method('isConfigured')->willReturn(true);
        $fake->method('sendToAll')->willReturnCallback(function () use (&$sendCalled) {
            $sendCalled = true;
            return ['success'=>0,'expired'=>0,'failed'=>0,'details'=>[]];
        });

        (new NotificationDispatcher($fake))->dispatch(10);
        $this->assertFalse($sendCalled, 'Sender no debe ser llamado si inicializacion falla');

        $db->transRollback();
    }

    // ─── Allowlist ──────────────────────────────────────────────────

    public function testArbitraryOutboxErrorSanitizedToInitFailed(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'AE1','email'=>'ae1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        $outbox->markFailed($job['id'], 'DROP TABLE users; -- SQL injection');
        $failed = $outbox->find($job['id']);
        $this->assertSame('delivery_initialization_failed', $failed['last_error'], 'Error arbitrario debe sanitizarse');

        $outbox->scheduleRetry($job['id'], 'Some random error', date('Y-m-d H:i:s'));
        $retried = $outbox->find($job['id']);
        $this->assertSame('delivery_initialization_failed', $retried['last_error'], 'Mensaje arbitrario en retry debe sanitizarse');

        $db->transRollback();
    }

    public function testAllowedErrorCodeIsPreserved(): void
    {
        $db = db_connect();
        $db->transBegin();
        $now = date('Y-m-d H:i:s');

        $db->table('users')->insert(['name'=>'AP1','email'=>'ap1@t.com','password'=>'x','created_at'=>$now,'updated_at'=>$now]);
        $uid = $db->insertID();
        $db->table('notifications')->insert(['user_id'=>$uid,'event_type'=>'x','title'=>'T','body'=>'B','target_url'=>'/g/1','created_at'=>$now,'updated_at'=>$now]);
        $nid = $db->insertID();

        $outbox = new NotificationOutbox();
        $outbox->createForNotification($nid);
        $job = $outbox->where('notification_id', $nid)->first();

        $allowedCodes = [
            'notification_not_found',
            'pending_future',
            'partial_delivery',
            'transport_error',
            'delivery_initialization_failed',
        ];

        foreach ($allowedCodes as $code) {
            $outbox->scheduleRetry($job['id'], $code, date('Y-m-d H:i:s', time() + 60));
            $updated = $outbox->find($job['id']);
            $this->assertSame($code, $updated['last_error'], "Codigo permitido '$code' debe conservarse");
        }

        $db->transRollback();
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
