<?php

namespace App\Controllers;

use App\Models\Grupo;
use App\Models\Gasto;
use App\Models\Pago;

class Dashboard extends BaseController
{
    public function index()
    {
        $userId = session()->get('userId');
        $grupoModel = new Grupo();
        $gastoModel = new Gasto();
        $pagoModel = new Pago();

        $grupos = $grupoModel->getGruposByUser($userId);
        $actividades = $grupoModel->getUltimaActividadByUser($userId);
        $ultimosMovs = $grupoModel->getUltimoMovimientoByUser($userId);

        foreach ($grupos as &$grupo) {
            $balance = $gastoModel->getBalanceByGrupo($grupo['id']);
            $deudas = Gasto::computeDeudasFromBalance($balance);

            $miSaldo = 0.0;
            foreach ($balance as $b) {
                if ($b['user_id'] == $userId) {
                    $miSaldo = $b['saldo'];
                    break;
                }
            }

            $gid = (int) $grupo['id'];
            $grupo['mi_saldo'] = $miSaldo;
            $grupo['total_gastado'] = $grupoModel->getTotalGastado($grupo['id']);
            $grupo['total_pagado'] = $pagoModel->getTotalPagadoByGrupo($grupo['id']);
            $grupo['deudas_count'] = count($deudas);
            $grupo['ultima_actividad'] = $actividades[$gid] ?? $grupo['created_at'];
            $grupo['ultimo_movimiento'] = $ultimosMovs[$gid] ?? null;
        }
        unset($grupo);

        $grupos = Grupo::sortGroupsByActivity($grupos);

        $resumen = Grupo::computeDashboardResumen($grupos);

        $ultimosGastos = $gastoModel->getUltimosGastosByUser($userId, 10);
        $ultimosPagos = $pagoModel->getUltimosPagosByUser($userId, 10);

        $movimientos = array_merge(
            array_map(fn($g) => array_merge($g, ['tipo' => 'gasto']), $ultimosGastos),
            array_map(fn($p) => array_merge($p, ['tipo' => 'pago']), $ultimosPagos)
        );
        usort($movimientos, fn($a, $b) => strcmp($b['fecha'] . 'z', $a['fecha'] . 'z'));
        $movimientos = array_slice($movimientos, 0, 20);

        // Deudas pendientes del usuario
        $deudasPendientes = [];
        foreach ($grupos as $g) {
            $balance = $gastoModel->getBalanceByGrupo($g['id']);
            $deudas = Gasto::computeDeudasFromBalance($balance);
            foreach ($deudas as $d) {
                if ((int) $d['deudor_id'] === $userId || (int) $d['acreedor_id'] === $userId) {
                    $d['grupo_id'] = $g['id'];
                    $d['grupo_nombre'] = $g['nombre'];
                    $deudasPendientes[] = $d;
                }
            }
        }
        usort($deudasPendientes, fn($a, $b) => $b['monto'] - $a['monto']);
        $deudasPendientes = array_slice($deudasPendientes, 0, 6);

        $totalDebe = 0;
        $totalLeDeben = 0;
        foreach ($deudasPendientes as $d) {
            if ((int) $d['deudor_id'] === $userId) {
                $totalDebe += $d['monto'];
            }
            if ((int) $d['acreedor_id'] === $userId) {
                $totalLeDeben += $d['monto'];
            }
        }

        return view('dashboard', array_merge(
            [
                'grupos' => $grupos,
                'movimientos' => $movimientos,
                'deudasPendientes' => $deudasPendientes,
                'totalDebe' => round($totalDebe, 2),
                'totalLeDeben' => round($totalLeDeben, 2),
            ],
            $resumen
        ));
    }
}
