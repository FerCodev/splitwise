<?php

use App\Services\Reportes;
use CodeIgniter\Test\CIUnitTestCase;

final class ReportesTest extends CIUnitTestCase
{
    private array $gastos = [
        [
            'fecha' => '2026-06-01',
            'grupo_id' => 1,
            'grupo' => 'Casa',
            'categoria_id' => 10,
            'categoria' => 'Servicios',
            'descripcion' => 'Luz',
            'pagador' => 'Fernando',
            'monto' => 1000,
        ],
        [
            'fecha' => '2026-06-05',
            'grupo_id' => 1,
            'grupo' => 'Casa',
            'categoria_id' => 11,
            'categoria' => 'Comida',
            'descripcion' => 'Super',
            'pagador' => 'Pareja',
            'monto' => 2500.50,
        ],
        [
            'fecha' => '2026-07-01',
            'grupo_id' => 2,
            'grupo' => 'Viaje',
            'categoria_id' => 11,
            'categoria' => 'Comida',
            'descripcion' => 'Cena',
            'pagador' => 'Juan',
            'monto' => 3000,
        ],
    ];

    public function testMetodosEstaticosExisten(): void
    {
        $this->assertTrue(method_exists(Reportes::class, 'resumenGlobal'));
        $this->assertTrue(method_exists(Reportes::class, 'gastosPorCategoria'));
        $this->assertTrue(method_exists(Reportes::class, 'gastosPorGrupo'));
        $this->assertTrue(method_exists(Reportes::class, 'ultimosMovimientos'));
        $this->assertTrue(method_exists(Reportes::class, 'gastosParaExportar'));
        $this->assertTrue(method_exists(Reportes::class, 'calcularResumen'));
        $this->assertTrue(method_exists(Reportes::class, 'aplicarFiltros'));
        $this->assertTrue(method_exists(Reportes::class, 'agruparGastosPorCategoria'));
        $this->assertTrue(method_exists(Reportes::class, 'agruparGastosPorGrupo'));
        $this->assertTrue(method_exists(Reportes::class, 'formatearFilasCsv'));
    }

    public function testCalcularResumen(): void
    {
        $resumen = Reportes::calcularResumen(600000, 320000, 5000, 300000, 20000);

        $this->assertSame(600000.0, $resumen['total_gastado']);
        $this->assertSame(320000.0, $resumen['total_pagado']);
        $this->assertSame(5000.0, $resumen['total_recibido']);
        $this->assertSame(300000.0, $resumen['total_consumido']);
        $this->assertSame(20000.0, $resumen['total_pagos_enviados']);
        $this->assertSame(35000.0, $resumen['saldo']);
    }

    public function testCalcularResumenRedondeaSaldo(): void
    {
        $resumen = Reportes::calcularResumen(500, 100.335, 0, 50);

        $this->assertSame(500.0, $resumen['total_gastado']);
        $this->assertSame(100.34, $resumen['total_pagado']);
        $this->assertSame(50.34, $resumen['saldo']);
    }

    public function testAplicarFiltrosPorGrupo(): void
    {
        $rows = Reportes::aplicarFiltros($this->gastos, ['grupo_id' => 1]);

        $this->assertCount(2, $rows);
        $this->assertSame(['Luz', 'Super'], array_column($rows, 'descripcion'));
    }

    public function testAplicarFiltrosPorCategoria(): void
    {
        $rows = Reportes::aplicarFiltros($this->gastos, ['categoria_id' => 11]);

        $this->assertCount(2, $rows);
        $this->assertSame(['Super', 'Cena'], array_column($rows, 'descripcion'));
    }

    public function testAplicarFiltrosPorFechas(): void
    {
        $rows = Reportes::aplicarFiltros($this->gastos, [
            'fecha_desde' => '2026-06-02',
            'fecha_hasta' => '2026-06-30',
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('Super', $rows[0]['descripcion']);
    }

    public function testAplicarFiltrosCombinados(): void
    {
        $rows = Reportes::aplicarFiltros($this->gastos, [
            'grupo_id' => 1,
            'categoria_id' => 11,
            'fecha_desde' => '2026-06-01',
            'fecha_hasta' => '2026-06-30',
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame('Super', $rows[0]['descripcion']);
    }

    public function testAgruparGastosPorCategoria(): void
    {
        $rows = Reportes::agruparGastosPorCategoria($this->gastos);

        $this->assertSame('Comida', $rows[0]['categoria']);
        $this->assertSame(2, $rows[0]['cantidad']);
        $this->assertSame(5500.50, $rows[0]['total']);
        $this->assertSame('Servicios', $rows[1]['categoria']);
        $this->assertSame(1000.0, $rows[1]['total']);
    }

    public function testAgruparGastosPorCategoriaUsaOtrosSiFaltaNombre(): void
    {
        $rows = Reportes::agruparGastosPorCategoria([
            ['categoria' => '', 'monto' => 100],
            ['monto' => 50],
        ]);

        $this->assertSame('Otros', $rows[0]['categoria']);
        $this->assertSame(2, $rows[0]['cantidad']);
        $this->assertSame(150.0, $rows[0]['total']);
    }

    public function testAgruparGastosPorGrupo(): void
    {
        $rows = Reportes::agruparGastosPorGrupo($this->gastos);

        $this->assertSame('Casa', $rows[0]['grupo']);
        $this->assertSame(2, $rows[0]['cantidad']);
        $this->assertSame(3500.50, $rows[0]['total']);
        $this->assertSame('Viaje', $rows[1]['grupo']);
        $this->assertSame(3000.0, $rows[1]['total']);
    }

    public function testAgruparGastosPorGrupoUsaSinGrupoSiFaltaNombre(): void
    {
        $rows = Reportes::agruparGastosPorGrupo([
            ['grupo' => '', 'monto' => 100],
            ['monto' => 50],
        ]);

        $this->assertSame('Sin grupo', $rows[0]['grupo']);
        $this->assertSame(2, $rows[0]['cantidad']);
        $this->assertSame(150.0, $rows[0]['total']);
    }

    public function testFormatearFilasCsvConDatos(): void
    {
        $lineas = Reportes::formatearFilasCsv([$this->gastos[0]]);

        $this->assertCount(1, $lineas);
        $this->assertSame([
            '2026-06-01',
            'Casa',
            'Servicios',
            'Luz',
            'Fernando',
            '1000.00',
        ], $lineas[0]);
    }

    public function testFormatearFilasCsvConMontoCeroYDefaults(): void
    {
        $lineas = Reportes::formatearFilasCsv([[]]);

        $this->assertSame(['', '', '', '', '', '0.00'], $lineas[0]);
    }

    public function testFormatearFilasCsvVacio(): void
    {
        $this->assertSame([], Reportes::formatearFilasCsv([]));
    }

    public function testFiltroConceptualDeAlcancePorUsuarioNoAceptaGrupoNoIncluido(): void
    {
        $rows = Reportes::aplicarFiltros($this->gastos, ['grupo_id' => 999]);

        $this->assertSame([], $rows);
    }
}
