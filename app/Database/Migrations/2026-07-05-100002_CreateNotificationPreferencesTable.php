<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationPreferencesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'push_enabled' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'expense_created' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'show_amounts' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addPrimaryKey('user_id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notification_preferences');
    }

    public function down()
    {
        $this->forge->dropTable('notification_preferences');
    }
}
