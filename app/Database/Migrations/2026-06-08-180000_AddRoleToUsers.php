<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRoleToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'role' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'user',
                'after' => 'password',
            ],
        ]);

        $firstUser = $this->db->query('SELECT id FROM users ORDER BY id ASC LIMIT 1')->getRow();
        if ($firstUser) {
            $this->db->query('UPDATE users SET role = ? WHERE id = ?', ['admin', $firstUser->id]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'role');
    }
}
