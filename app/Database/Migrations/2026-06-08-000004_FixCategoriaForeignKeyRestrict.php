<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixCategoriaForeignKeyRestrict extends Migration
{
    public function up()
    {
        $fkExists = $this->db->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gastos' AND CONSTRAINT_NAME = 'gastos_categoria_id_foreign'")->getResultArray();
        if (!empty($fkExists)) {
            $this->db->query('ALTER TABLE gastos DROP FOREIGN KEY gastos_categoria_id_foreign');
        }
        $this->db->query('ALTER TABLE gastos ADD CONSTRAINT gastos_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT ON UPDATE CASCADE');
    }

    public function down()
    {
        $fkExists = $this->db->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gastos' AND CONSTRAINT_NAME = 'gastos_categoria_id_foreign'")->getResultArray();
        if (!empty($fkExists)) {
            $this->db->query('ALTER TABLE gastos DROP FOREIGN KEY gastos_categoria_id_foreign');
        }
        $this->db->query('ALTER TABLE gastos ADD CONSTRAINT gastos_categoria_id_foreign FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE ON UPDATE CASCADE');
    }
}
