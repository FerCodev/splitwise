<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColorToUsers extends Migration
{
    public function up()
    {
        // 'auto' = sin color personalizado; la UI usara el reservado
        // 'system' hasta que el usuario elija uno de la paleta.
        $this->forge->addColumn('users', [
            'color' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'auto',
                'after'      => 'role',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'color');
    }
}
