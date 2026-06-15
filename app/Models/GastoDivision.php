<?php

namespace App\Models;

use CodeIgniter\Model;

class GastoDivision extends Model
{
    protected $table = 'gasto_divisiones';
    protected $primaryKey = 'id';
    protected $allowedFields = ['gasto_id', 'user_id', 'monto_calculado'];
    protected $useTimestamps = true;

    public function gasto()
    {
        return $this->belongsTo(Gasto::class, 'gasto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function generarDivisionesIgualitarias(int $gastoId, float $monto, array $participantesIds): void
    {
        $model = new self();
        $divisiones = [];
        $cantidad = count($participantesIds);
        if ($cantidad === 0) return;

        $porcion = round($monto / $cantidad, 2);
        $diferencias = round($monto - ($porcion * $cantidad), 2);

        foreach ($participantesIds as $i => $userId) {
            $asignado = $porcion;
            if ($i === array_key_last($participantesIds)) {
                $asignado += $diferencias;
            }
            $divisiones[] = [
                'gasto_id' => $gastoId,
                'user_id' => (int) $userId,
                'monto_calculado' => round($asignado, 2),
            ];
        }

        if (!empty($divisiones)) {
            $model->insertBatch($divisiones);
        }
    }

    public static function getGastosSinDivisiones(): array
    {
        $db = db_connect();
        return $db->table('gastos')
            ->select('gastos.id')
            ->where('gastos.division_tipo', 'igualitario')
            ->whereNotIn('gastos.id', function ($qb) {
                $qb->select('gasto_id')->from('gasto_divisiones');
            })
            ->get()
            ->getResultArray();
    }
}
