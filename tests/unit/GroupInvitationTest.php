<?php

use App\Models\GroupInvitation;
use CodeIgniter\Test\CIUnitTestCase;

final class GroupInvitationTest extends CIUnitTestCase
{
    protected $db;
    private int $userId;
    private int $groupId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = db_connect();
        $this->db->query("CREATE TABLE IF NOT EXISTS group_invitations (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, group_id INT UNSIGNED NOT NULL, invited_by_id INT UNSIGNED NOT NULL, email VARCHAR(255) NOT NULL, token_hash VARCHAR(64) NOT NULL, status VARCHAR(16) NOT NULL DEFAULT 'pending', expires_at DATETIME NOT NULL, accepted_by_id INT UNSIGNED NULL, accepted_at DATETIME NULL, cancelled_at DATETIME NULL, created_at DATETIME NULL, updated_at DATETIME NULL, UNIQUE KEY token_hash (token_hash), KEY group_email_status (group_id,email,status))");
        $this->userId = random_int(910000, 950000);
        $this->db->table('users')->insert(['id' => $this->userId, 'name' => 'Invitador', 'email' => "inviter{$this->userId}@test.local", 'password' => 'x', 'role' => 'user']);
        $this->db->table('grupos')->insert(['nombre' => 'Grupo invitaciones', 'created_by' => $this->userId]);
        $this->groupId = (int) $this->db->insertID();
    }

    protected function tearDown(): void
    {
        $this->db->table('group_invitations')->where('group_id', $this->groupId)->delete();
        $this->db->table('grupo_miembros')->where('grupo_id', $this->groupId)->delete();
        $this->db->table('grupos')->where('id', $this->groupId)->delete();
        $this->db->table('users')->where('id', $this->userId)->delete();
        parent::tearDown();
    }

    public function testCreatesHashedSevenDaySingleUseToken(): void
    {
        $result = (new GroupInvitation())->createInvitation($this->groupId, $this->userId, 'NUEVO@Example.com');
        $row = (new GroupInvitation())->find($result['id']);
        $this->assertSame('nuevo@example.com', $row['email']);
        $this->assertSame(hash('sha256', $result['token']), $row['token_hash']);
        $this->assertNotSame($result['token'], $row['token_hash']);
        $this->assertGreaterThan(strtotime('+6 days'), strtotime($row['expires_at']));
        $this->assertNotNull((new GroupInvitation())->validateToken($result['token']));
    }

    public function testRejectsDuplicatePendingInvitation(): void
    {
        $model = new GroupInvitation();
        $model->createInvitation($this->groupId, $this->userId, 'nuevo@example.com');
        $this->expectException(\RuntimeException::class);
        $model->createInvitation($this->groupId, $this->userId, 'nuevo@example.com');
    }

    public function testRenewInvalidatesOldTokenAndCancelInvalidatesNewToken(): void
    {
        $model = new GroupInvitation();
        $created = $model->createInvitation($this->groupId, $this->userId, 'nuevo@example.com');
        $renewed = $model->renew($created['id'], $this->groupId);
        $this->assertNull((new GroupInvitation())->validateToken($created['token']));
        $this->assertNotNull((new GroupInvitation())->validateToken($renewed['token']));
        $this->assertTrue((new GroupInvitation())->cancelInvitation($created['id'], $this->groupId));
        $this->assertNull((new GroupInvitation())->validateToken($renewed['token']));
    }

    public function testRegistrationControllerUsesTransactionAndRowLock(): void
    {
        $source = file_get_contents(APPPATH . 'Controllers/Invitaciones.php');
        $this->assertStringContainsString('transBegin()', $source);
        $this->assertStringContainsString('FOR UPDATE', $source);
        $this->assertStringContainsString("'status' => 'accepted'", $source);
        $this->assertStringContainsString('transRollback()', $source);
    }
}
