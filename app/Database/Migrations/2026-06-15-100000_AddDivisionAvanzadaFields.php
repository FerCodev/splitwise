<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDivisionAvanzadaFields extends Migration
{
    public function up()
    {
        $this->forge->addColumn('gasto_divisiones', [
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'user_id',
            ],
            'valor' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
                'after' => 'tipo',
            ],
        ]);

        $this->forge->addColumn('gastos', [
            'nota' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'categoria_id',
            ],
            'recibo_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'nota',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('gasto_divisiones', 'tipo');
        $this->forge->dropColumn('gasto_divisiones', 'valor');
        $this->forge->dropColumn('gastos', 'nota');
        $this->forge->dropColumn('gastos', 'recibo_path');
    }
}
