<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoriaToGastos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('gastos', [
            'categoria' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'default' => 'Otros',
                'null' => false,
                'after' => 'monto',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('gastos', 'categoria');
    }
}
