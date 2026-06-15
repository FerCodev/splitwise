<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MigrateGastosExistentesADivisiones extends Migration
{
    public function up()
    {
        $gastos = $this->db->table('gastos')
            ->select('gastos.id, gastos.monto')
            ->where('gastos.division_tipo', 'igualitario')
            ->whereNotIn('gastos.id', function ($qb) {
                $qb->select('gasto_id')->from('gasto_divisiones');
            })
            ->limit(500)
            ->get()
            ->getResultArray();

        foreach ($gastos as $g) {
            $participantes = $this->db->table('gasto_participantes')
                ->select('user_id')
                ->where('gasto_id', $g['id'])
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($participantes)) continue;

            $ids = array_column($participantes, 'user_id');
            $cantidad = count($ids);
            $monto = (float) $g['monto'];
            $porcion = round($monto / $cantidad, 2);
            $diferencias = round($monto - ($porcion * $cantidad), 2);

            foreach ($ids as $i => $userId) {
                $asignado = $porcion;
                if ($i === array_key_last($ids)) {
                    $asignado += $diferencias;
                }
                $this->db->table('gasto_divisiones')->insert([
                    'gasto_id' => $g['id'],
                    'user_id' => $userId,
                    'monto_calculado' => round($asignado, 2),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $this->db->table('gasto_divisiones')->truncate();
    }
}
