<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddReciboMetadataToGastos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('gastos', [
            'recibo_nombre' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'recibo_path'],
            'recibo_mime'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true, 'after' => 'recibo_nombre'],
            'recibo_size'   => ['type' => 'INT', 'null' => true, 'after' => 'recibo_mime'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('gastos', 'recibo_nombre');
        $this->forge->dropColumn('gastos', 'recibo_mime');
        $this->forge->dropColumn('gastos', 'recibo_size');
    }
}
