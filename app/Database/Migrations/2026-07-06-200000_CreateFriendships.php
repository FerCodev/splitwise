<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFriendships extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_low_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'user_high_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'requested_by_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'pending'],
            'responded_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_low_id', 'user_high_id'], 'friendships_pair_unique');
        $this->forge->addKey(['requested_by_id', 'status']);
        $this->forge->addForeignKey('user_low_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_high_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('requested_by_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('friendships');

        $this->forge->addColumn('notification_preferences', [
            'friend_events' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'after' => 'group_events'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('notification_preferences', 'friend_events');
        $this->forge->dropTable('friendships');
    }
}
