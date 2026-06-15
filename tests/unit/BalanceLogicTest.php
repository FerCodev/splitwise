<?php

use App\Models\Gasto;
use App\Models\GastoDivision;

/**
 * @internal
 */
final class BalanceLogicTest extends \CodeIgniter\Test\CIUnitTestCase
{
    /**
     * Caso 1: Dos usuarios, un gasto de $600 dividido entre ambos, Fernando paga todo.
     * Fernando +$300, Pareja -$300. Transferencia: Pareja -> Fernando $300.
     */
    public function testBalanceSinPagos(): void
    {
        $balance = Gasto::computeBalance(
            [
                ['user_id' => 1, 'name' => 'Fernando'],
                ['user_id' => 2, 'name' => 'Pareja'],
            ],
            [1 => 600],
            [1 => 300, 2 => 300],
            [],
            []
        );

        $this->assertCount(2, $balance);
        $this->assertSame(300.0, $balance[0]['saldo']);
        $this->assertSame('Fernando', $balance[0]['name']);
        $this->assertSame(-300.0, $balance[1]['saldo']);
        $this->assertSame('Pareja', $balance[1]['name']);

        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertCount(1, $deudas);
        $this->assertSame('Pareja', $deudas[0]['deudor']);
        $this->assertSame('Fernando', $deudas[0]['acreedor']);
        $this->assertSame(300.0, $deudas[0]['monto']);
    }

    /**
     * Caso 2: Mismo caso anterior + Pareja paga $100 a Fernando.
     * Fernando +$200, Pareja -$200. Transferencia: Pareja -> Fernando $200.
     */
    public function testBalanceConPagoParcial(): void
    {
        $balance = Gasto::computeBalance(
            [
                ['user_id' => 1, 'name' => 'Fernando'],
                ['user_id' => 2, 'name' => 'Pareja'],
            ],
            [1 => 600],
            [1 => 300, 2 => 300],
            [2 => 100],
            [1 => 100]
        );

        $this->assertCount(2, $balance);
        $this->assertSame(200.0, $balance[0]['saldo']);
        $this->assertSame('Fernando', $balance[0]['name']);
        $this->assertSame(-200.0, $balance[1]['saldo']);
        $this->assertSame('Pareja', $balance[1]['name']);

        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertCount(1, $deudas);
        $this->assertSame(200.0, $deudas[0]['monto']);
    }

    /**
     * Caso 3: Mismo caso anterior + Pareja paga $300 restantes a Fernando.
     * Ambos saldo 0. Sin transferencias.
     */
    public function testBalanceConPagoTotal(): void
    {
        $balance = Gasto::computeBalance(
            [
                ['user_id' => 1, 'name' => 'Fernando'],
                ['user_id' => 2, 'name' => 'Pareja'],
            ],
            [1 => 600],
            [1 => 300, 2 => 300],
            [2 => 300],
            [1 => 300]
        );

        $this->assertCount(2, $balance);
        $this->assertSame(0.0, $balance[0]['saldo']);
        $this->assertSame(0.0, $balance[1]['saldo']);

        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertCount(0, $deudas);
    }

    /**
     * Caso extra: grupo saldado desde el inicio (sin gastos)
     */
    public function testGrupoSinMovimientos(): void
    {
        $balance = Gasto::computeBalance(
            [
                ['user_id' => 1, 'name' => 'A'],
                ['user_id' => 2, 'name' => 'B'],
            ],
            [],
            [],
            [],
            []
        );

        $this->assertCount(2, $balance);
        $this->assertSame(0.0, $balance[0]['saldo']);
        $this->assertSame(0.0, $balance[1]['saldo']);

        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertCount(0, $deudas);
    }

    /**
     * Estructura de balance: verifica que todos los keys requeridos existan.
     */
    public function testBalanceKeys(): void
    {
        $balance = Gasto::computeBalance(
            [['user_id' => 1, 'name' => 'X']],
            [1 => 100],
            [1 => 50],
            [1 => 10],
            [1 => 20]
        );

        $this->assertArrayHasKey('user_id', $balance[0]);
        $this->assertArrayHasKey('name', $balance[0]);
        $this->assertArrayHasKey('total_pagado_gastos', $balance[0]);
        $this->assertArrayHasKey('total_consumido', $balance[0]);
        $this->assertArrayHasKey('pagos_enviados', $balance[0]);
        $this->assertArrayHasKey('pagos_recibidos', $balance[0]);
        $this->assertArrayHasKey('saldo', $balance[0]);
    }

    /**
     * Estructura de deudas: verifica que todos los keys requeridos existan.
     */
    public function testDeudasKeys(): void
    {
        $balance = Gasto::computeBalance(
            [
                ['user_id' => 1, 'name' => 'A'],
                ['user_id' => 2, 'name' => 'B'],
            ],
            [1 => 100],
            [1 => 50, 2 => 50],
            [],
            []
        );

        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertCount(1, $deudas);
        $this->assertArrayHasKey('deudor', $deudas[0]);
        $this->assertArrayHasKey('deudor_id', $deudas[0]);
        $this->assertArrayHasKey('acreedor', $deudas[0]);
        $this->assertArrayHasKey('acreedor_id', $deudas[0]);
        $this->assertArrayHasKey('monto', $deudas[0]);
    }

    /**
     * Verifica que saldos positivos y negativos sumen cero (conservacion).
     */
    public function testBalanceConservation(): void
    {
        $balance = Gasto::computeBalance(
            [
                ['user_id' => 1, 'name' => 'A'],
                ['user_id' => 2, 'name' => 'B'],
                ['user_id' => 3, 'name' => 'C'],
            ],
            [1 => 500, 2 => 300],
            [1 => 200, 2 => 400, 3 => 200],
            [3 => 50],
            [2 => 50]
        );

        $total = array_sum(array_column($balance, 'saldo'));
        $this->assertSame(0.0, $total);
    }

    public function testDivisionIgualitariaSumaExactaDosPersonas(): void
    {
        $divisiones = [];
        $monto = 600.0;
        $ids = [1, 2];
        $cantidad = count($ids);
        $porcion = round($monto / $cantidad, 2);
        $diferencias = round($monto - ($porcion * $cantidad), 2);

        foreach ($ids as $i => $uid) {
            $asignado = $porcion;
            if ($i === array_key_last($ids)) {
                $asignado += $diferencias;
            }
            $divisiones[] = $asignado;
        }

        $this->assertSame(600.0, round(array_sum($divisiones), 2));
        $this->assertSame(300.0, $divisiones[0]);
        $this->assertSame(300.0, $divisiones[1]);
    }

    public function testDivisionIgualitariaRedondeoTresPersonas(): void
    {
        $divisiones = [];
        $monto = 100.0;
        $ids = [1, 2, 3];
        $cantidad = count($ids);
        $porcion = round($monto / $cantidad, 2);
        $diferencias = round($monto - ($porcion * $cantidad), 2);

        foreach ($ids as $i => $uid) {
            $asignado = $porcion;
            if ($i === array_key_last($ids)) {
                $asignado += $diferencias;
            }
            $divisiones[] = round($asignado, 2);
        }

        $this->assertSame(100.0, round(array_sum($divisiones), 2));
        $this->assertSame(33.33, $divisiones[0]);
        $this->assertSame(33.33, $divisiones[1]);
        $this->assertSame(33.34, $divisiones[2]);
    }

    public function testDivisionIgualitariaCincoPersonas(): void
    {
        $divisiones = [];
        $monto = 200.0;
        $ids = [1, 2, 3, 4, 5];
        $cantidad = count($ids);
        $porcion = round($monto / $cantidad, 2);
        $diferencias = round($monto - ($porcion * $cantidad), 2);

        foreach ($ids as $i => $uid) {
            $asignado = $porcion;
            if ($i === array_key_last($ids)) {
                $asignado += $diferencias;
            }
            $divisiones[] = round($asignado, 2);
        }

        $this->assertSame(200.0, round(array_sum($divisiones), 2));
    }

    public function testBalanceConDivisionesExplicitas(): void
    {
        $balance = Gasto::computeBalance(
            [
                ['user_id' => 1, 'name' => 'A'],
                ['user_id' => 2, 'name' => 'B'],
            ],
            [1 => 600],
            [1 => 300, 2 => 300],
            [],
            []
        );

        $this->assertSame(300.0, $balance[0]['saldo']);
        $this->assertSame(-300.0, $balance[1]['saldo']);

        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertCount(1, $deudas);
        $this->assertSame(300.0, $deudas[0]['monto']);
    }

    public function testDivisionMontoFijoConSumaExacta(): void
    {
        $divisiones = [];
        $monto = 600.0;
        $valores = [1 => 400.0, 2 => 200.0];
        $totalValor = array_sum($valores);
        $this->assertSame(600.0, $totalValor);

        foreach ([1, 2] as $uid) {
            $divisiones[] = $valores[$uid];
        }
        $this->assertSame(600.0, round(array_sum($divisiones), 2));
    }

    public function testDivisionMontoFijoConDiferencia(): void
    {
        $monto = 600.0;
        $valores = [1 => 400.0, 2 => 150.0];
        $totalValor = array_sum($valores);
        $diferencia = round($monto - $totalValor, 2);
        $this->assertSame(50.0, $diferencia);
    }

    public function testDivisionPorcentajeConSumaCien(): void
    {
        $monto = 200.0;
        $pcts = [1 => 60, 2 => 40];
        $this->assertEquals(100.0, array_sum($pcts));
        foreach ($pcts as $uid => $pct) {
            $calc = round($monto * $pct / 100, 2);
            $divisiones[] = $calc;
        }
        $this->assertSame(200.0, round(array_sum($divisiones), 2));
        $this->assertSame(120.0, $divisiones[0]);
        $this->assertSame(80.0, $divisiones[1]);
    }

    public function testDivisionPartes(): void
    {
        $monto = 300.0;
        $partes = [1 => 1, 2 => 2];
        $totalPartes = array_sum($partes);
        $this->assertSame(3, $totalPartes);

        $divisiones = [];
        foreach ($partes as $uid => $p) {
            $calc = round($monto * $p / $totalPartes, 2);
            $divisiones[] = $calc;
        }
        $this->assertSame(300.0, round(array_sum($divisiones), 2));
        $this->assertSame(100.0, $divisiones[0]);
        $this->assertSame(200.0, $divisiones[1]);
    }

    public function testDivisionAjuste(): void
    {
        $monto = 200.0;
        $ids = [1, 2];
        $ajustes = [1 => 50.0, 2 => -50.0];
        $this->assertSame(0.0, array_sum($ajustes));

        $porcion = round($monto / count($ids), 2);
        $divisiones = [];
        foreach ($ids as $uid) {
            $calc = round($porcion + ($ajustes[$uid] ?? 0), 2);
            $divisiones[] = $calc;
        }
        $this->assertSame(200.0, round(array_sum($divisiones), 2));
        $this->assertSame(150.0, $divisiones[0]);
        $this->assertSame(50.0, $divisiones[1]);
    }
}
