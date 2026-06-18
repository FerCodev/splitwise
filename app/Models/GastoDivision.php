<?php

namespace App\Models;

use CodeIgniter\Model;

class GastoDivision extends Model
{
    protected $table = 'gasto_divisiones';
    protected $primaryKey = 'id';
    protected $allowedFields = ['gasto_id', 'user_id', 'tipo', 'valor', 'monto_calculado'];
    protected $useTimestamps = true;

    /**
     * UX4 solo expone igualitario, monto_fijo y porcentaje.
     * partes y ajuste quedan como soporte tecnico no expuesto/heredado.
     * No eliminar en Fase B (pendiente evaluacion en Fase C).
     */
    public static function generarDivisionesIgualitarias(int $gastoId, float $monto, array $participantesIds, string $tipo = 'igualitario', array $valores = []): bool
    {
        $model = new self();
        $divisiones = [];
        $cantidad = count($participantesIds);
        if ($cantidad === 0) return true;

        // Primero eliminar divisiones existentes para este gasto
        $model->where('gasto_id', $gastoId)->delete();

        if ($tipo === 'monto_fijo' && !empty($valores)) {
            $totalAsignado = array_sum($valores);
            foreach ($participantesIds as $userId) {
                $v = $valores[$userId] ?? 0;
                $divisiones[] = [
                    'gasto_id' => $gastoId,
                    'user_id' => (int) $userId,
                    'tipo' => 'monto_fijo',
                    'valor' => $v,
                    'monto_calculado' => round($v, 2),
                ];
            }
        } elseif ($tipo === 'porcentaje' && !empty($valores)) {
            foreach ($participantesIds as $userId) {
                $pct = $valores[$userId] ?? 0;
                $calc = round($monto * $pct / 100, 2);
                $divisiones[] = [
                    'gasto_id' => $gastoId,
                    'user_id' => (int) $userId,
                    'tipo' => 'porcentaje',
                    'valor' => $pct,
                    'monto_calculado' => $calc,
                ];
            }
            // Ajustar redondeo
            $totalCalc = array_sum(array_column($divisiones, 'monto_calculado'));
            $diff = round($monto - $totalCalc, 2);
            if (abs($diff) > 0.001 && !empty($divisiones)) {
                $divisiones[array_key_last($divisiones)]['monto_calculado'] = round($divisiones[array_key_last($divisiones)]['monto_calculado'] + $diff, 2);
            }
        } elseif ($tipo === 'partes' && !empty($valores)) {
            $totalPartes = array_sum($valores);
            if ($totalPartes > 0) {
                foreach ($participantesIds as $userId) {
                    $partes = $valores[$userId] ?? 1;
                    $calc = round($monto * $partes / $totalPartes, 2);
                    $divisiones[] = [
                        'gasto_id' => $gastoId,
                        'user_id' => (int) $userId,
                        'tipo' => 'partes',
                        'valor' => $partes,
                        'monto_calculado' => $calc,
                    ];
                }
                $totalCalc = array_sum(array_column($divisiones, 'monto_calculado'));
                $diff = round($monto - $totalCalc, 2);
                if (abs($diff) > 0.001 && !empty($divisiones)) {
                    $divisiones[array_key_last($divisiones)]['monto_calculado'] = round($divisiones[array_key_last($divisiones)]['monto_calculado'] + $diff, 2);
                }
            }
        } elseif ($tipo === 'ajuste' && !empty($valores)) {
            // Base igualitaria + ajustes individuales
            $porcion = round($monto / $cantidad, 2);
            $diferencias = round($monto - ($porcion * $cantidad), 2);
            foreach ($participantesIds as $i => $userId) {
                $asignado = $porcion;
                if ($i === array_key_last($participantesIds)) {
                    $asignado += $diferencias;
                }
                $ajuste = $valores[$userId] ?? 0;
                $calc = round($asignado + $ajuste, 2);
                $divisiones[] = [
                    'gasto_id' => $gastoId,
                    'user_id' => (int) $userId,
                    'tipo' => 'ajuste',
                    'valor' => $ajuste,
                    'monto_calculado' => $calc,
                ];
            }
        } else {
            // Igualitario por defecto
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
                    'tipo' => $tipo,
                    'valor' => null,
                    'monto_calculado' => round($asignado, 2),
                ];
            }
        }

        if (!empty($divisiones)) {
            return $model->insertBatch($divisiones) !== false;
        }
        return true;
    }

}
