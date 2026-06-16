<?php

/**
 * @internal
 */
final class DivisionGastosTest extends \CodeIgniter\Test\CIUnitTestCase
{
    /**
     * Calcula la division exactamente como lo hace Gastos::create().
     */
    private function calcularDivision(string $tipo, float $monto, array $participantesIds, array $divisionValores = []): array
    {
        $participantesIds = array_unique(array_map('intval', $participantesIds));
        $participantesMonto = [];

        if ($tipo === 'monto_fijo' && !empty($divisionValores)) {
            $valoresMap = [];
            foreach ($divisionValores as $dv) {
                $valoresMap[(int) $dv['user_id']] = (float) $dv['valor'];
            }
            foreach ($participantesIds as $uid) {
                $participantesMonto[$uid] = round($valoresMap[$uid] ?? 0, 2);
            }
        } elseif ($tipo === 'porcentaje' && !empty($divisionValores)) {
            $valoresMap = [];
            foreach ($divisionValores as $dv) {
                $valoresMap[(int) $dv['user_id']] = (float) $dv['valor'];
            }
            $totalCalc = 0;
            foreach ($participantesIds as $i => $uid) {
                $calc = round($monto * ($valoresMap[$uid] ?? 0) / 100, 2);
                $participantesMonto[$uid] = $calc;
                $totalCalc += $calc;
            }
            $diff = round($monto - $totalCalc, 2);
            if (abs($diff) > 0.001 && !empty($participantesIds)) {
                $lastUid = end($participantesIds);
                $participantesMonto[$lastUid] = round($participantesMonto[$lastUid] + $diff, 2);
            }
        } else {
            $porcion = round($monto / count($participantesIds), 2);
            $diferencias = round($monto - ($porcion * count($participantesIds)), 2);
            foreach ($participantesIds as $i => $uid) {
                $asignado = $porcion;
                if ($i === array_key_last($participantesIds)) {
                    $asignado += $diferencias;
                }
                $participantesMonto[$uid] = round($asignado, 2);
            }
        }

        return $participantesMonto;
    }

    public function testIgualitarioDosPersonas(): void
    {
        $result = $this->calcularDivision('igualitario', 100.00, [1, 2]);
        $this->assertCount(2, $result);
        $this->assertSame(50.0, $result[1]);
        $this->assertSame(50.0, $result[2]);
        $this->assertSame(100.0, array_sum($result));
    }

    public function testIgualitarioTresPersonasConRedondeo(): void
    {
        $result = $this->calcularDivision('igualitario', 100.00, [1, 2, 3]);
        $this->assertCount(3, $result);
        // 100/3 = 33.33, so last gets 33.34
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
                $result = $this->calcularDivision('igualitario', $monto, $ids);
                $suma = round(array_sum($result), 2);
                $this->assertSame(round($monto, 2), $suma, "Fallo: monto=$monto participantes=$n suma=$suma");
            }
        }
    }

    public function testMontoFijoDosPersonas(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '30.00'],
            ['user_id' => 2, 'valor' => '70.00'],
        ];
        $result = $this->calcularDivision('monto_fijo', 100.00, [1, 2], $valores);
        $this->assertSame(30.0, $result[1]);
        $this->assertSame(70.0, $result[2]);
        $this->assertSame(100.0, array_sum($result));
    }

    public function testMontoFijoSumaCorrecta(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '10.50'],
            ['user_id' => 2, 'valor' => '20.25'],
            ['user_id' => 3, 'valor' => '30.75'],
        ];
        $result = $this->calcularDivision('monto_fijo', 61.50, [1, 2, 3], $valores);
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
        $result = $this->calcularDivision('porcentaje', 200.00, [1, 2], $valores);
        $this->assertSame(120.0, $result[1]); // 200 * 60%
        $this->assertSame(80.0, $result[2]);  // 200 * 40%
        $this->assertSame(200.0, array_sum($result));
    }

    public function testPorcentajeTresPersonasConRedondeo(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '33.33'],
            ['user_id' => 2, 'valor' => '33.33'],
            ['user_id' => 3, 'valor' => '33.34'],
        ];
        $result = $this->calcularDivision('porcentaje', 100.00, [1, 2, 3], $valores);
        // 33.33, 33.33, 33.34 = 100%
        // Montos: 33.33, 33.33, 33.34 = 100.00
        $this->assertSame(33.33, $result[1]);
        $this->assertSame(33.33, $result[2]);
        // Last gets 33.34 - if rounding diff, last gets adjusted
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
            $result = $this->calcularDivision('porcentaje', $s['monto'], $ids, $valores);
            $suma = round(array_sum($result), 2);
            $this->assertSame(round($s['monto'], 2), $suma, "Fallo: monto={$s['monto']} pcts=" . json_encode($s['pcts']) . " suma=$suma");
        }
    }

    public function testMontoFijoSumaInvalidaNoAlcanza(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '10.00'],
            ['user_id' => 2, 'valor' => '10.00'],
        ];
        $result = $this->calcularDivision('monto_fijo', 100.00, [1, 2], $valores);
        // La funcion no valida, solo calcula. La validacion esta en el controller.
        $this->assertSame(20.0, array_sum($result));
    }

    public function testIgualitarioUsuarioUnico(): void
    {
        $result = $this->calcularDivision('igualitario', 50.00, [1]);
        $this->assertCount(1, $result);
        $this->assertSame(50.0, $result[1]);
        $this->assertSame(50.0, array_sum($result));
    }

    public function testPorcentajeEnteros(): void
    {
        $valores = [
            ['user_id' => 1, 'valor' => '20'],
            ['user_id' => 2, 'valor' => '30'],
            ['user_id' => 3, 'valor' => '50'],
        ];
        $result = $this->calcularDivision('porcentaje', 1000.00, [1, 2, 3], $valores);
        $this->assertSame(200.0, $result[1]);
        $this->assertSame(300.0, $result[2]);
        $this->assertSame(500.0, $result[3]);
        $this->assertSame(1000.0, array_sum($result));
    }
}
