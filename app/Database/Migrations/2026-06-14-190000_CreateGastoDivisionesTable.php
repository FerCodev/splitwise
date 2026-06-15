<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGastoDivisionesTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('gastos', [
            'division_tipo' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => 'igualitario',
                'after' => 'categoria_id',
            ],
        ]);

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'gasto_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'monto_calculado' => [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
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
        $this->forge->addForeignKey('gasto_id', 'gastos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('gasto_divisiones');
    }

    public function down()
    {
        $this->forge->dropTable('gasto_divisiones');
        $this->forge->dropColumn('gastos', 'division_tipo');
    }
}
