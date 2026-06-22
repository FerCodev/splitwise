<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUiComponentCatalogDecisionsTable extends Migration
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
            'catalog_key' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'section_key' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'group_key' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'default' => '',
            ],
            'item_key' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
            ],
            'item_name' => [
                'type' => 'VARCHAR',
                'constraint' => 160,
            ],
            'item_hint' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'source_label' => [
                'type' => 'VARCHAR',
                'constraint' => 120,
                'null' => true,
            ],
            'decision' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
            ],
            'redesign_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        $this->forge->addUniqueKey(['catalog_key', 'section_key', 'group_key', 'item_key'], 'ui_component_catalog_decision_unique');
        $this->forge->createTable('ui_component_catalog_decisions');
    }

    public function down()
    {
        $this->forge->dropTable('ui_component_catalog_decisions');
    }
}
