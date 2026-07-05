<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationOutboxTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'notification_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'attempts' => ['type' => 'SMALLINT', 'constraint' => 5, 'unsigned' => true, 'default' => 0],
            'available_at' => ['type' => 'DATETIME', 'null' => false],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'last_error' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('notification_id', 'notifications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey('notification_id');
        $this->forge->addKey('status');
        $this->forge->addKey('available_at');
        $this->forge->createTable('notification_outbox');
    }

    public function down()
    {
        $this->forge->dropTable('notification_outbox');
    }
}
