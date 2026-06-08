<?php

use App\Models\Gasto;

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
        $miembros = [
            ['user_id' => 1, 'name' => 'Fernando'],
            ['user_id' => 2, 'name' => 'Pareja'],
        ];
        $pagadoGastos = [1 => 600];
        $consumido = [1 => 300, 2 => 300];

        $balance = Gasto::computeBalance($miembros, $pagadoGastos, $consumido, [], []);

        $this->assertCount(2, $balance);
        $this->assertSame(300.0, $balance[0]['saldo']);
        $this->assertSame('Fernando', $balance[0]['name']);
        $this->assertSame(-300.0, $balance[1]['saldo']);
        $this->assertSame('Pareja', $balance[1]['name']);

        $deudas = $this->computeDeudas($balance);
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
        $miembros = [
            ['user_id' => 1, 'name' => 'Fernando'],
            ['user_id' => 2, 'name' => 'Pareja'],
        ];
        $pagadoGastos = [1 => 600];
        $consumido = [1 => 300, 2 => 300];
        $pagosEnviados = [2 => 100];
        $pagosRecibidos = [1 => 100];

        $balance = Gasto::computeBalance($miembros, $pagadoGastos, $consumido, $pagosEnviados, $pagosRecibidos);

        $this->assertCount(2, $balance);
        $this->assertSame(200.0, $balance[0]['saldo']);
        $this->assertSame('Fernando', $balance[0]['name']);
        $this->assertSame(-200.0, $balance[1]['saldo']);
        $this->assertSame('Pareja', $balance[1]['name']);

        $deudas = $this->computeDeudas($balance);
        $this->assertCount(1, $deudas);
        $this->assertSame(200.0, $deudas[0]['monto']);
    }

    /**
     * Caso 3: Mismo caso anterior + Pareja paga $300 restantes a Fernando.
     * Ambos saldo 0. Sin transferencias.
     */
    public function testBalanceConPagoTotal(): void
    {
        $miembros = [
            ['user_id' => 1, 'name' => 'Fernando'],
            ['user_id' => 2, 'name' => 'Pareja'],
        ];
        $pagadoGastos = [1 => 600];
        $consumido = [1 => 300, 2 => 300];
        $pagosEnviados = [2 => 300];
        $pagosRecibidos = [1 => 300];

        $balance = Gasto::computeBalance($miembros, $pagadoGastos, $consumido, $pagosEnviados, $pagosRecibidos);

        $this->assertCount(2, $balance);
        $this->assertSame(0.0, $balance[0]['saldo']);
        $this->assertSame(0.0, $balance[1]['saldo']);

        $deudas = $this->computeDeudas($balance);
        $this->assertCount(0, $deudas);
    }

    /**
     * Caso extra: grupo saldado desde el inicio (sin gastos)
     */
    public function testGrupoSinMovimientos(): void
    {
        $miembros = [
            ['user_id' => 1, 'name' => 'A'],
            ['user_id' => 2, 'name' => 'B'],
        ];

        $balance = Gasto::computeBalance($miembros, [], [], [], []);

        $this->assertCount(2, $balance);
        $this->assertSame(0.0, $balance[0]['saldo']);
        $this->assertSame(0.0, $balance[1]['saldo']);

        $deudas = $this->computeDeudas($balance);
        $this->assertCount(0, $deudas);
    }

    private function computeDeudas(array $saldos): array
    {
        $acreedores = array_filter($saldos, fn($s) => $s['saldo'] > 0);
        $deudores = array_filter($saldos, fn($s) => $s['saldo'] < 0);

        $deudas = [];
        foreach ($deudores as $d) {
            $restante = abs($d['saldo']);
            foreach ($acreedores as &$a) {
                if ($restante <= 0) break;
                if ($a['saldo'] <= 0) continue;

                $monto = min($restante, $a['saldo']);
                $deudas[] = [
                    'deudor' => $d['name'],
                    'deudor_id' => $d['user_id'],
                    'acreedor' => $a['name'],
                    'acreedor_id' => $a['user_id'],
                    'monto' => round($monto, 2),
                ];
                $restante -= $monto;
                $a['saldo'] -= $monto;
            }
        }

        return $deudas;
    }
}
