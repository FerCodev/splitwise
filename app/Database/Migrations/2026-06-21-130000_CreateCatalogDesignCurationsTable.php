<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCatalogDesignCurationsTable extends Migration
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
            'design_id' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
            ],
            'design_name' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
            ],
            'design_group' => [
                'type' => 'VARCHAR',
                'constraint' => 180,
            ],
            'status' => [
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => true,
            ],
            'redesign_note' => [
                'type' => 'TEXT',
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
        $this->forge->addUniqueKey('design_id', 'catalog_design_curations_unique_design');
        $this->forge->createTable('catalog_design_curations');
    }

    public function down()
    {
        $this->forge->dropTable('catalog_design_curations');
    }
}
