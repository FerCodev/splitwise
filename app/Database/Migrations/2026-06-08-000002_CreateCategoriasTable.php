<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCategoriasTable extends Migration
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
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'unique' => true,
            ],
            'activa' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
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
        $this->forge->createTable('categorias');

        $categoriasIniciales = [
            'Supermercado',
            'Servicios',
            'Combustible',
            'Farmacia',
            'Mascotas',
            'Transporte',
            'Comida',
            'Viajes',
            'Otros',
        ];

        $this->db->table('categorias')->insertBatch(
            array_map(fn($n) => ['nombre' => $n, 'activa' => 1, 'created_at' => date('Y-m-d H:i:s')], $categoriasIniciales)
        );

        $this->forge->addColumn('gastos', [
            'categoria_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
                'default' => 0,
                'after' => 'monto',
            ],
        ]);

        $this->db->query('UPDATE gastos g JOIN categorias c ON g.categoria = c.nombre SET g.categoria_id = c.id');

        $otrosId = $this->db->table('categorias')->select('id')->where('nombre', 'Otros')->get()->getRow()->id ?? 0;
        if ($otrosId > 0) {
            $this->db->query("UPDATE gastos SET categoria_id = ? WHERE categoria_id = 0", [$otrosId]);
        }

        $this->forge->addForeignKey('categoria_id', 'categorias', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropForeignKey('gastos', 'gastos_categoria_id_foreign');
        $this->forge->dropColumn('gastos', 'categoria_id');
        $this->forge->dropTable('categorias');
    }
}
