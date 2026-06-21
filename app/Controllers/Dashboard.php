<?php

namespace App\Controllers;

use App\Models\Grupo;
use App\Models\Gasto;
use App\Models\Pago;

class Dashboard extends BaseController
{
    public function index()
    {
        $userId = (int) session()->get('userId');
        $grupoModel = new Grupo();
        $gastoModel = new Gasto();
        $pagoModel = new Pago();

        $grupos = $grupoModel->getGruposByUser($userId);
        $actividades = $grupoModel->getUltimaActividadByUser($userId);
        $ultimosMovs = $grupoModel->getUltimoMovimientoByUser($userId);

        $deudasPendientes = [];
        $totalDebe = 0;
        $totalLeDeben = 0;

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

            // Acumular deudas pendientes del usuario desde el mismo calculo
            foreach ($deudas as $d) {
                if ((int) $d['deudor_id'] === $userId || (int) $d['acreedor_id'] === $userId) {
                    $d['grupo_id'] = $gid;
                    $d['grupo_nombre'] = $grupo['nombre'];
                    $deudasPendientes[] = $d;
                    if ((int) $d['deudor_id'] === $userId) {
                        $totalDebe += $d['monto'];
                    }
                    if ((int) $d['acreedor_id'] === $userId) {
                        $totalLeDeben += $d['monto'];
                    }
                }
            }
        }
        unset($grupo);

        $grupos = Grupo::sortGroupsByActivity($grupos);

        $resumen = Grupo::computeDashboardResumen($grupos);

        $totalDeudasPendientes = count($deudasPendientes);
        usort($deudasPendientes, fn($a, $b) => $b['monto'] <=> $a['monto']);
        $deudasPendientes = array_slice($deudasPendientes, 0, 6);
        $hayMasDeudas = $totalDeudasPendientes > 6;

        return view('dashboard', array_merge(
            [
                'grupos' => $grupos,
                'deudasPendientes' => $deudasPendientes,
                'hayMasDeudas' => $hayMasDeudas,
                'totalDebe' => round($totalDebe, 2),
                'totalLeDeben' => round($totalLeDeben, 2),
            ],
            $resumen
        ));
    }
}
