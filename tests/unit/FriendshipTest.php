<?php

use App\Models\Friendship;
use App\Models\Grupo;
use CodeIgniter\Test\CIUnitTestCase;

final class FriendshipTest extends CIUnitTestCase
{
    protected $db;
    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = db_connect();
        $this->db->query('CREATE TABLE IF NOT EXISTS friendships (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_low_id INT UNSIGNED NOT NULL, user_high_id INT UNSIGNED NOT NULL, requested_by_id INT UNSIGNED NOT NULL, status VARCHAR(16) NOT NULL DEFAULT \'pending\', responded_at DATETIME NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE KEY friendships_pair_unique (user_low_id, user_high_id))');
        $base = random_int(700000, 900000);
        foreach (['Uno', 'Dos', 'Tres'] as $offset => $name) {
            $id = $base + $offset;
            $this->db->table('users')->insert(['id' => $id, 'name' => $name, 'email' => "friend{$id}@test.local", 'password' => 'x', 'role' => 'user']);
            $this->ids[] = $id;
        }
    }

    protected function tearDown(): void
    {
        $this->db->table('friendships')->whereIn('user_low_id', $this->ids)->orWhereIn('user_high_id', $this->ids)->delete();
        $this->db->table('users')->whereIn('id', $this->ids)->delete();
        parent::tearDown();
    }

    public function testRequestNormalizesPairAndPreventsDuplicate(): void
    {
        $model = new Friendship();
        $model->request($this->ids[1], $this->ids[0]);
        $row = $model->between($this->ids[0], $this->ids[1]);
        $this->assertSame($this->ids[0], (int) $row['user_low_id']);
        $this->assertSame($this->ids[1], (int) $row['user_high_id']);
        $this->expectException(\RuntimeException::class);
        $model->request($this->ids[0], $this->ids[1]);
    }

    public function testOnlyRecipientCanAcceptAndRejectedCanBeResent(): void
    {
        $model = new Friendship();
        $model->request($this->ids[0], $this->ids[1]);
        $row = $model->between($this->ids[0], $this->ids[1]);
        $model->respond((int) $row['id'], $this->ids[1], false);
        $model->request($this->ids[1], $this->ids[0]);
        $pending = $model->between($this->ids[0], $this->ids[1]);
        $this->assertSame('pending', $pending['status']);
        $this->assertSame($this->ids[1], (int) $pending['requested_by_id']);
    }

    public function testAcceptedFriendAppearsForBothUsersAndCanBeRemoved(): void
    {
        $model = new Friendship();
        $model->request($this->ids[0], $this->ids[1]);
        $row = $model->between($this->ids[0], $this->ids[1]);
        $model->respond((int) $row['id'], $this->ids[1], true);
        $this->assertCount(1, $model->dashboard($this->ids[0])['friends']);
        $this->assertCount(1, (new Friendship())->dashboard($this->ids[1])['friends']);
        $this->assertTrue((new Friendship())->unfriend((int) $row['id'], $this->ids[0]));
    }

    public function testOnlyAcceptedFriendsAreAvailableToAddToGroup(): void
    {
        $friendships = new Friendship();
        $friendships->request($this->ids[0], $this->ids[1]);
        $row = $friendships->between($this->ids[0], $this->ids[1]);
        $friendships->respond((int) $row['id'], $this->ids[1], true);

        $this->db->table('grupos')->insert(['nombre' => 'Grupo amigos', 'created_by' => $this->ids[0]]);
        $groupId = (int) $this->db->insertID();
        $this->db->table('grupo_miembros')->insert(['grupo_id' => $groupId, 'user_id' => $this->ids[0], 'rol' => 'admin']);
        try {
            $available = (new Grupo())->getUsuariosDisponibles($groupId, $this->ids[0]);
            $this->assertSame([$this->ids[1]], array_map('intval', array_column($available, 'id')));
        } finally {
            $this->db->table('grupo_miembros')->where('grupo_id', $groupId)->delete();
            $this->db->table('grupos')->where('id', $groupId)->delete();
        }
    }
}
