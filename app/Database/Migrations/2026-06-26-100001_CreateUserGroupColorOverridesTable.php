<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserGroupColorOverridesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'viewer_user_id'    => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'group_id'          => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'target_user_id'    => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'color'             => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        // Solo se permite un override por (viewer, grupo, target).
        $this->forge->addUniqueKey(['viewer_user_id', 'group_id', 'target_user_id']);
        $this->forge->addKey('viewer_user_id');
        $this->forge->addKey(['viewer_user_id', 'group_id']);
        $this->forge->addForeignKey('viewer_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('group_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('target_user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_group_color_overrides');
    }

    public function down()
    {
        $this->forge->dropTable('user_group_color_overrides');
    }
}
