<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEstadoToGrupos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('grupos', [
            'estado' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'activo',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('grupos', 'estado');
    }
}
