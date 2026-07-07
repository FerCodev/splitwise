<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGroupInvitations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'group_id' => ['type' => 'INT', 'unsigned' => true],
            'invited_by_id' => ['type' => 'INT', 'unsigned' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'pending'],
            'expires_at' => ['type' => 'DATETIME'],
            'accepted_by_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'accepted_at' => ['type' => 'DATETIME', 'null' => true],
            'cancelled_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('token_hash');
        $this->forge->addKey(['group_id', 'email', 'status']);
        $this->forge->addForeignKey('group_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('invited_by_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('accepted_by_id', 'users', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('group_invitations');
    }

    public function down()
    {
        $this->forge->dropTable('group_invitations');
    }
}
