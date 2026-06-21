<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUiComponentPreferencesTable extends Migration
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
            'screen_key' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'component_key' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
            ],
            'variant_key' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
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
        $this->forge->addUniqueKey(['screen_key', 'component_key'], 'ui_component_unique_preference');
        $this->forge->createTable('ui_component_preferences');
    }

    public function down()
    {
        $this->forge->dropTable('ui_component_preferences');
    }
}
