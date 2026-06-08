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

            $grupo['mi_saldo'] = $miSaldo;
            $grupo['total_gastado'] = $grupoModel->getTotalGastado($grupo['id']);
            $grupo['total_pagado'] = $pagoModel->getTotalPagadoByGrupo($grupo['id']);
            $grupo['deudas_count'] = count($deudas);
        }
        unset($grupo);

        $resumen = Grupo::computeDashboardResumen($grupos);

        $ultimosGastos = $gastoModel->getUltimosGastosByUser($userId, 5);
        $ultimosPagos = $pagoModel->getUltimosPagosByUser($userId, 5);

        return view('dashboard', array_merge(
            ['grupos' => $grupos, 'ultimosGastos' => $ultimosGastos, 'ultimosPagos' => $ultimosPagos],
            $resumen
        ));
    }
}
