<?php

use App\Services\NotificationService;
use App\Services\NotificationRecipientResolver;
use App\Models\Notification;
use App\Models\NotificationOutbox;
use App\Models\NotificationPreference;

/**
 * Pruebas de consistencia de notificaciones.
 * Cada test usa transacción y rollback para evitar side effects.
 *
 * @internal
 */
final class NotificationConsistencyTest extends \CodeIgniter\Test\CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = db_connect();
        $this->db->transBegin();
    }

    protected function tearDown(): void
    {
        if ($this->db && $this->db->transEnabled) {
            $this->db->transRollback();
        }
        parent::tearDown();
    }

    private function seedUsers(array $users): void
    {
        $this->db->table('users')->insertBatch($users);
    }

    private function seedGrupo(int $id, string $nombre, int $createdBy): void
    {
        $this->db->table('grupos')->insert(['id' => $id, 'nombre' => $nombre, 'created_by' => $createdBy]);
    }

    private function seedMiembros(int $grupoId, array $miembros): void
    {
        $rows = [];
        foreach ($miembros as $m) {
            $rows[] = ['grupo_id' => $grupoId, 'user_id' => $m['user_id'], 'rol' => $m['rol'] ?? 'member'];
        }
        $this->db->table('grupo_miembros')->insertBatch($rows);
    }

    private function nextId(int &$seq): int
    {
        $seq++;
        return $seq;
    }

    // ─── Resolver recipientes ───────────────────────────────────────

    public function testRecipientResolverExcludesActor(): void
    {
        $s = 0;
        $a = $this->nextId($s); $m1 = $this->nextId($s); $m2 = $this->nextId($s);

        $this->seedUsers([
            ['id' => $a, 'name' => 'Actor', 'email' => "a{$a}@t.com", 'password' => 'x'],
            ['id' => $m1, 'name' => 'M1', 'email' => "m{$m1}@t.com", 'password' => 'x'],
            ['id' => $m2, 'name' => 'M2', 'email' => "m{$m2}@t.com", 'password' => 'x'],
        ]);
        $g = $this->nextId($s);
        $this->seedGrupo($g, 'Test', $a);
        $this->seedMiembros($g, [
            ['user_id' => $a, 'rol' => 'admin'],
            ['user_id' => $m1, 'rol' => 'member'],
            ['user_id' => $m2, 'rol' => 'member'],
        ]);

        $recipients = (new NotificationRecipientResolver())->resolveForGroup($g, $a);

        $this->assertCount(2, $recipients);
        $this->assertNotContains($a, $recipients);
        $this->assertContains($m1, $recipients);
        $this->assertContains($m2, $recipients);
    }

    public function testRecipientExcludesActorNotPagador(): void
    {
        $s = 100;
        $actor = $this->nextId($s); $pagador = $this->nextId($s); $otro = $this->nextId($s);

        $this->seedUsers([
            ['id' => $actor, 'name' => 'Actor', 'email' => "a{$actor}@t.com", 'password' => 'x'],
            ['id' => $pagador, 'name' => 'Pagador', 'email' => "p{$pagador}@t.com", 'password' => 'x'],
            ['id' => $otro, 'name' => 'Otro', 'email' => "o{$otro}@t.com", 'password' => 'x'],
        ]);
        $g = $this->nextId($s);
        $this->seedGrupo($g, 'Test', $pagador);
        $this->seedMiembros($g, [
            ['user_id' => $actor, 'rol' => 'member'],
            ['user_id' => $pagador, 'rol' => 'admin'],
            ['user_id' => $otro, 'rol' => 'member'],
        ]);

        $recipients = (new NotificationRecipientResolver())->resolveForGroup($g, $actor);

        $this->assertCount(2, $recipients);
        $this->assertNotContains($actor, $recipients);
        $this->assertContains($pagador, $recipients);
        $this->assertContains($otro, $recipients);
    }

    // ─── Notificaciones y outbox ────────────────────────────────────

    public function testNotificationCreatedForRecipientsExcludesActor(): void
    {
        $s = 200;
        $a = $this->nextId($s); $r1 = $this->nextId($s); $r2 = $this->nextId($s);

        $this->seedUsers([
            ['id' => $a, 'name' => 'Actor', 'email' => "a{$a}@t.com", 'password' => 'x'],
            ['id' => $r1, 'name' => 'R1', 'email' => "r{$r1}@t.com", 'password' => 'x'],
            ['id' => $r2, 'name' => 'R2', 'email' => "r{$r2}@t.com", 'password' => 'x'],
        ]);
        $g = $this->nextId($s);
        $this->seedGrupo($g, 'Julio', $a);
        $this->seedMiembros($g, [
            ['user_id' => $a, 'rol' => 'admin'],
            ['user_id' => $r1, 'rol' => 'member'],
            ['user_id' => $r2, 'rol' => 'member'],
        ]);

        $service = new NotificationService();
        $service->notifyExpenseCreated($g, 'Julio', $a, 'Actor', 500, 'Verdulería', 4900.00);

        $notifModel = new Notification();
        $nR1 = $notifModel->where('user_id', $r1)->first();
        $nR2 = $notifModel->where('user_id', $r2)->first();
        $nActor = $notifModel->where('user_id', $a)->first();

        $this->assertNotNull($nR1);
        $this->assertNotNull($nR2);
        $this->assertNull($nActor);
        $this->assertSame('Nuevo gasto en Julio', $nR1['title']);
        $this->assertStringContainsString('Actor', $nR1['body']);
        $this->assertStringContainsString('Verdulería', $nR1['body']);
        $this->assertStringEndsWith('/gastos/500', $nR1['target_url']);
        $this->assertSame('expense.created', $nR1['event_type']);
    }

    public function testOutboxCreatedForEachNotification(): void
    {
        $s = 300;
        $a = $this->nextId($s); $r = $this->nextId($s);

        $this->seedUsers([
            ['id' => $a, 'name' => 'Actor', 'email' => "a{$a}@t.com", 'password' => 'x'],
            ['id' => $r, 'name' => 'Recipient', 'email' => "r{$r}@t.com", 'password' => 'x'],
        ]);
        $g = $this->nextId($s);
        $this->seedGrupo($g, 'G4', $a);
        $this->seedMiembros($g, [
            ['user_id' => $a, 'rol' => 'admin'],
            ['user_id' => $r, 'rol' => 'member'],
        ]);

        $service = new NotificationService();
        $service->notifyExpenseCreated($g, 'G4', $a, 'Actor', 600, 'Cena', 3500.00);

        $notif = (new Notification())->where('user_id', $r)->first();
        $this->assertNotNull($notif);

        $outbox = (new NotificationOutbox())->where('notification_id', $notif['id'])->first();
        $this->assertNotNull($outbox);
        $this->assertSame('pending', $outbox['status']);
        $this->assertSame(0, (int) $outbox['attempts']);
    }

    // ─── Preferencias ───────────────────────────────────────────────

    public function testExpenseCreatedPreferenceOff(): void
    {
        $s = 400;
        $a = $this->nextId($s); $off = $this->nextId($s); $on = $this->nextId($s);

        $this->seedUsers([
            ['id' => $a, 'name' => 'Actor', 'email' => "a{$a}@t.com", 'password' => 'x'],
            ['id' => $off, 'name' => 'Off', 'email' => "o{$off}@t.com", 'password' => 'x'],
            ['id' => $on, 'name' => 'On', 'email' => "on{$on}@t.com", 'password' => 'x'],
        ]);
        $g = $this->nextId($s);
        $this->seedGrupo($g, 'G5', $a);
        $this->seedMiembros($g, [
            ['user_id' => $a, 'rol' => 'admin'],
            ['user_id' => $off, 'rol' => 'member'],
            ['user_id' => $on, 'rol' => 'member'],
        ]);

        (new NotificationPreference())->saveForUser($off, ['expense_created' => false]);
        (new NotificationPreference())->saveForUser($on, ['expense_created' => true]);

        (new NotificationService())->notifyExpenseCreated($g, 'G5', $a, 'Actor', 700, 'Taxi', 1200.00);

        $notifModel = new Notification();
        $this->assertNull($notifModel->where('user_id', $off)->first());
        $this->assertNotNull($notifModel->where('user_id', $on)->first());
    }

    public function testShowAmountsPreference(): void
    {
        $s = 500;
        $a = $this->nextId($s); $w = $this->nextId($s); $n = $this->nextId($s);

        $this->seedUsers([
            ['id' => $a, 'name' => 'Actor', 'email' => "a{$a}@t.com", 'password' => 'x'],
            ['id' => $w, 'name' => 'With', 'email' => "w{$w}@t.com", 'password' => 'x'],
            ['id' => $n, 'name' => 'No', 'email' => "n{$n}@t.com", 'password' => 'x'],
        ]);
        $g = $this->nextId($s);
        $this->seedGrupo($g, 'G6', $a);
        $this->seedMiembros($g, [
            ['user_id' => $a, 'rol' => 'admin'],
            ['user_id' => $w, 'rol' => 'member'],
            ['user_id' => $n, 'rol' => 'member'],
        ]);

        (new NotificationPreference())->saveForUser($w, ['show_amounts' => true]);
        (new NotificationPreference())->saveForUser($n, ['show_amounts' => false]);

        (new NotificationService())->notifyExpenseCreated($g, 'G6', $a, 'Actor', 800, 'Compra', 4500.00);

        $notifModel = new Notification();
        $nW = $notifModel->where('user_id', $w)->first();
        $nN = $notifModel->where('user_id', $n)->first();

        $this->assertStringContainsString('$', $nW['body']);
        $this->assertStringNotContainsString('$', $nN['body']);
        $this->assertStringContainsString('registró', $nN['body']);
        $this->assertStringContainsString('cargó', $nW['body']);
    }

    // ─── Lectura y pertenencia ──────────────────────────────────────

    public function testUnreadCount(): void
    {
        $s = 600;
        $u = $this->nextId($s);
        $this->seedUsers([['id' => $u, 'name' => 'U', 'email' => "u{$u}@t.com", 'password' => 'x']]);

        $notifModel = new Notification();
        $notifModel->insert(['user_id' => $u, 'event_type' => 'expense.created', 'title' => 'T1', 'body' => 'B1', 'target_url' => '/g/1']);
        $notifModel->insert(['user_id' => $u, 'event_type' => 'expense.created', 'title' => 'T2', 'body' => 'B2', 'target_url' => '/g/2']);
        $notifModel->insert(['user_id' => $u, 'event_type' => 'expense.created', 'title' => 'T3', 'body' => 'B3', 'target_url' => '/g/3', 'read_at' => date('Y-m-d H:i:s')]);

        $this->assertSame(2, $notifModel->countUnread($u));
    }

    public function testMarkOwnAsRead(): void
    {
        $s = 700;
        $u = $this->nextId($s);
        $this->seedUsers([['id' => $u, 'name' => 'U', 'email' => "u{$u}@t.com", 'password' => 'x']]);

        $notifModel = new Notification();
        $id = $notifModel->insert(['user_id' => $u, 'event_type' => 'expense.created', 'title' => 'T', 'body' => 'B', 'target_url' => '/g/1']);

        $this->assertTrue($notifModel->markAsRead($id, $u));
        $this->assertNotNull($notifModel->find($id)['read_at']);
    }

    public function testCannotOpenOtherUsersNotification(): void
    {
        $s = 800;
        $owner = $this->nextId($s); $attacker = $this->nextId($s);

        $this->seedUsers([
            ['id' => $owner, 'name' => 'Owner', 'email' => "o{$owner}@t.com", 'password' => 'x'],
            ['id' => $attacker, 'name' => 'Att', 'email' => "a{$attacker}@t.com", 'password' => 'x'],
        ]);

        $notifModel = new Notification();
        $id = $notifModel->insert(['user_id' => $owner, 'event_type' => 'expense.created', 'title' => 'T', 'body' => 'B', 'target_url' => '/g/1']);

        $this->assertFalse($notifModel->belongsToUser($id, $attacker));
        $this->assertTrue($notifModel->belongsToUser($id, $owner));
    }

    // ─── Valores predeterminados ────────────────────────────────────

    public function testPreferenceDefaultsWhenNoRow(): void
    {
        $prefs = (new NotificationPreference())->getForUser(99999);
        $this->assertTrue($prefs['push_enabled']);
        $this->assertTrue($prefs['expense_created']);
        $this->assertTrue($prefs['payment_events']);
        $this->assertTrue($prefs['group_events']);
        $this->assertTrue($prefs['show_amounts']);
        $this->assertSame(99999, $prefs['user_id']);
    }

    public function testPaymentNotificationOnlyTargetsCounterparty(): void
    {
        $payer = 9101;
        $receiver = 9102;
        $this->seedUsers([
            ['id' => $payer, 'name' => 'Payer', 'email' => 'payer9101@t.com', 'password' => 'x'],
            ['id' => $receiver, 'name' => 'Receiver', 'email' => 'receiver9102@t.com', 'password' => 'x'],
        ]);

        (new NotificationService())->notifyPaymentCreated(
            'Viaje', $payer, 'Payer', 44, $payer, $receiver, 2500.0
        );

        $notification = (new Notification())->where('user_id', $receiver)->first();
        $this->assertNotNull($notification);
        $this->assertSame('payment.created', $notification['event_type']);
        $this->assertStringEndsWith('/pagos/44', $notification['target_url']);
        $this->assertNull((new Notification())->where('user_id', $payer)->first());
    }

    public function testExpenseUpdateOnlyTargetsAffectedUsers(): void
    {
        $actor = 9201;
        $affected = 9202;
        $unaffected = 9203;
        $this->seedUsers([
            ['id' => $actor, 'name' => 'Actor', 'email' => 'actor9201@t.com', 'password' => 'x'],
            ['id' => $affected, 'name' => 'Affected', 'email' => 'affected9202@t.com', 'password' => 'x'],
            ['id' => $unaffected, 'name' => 'Unaffected', 'email' => 'unaffected9203@t.com', 'password' => 'x'],
        ]);

        (new NotificationService())->notifyExpenseUpdated(
            'Casa', $actor, 'Actor', 55, 'Supermercado', 3200.0, [$actor, $affected]
        );

        $this->assertNotNull((new Notification())->where('user_id', $affected)->first());
        $this->assertNull((new Notification())->where('user_id', $actor)->first());
        $this->assertNull((new Notification())->where('user_id', $unaffected)->first());
    }

    public function testClosedGroupGivesDebtorActionableMessage(): void
    {
        $actor = 9301;
        $debtor = 9302;
        $creditor = 9303;
        $this->seedUsers([
            ['id' => $actor, 'name' => 'Actor', 'email' => 'actor9301@t.com', 'password' => 'x'],
            ['id' => $debtor, 'name' => 'Debtor', 'email' => 'debtor9302@t.com', 'password' => 'x'],
            ['id' => $creditor, 'name' => 'Creditor', 'email' => 'creditor9303@t.com', 'password' => 'x'],
        ]);
        $groupId = 9304;
        $this->seedGrupo($groupId, 'Vacaciones', $actor);
        $this->seedMiembros($groupId, [
            ['user_id' => $actor, 'rol' => 'admin'],
            ['user_id' => $debtor],
            ['user_id' => $creditor],
        ]);

        (new NotificationService())->notifyGroupStateChanged(
            $groupId, 'Vacaciones', $actor, 'Actor', 'cerrado', [[
                'deudor_id' => $debtor,
                'acreedor_id' => $creditor,
                'monto' => 1800.0,
            ]]
        );

        $debtorNotification = (new Notification())->where('user_id', $debtor)->first();
        $this->assertStringContainsString('pagos pendientes', $debtorNotification['body']);
        $this->assertStringEndsWith("/grupos/{$groupId}/balance", $debtorNotification['target_url']);
        $this->assertNotNull((new Notification())->where('user_id', $creditor)->first());
        $this->assertNotNull((new Notification())->where('user_id', $actor)->first());
    }
}
