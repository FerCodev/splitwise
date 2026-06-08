<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserPaymentMethodsTable extends Migration
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
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'tipo' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
            ],
            'alias' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'cbu_cvu' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'banco' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'titular' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'payment_link' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
                'null' => true,
            ],
            'activo' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
            ],
            'favorito' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
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
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_payment_methods');
    }

    public function down()
    {
        $this->forge->dropTable('user_payment_methods');
    }
}
