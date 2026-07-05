<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePushSubscriptionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'endpoint' => ['type' => 'VARCHAR', 'constraint' => 1024],
            'endpoint_hash' => ['type' => 'CHAR', 'constraint' => 64],
            'public_key' => ['type' => 'VARCHAR', 'constraint' => 255],
            'auth_token' => ['type' => 'VARCHAR', 'constraint' => 255],
            'content_encoding' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'aes128gcm'],
            'user_agent' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'last_success_at' => ['type' => 'DATETIME', 'null' => true],
            'last_failure_at' => ['type' => 'DATETIME', 'null' => true],
            'failure_count' => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey('endpoint_hash');
        $this->forge->addKey('user_id');
        $this->forge->createTable('push_subscriptions');
    }

    public function down()
    {
        $this->forge->dropTable('push_subscriptions');
    }
}
