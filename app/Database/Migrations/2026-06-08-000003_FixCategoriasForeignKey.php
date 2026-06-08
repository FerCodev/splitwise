<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixCategoriasForeignKey extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE gastos ALTER COLUMN categoria_id DROP DEFAULT');
        $this->db->query('ALTER TABLE gastos MODIFY COLUMN categoria_id INT UNSIGNED NOT NULL');

        $foreignKeys = $this->db->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gastos' AND COLUMN_NAME = 'categoria_id' AND REFERENCED_TABLE_NAME IS NOT NULL")->getResultArray();
        if (empty($foreignKeys)) {
            $this->db->query('ALTER TABLE gastos ADD CONSTRAINT gastos_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        $this->forge->dropColumn('gastos', 'categoria');
    }

    public function down()
    {
        $fkExists = $this->db->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gastos' AND CONSTRAINT_NAME = 'gastos_categoria_id_foreign'")->getResultArray();
        if (!empty($fkExists)) {
            $this->forge->dropForeignKey('gastos', 'gastos_categoria_id_foreign');
        }
        $this->forge->addColumn('gastos', [
            'categoria' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'default' => 'Otros',
                'after' => 'monto',
            ],
        ]);
    }
}
