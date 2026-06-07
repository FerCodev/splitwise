<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePagosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'grupo_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'pagador_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'receptor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'monto' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'fecha' => [
                'type' => 'DATE',
            ],
            'descripcion' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
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
        $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('pagador_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('receptor_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pagos');
    }

    public function down()
    {
        $this->forge->dropTable('pagos');
    }
}
