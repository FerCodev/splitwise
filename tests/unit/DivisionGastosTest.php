<?php

use App\Models\Gasto;

/**
 * @internal
 */
final class DivisionGastosTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testIgualitarioDosPersonas(): void
    {
        $result = Gasto::calcularMontosDivision('igualitario', 100.00, [1, 2]);
        $this->assertCount(2, $result);
        $this->assertSame(50.0, $result[1]);
        $this->assertSame(50.0, $result[2]);
        $this->assertSame(100.0, array_sum($result));
    }

    public function testIgualitarioTresPersonasConRedondeo(): void
    {
        $result = Gasto::calcularMontosDivision('igualitario', 100.00, [1, 2, 3]);
        $this->assertCount(3, $result);
        $this->assertSame(33.33, $result[1]);
        $this->assertSame(33.33, $result[2]);
        $this->assertSame(33.34, $result[3]);
        $this->assertSame(100.0, array_sum($result));
    }

    public function testIgualitarioSumaPreservada(): void
    {
        $montos = [0.01, 9.99, 1234.56, 0.50, 9999.99];
        foreach ($montos as $monto) {
            for ($n = 2; $n <= 8; $n++) {
                $ids = range(1, $n);
                $result = Gasto::calcularMontosDivision('igualitario', $monto, $ids);
                $suma = round(array_sum($result), 2);
                $this->assertSame(round($monto, 2), $suma, "monto=$monto participantes=$n suma=$suma");
            }
        }
    }

    public function testIgualitarioUsuarioUnico(): void
    {
        $result = Gasto::calcularMontosDivision('igualitario', 50.00, [1]);
        $this->assertCount(1, $result);
        $this->assertSame(50.0, $result[1]);
    }

    public function testMontoFijoDosPersonas(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '30.00'],
            ['user_id' => 2, 'valor' => '70.00'],
        ];
        $result = Gasto::calcularMontosDivision('monto_fijo', 100.00, [1, 2], $valores);
        $this->assertSame(30.0, $result[1]);
        $this->assertSame(70.0, $result[2]);
        $this->assertSame(100.0, array_sum($result));
    }

    public function testMontoFijoTresPersonas(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '10.50'],
            ['user_id' => 2, 'valor' => '20.25'],
            ['user_id' => 3, 'valor' => '30.75'],
        ];
        $result = Gasto::calcularMontosDivision('monto_fijo', 61.50, [1, 2, 3], $valores);
        $this->assertSame(10.50, $result[1]);
        $this->assertSame(20.25, $result[2]);
        $this->assertSame(30.75, $result[3]);
        $this->assertSame(61.50, array_sum($result));
    }

    public function testPorcentajeDosPersonas(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '60'],
            ['user_id' => 2, 'valor' => '40'],
        ];
        $result = Gasto::calcularMontosDivision('porcentaje', 200.00, [1, 2], $valores);
        $this->assertSame(120.0, $result[1]);
        $this->assertSame(80.0, $result[2]);
        $this->assertSame(200.0, array_sum($result));
    }

    public function testPorcentajeTresPersonasConRedondeo(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '33.33'],
            ['user_id' => 2, 'valor' => '33.33'],
            ['user_id' => 3, 'valor' => '33.34'],
        ];
        $result = Gasto::calcularMontosDivision('porcentaje', 100.00, [1, 2, 3], $valores);
        $suma = round(array_sum($result), 2);
        $this->assertSame(100.0, $suma);
    }

    public function testPorcentajeSumaPreservada(): void
    {
        $scenarios = [
            ['monto' => 100.00, 'pcts' => [50, 50]],
            ['monto' => 300.00, 'pcts' => [25, 25, 25, 25]],
            ['monto' => 99.99, 'pcts' => [30, 30, 40]],
            ['monto' => 0.03, 'pcts' => [50, 50]],
        ];
        foreach ($scenarios as $s) {
            $valores = [];
            $ids = [];
            foreach ($s['pcts'] as $i => $pct) {
                $uid = $i + 1;
                $ids[] = $uid;
                $valores[] = ['user_id' => $uid, 'valor' => (string) $pct];
            }
            $result = Gasto::calcularMontosDivision('porcentaje', $s['monto'], $ids, $valores);
            $suma = round(array_sum($result), 2);
            $this->assertSame(round($s['monto'], 2), $suma, "monto={$s['monto']} pcts=" . json_encode($s['pcts']) . " suma=$suma");
        }
    }

    public function testPorcentajeEnteros(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '20'],
            ['user_id' => 2, 'valor' => '30'],
            ['user_id' => 3, 'valor' => '50'],
        ];
        $result = Gasto::calcularMontosDivision('porcentaje', 1000.00, [1, 2, 3], $valores);
        $this->assertSame(200.0, $result[1]);
        $this->assertSame(300.0, $result[2]);
        $this->assertSame(500.0, $result[3]);
        $this->assertSame(1000.0, array_sum($result));
    }
}
