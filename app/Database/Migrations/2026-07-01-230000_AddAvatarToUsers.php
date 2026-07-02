<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'avatar_filename' => [
                'type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'default' => null, 'after' => 'color',
            ],
            'avatar_updated_at' => [
                'type' => 'DATETIME', 'null' => true, 'default' => null, 'after' => 'avatar_filename',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['avatar_filename', 'avatar_updated_at']);
    }
}
