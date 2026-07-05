<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'title' => ['type' => 'VARCHAR', 'constraint' => 255],
            'body' => ['type' => 'VARCHAR', 'constraint' => 500],
            'target_url' => ['type' => 'VARCHAR', 'constraint' => 500],
            'read_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addKey(['user_id', 'read_at']);
        $this->forge->addKey(['user_id', 'created_at']);
        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
