<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNombreToUserPaymentMethods extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user_payment_methods', [
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'after' => 'tipo',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('user_payment_methods', 'nombre');
    }
}
