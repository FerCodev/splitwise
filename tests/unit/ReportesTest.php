<?php

use App\Services\Reportes;
use App\Controllers\Reportes as ReportesController;
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
        $this->assertTrue(method_exists(Reportes::class, 'gastosParaExportar'));
        $this->assertTrue(method_exists(Reportes::class, 'calcularResumen'));
        $this->assertTrue(method_exists(Reportes::class, 'formatearFilasCsv'));
        $this->assertTrue(method_exists(Reportes::class, 'topGrupos'));
        $this->assertTrue(method_exists(Reportes::class, 'topCategorias'));
        $this->assertTrue(method_exists(Reportes::class, 'movimientosFiltrados'));
        $this->assertTrue(method_exists(Reportes::class, 'deudasPendientes'));
    }

    public function testMetodoMensualEliminado(): void
    {
        $this->assertFalse(method_exists(Reportes::class, 'resumenMensual'));
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

    public function testPeriodoTextoSinFechas(): void
    {
        $this->assertSame('Todo el historial', ReportesController::periodoTexto([]));
    }

    public function testPeriodoTextoSoloDesde(): void
    {
        $this->assertSame('Desde 01/07/2026', ReportesController::periodoTexto(['fecha_desde' => '2026-07-01']));
    }

    public function testPeriodoTextoSoloHasta(): void
    {
        $this->assertSame('Hasta 31/07/2026', ReportesController::periodoTexto(['fecha_hasta' => '2026-07-31']));
    }

    public function testPeriodoTextoRangoCompleto(): void
    {
        $this->assertSame(
            '01/07/2026 al 31/07/2026',
            ReportesController::periodoTexto(['fecha_desde' => '2026-07-01', 'fecha_hasta' => '2026-07-31'])
        );
    }

    public function testPeriodoTextoUnSoloDia(): void
    {
        $this->assertSame(
            '15/07/2026 al 15/07/2026',
            ReportesController::periodoTexto(['fecha_desde' => '2026-07-15', 'fecha_hasta' => '2026-07-15'])
        );
    }

    public function testPeriodoTextoConGrupoYFechas(): void
    {
        $this->assertSame(
            '01/07/2026 al 31/07/2026',
            ReportesController::periodoTexto(['fecha_desde' => '2026-07-01', 'fecha_hasta' => '2026-07-31', 'grupo_id' => '5'])
        );
    }

    public function testPeriodoTextoConCategoriaYFechas(): void
    {
        $this->assertSame(
            'Desde 01/07/2026',
            ReportesController::periodoTexto(['fecha_desde' => '2026-07-01', 'categoria_id' => '3'])
        );
    }

    public function testPeriodoTextoIgnoraOldYearMonth(): void
    {
        $this->assertSame(
            'Todo el historial',
            ReportesController::periodoTexto(['year_month' => '2026-07'])
        );
    }

    public function testYearMonthNoEstaEnFiltrosEsperados(): void
    {
        $reflection = new ReflectionClass(ReportesController::class);
        $method = $reflection->getMethod('filtrosEsperados');
        $method->setAccessible(true);
        $instance = $reflection->newInstanceWithoutConstructor();
        $filtros = $method->invoke($instance);
        $this->assertArrayNotHasKey('year_month', $filtros);
        $this->assertArrayHasKey('fecha_desde', $filtros);
        $this->assertArrayHasKey('fecha_hasta', $filtros);
    }

    public function testValidarFiltrosSinFechasEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros([]));
    }

    public function testValidarFiltrosSoloDesdeEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros(['fecha_desde' => '2026-07-01']));
    }

    public function testValidarFiltrosSoloHastaEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros(['fecha_hasta' => '2026-07-31']));
    }

    public function testValidarFiltrosRangoCompletoValido(): void
    {
        $this->assertNull(Reportes::validarFiltros([
            'fecha_desde' => '2026-07-01',
            'fecha_hasta' => '2026-07-31',
        ]));
    }

    public function testValidarFiltrosMismoDiaEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros([
            'fecha_desde' => '2026-07-15',
            'fecha_hasta' => '2026-07-15',
        ]));
    }

    public function testValidarFiltrosGrupoConFechasEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros([
            'fecha_desde' => '2026-07-01',
            'fecha_hasta' => '2026-07-31',
            'grupo_id' => '5',
        ]));
    }

    public function testValidarFiltrosCategoriaConFechasEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros([
            'fecha_desde' => '2026-07-01',
            'categoria_id' => '3',
        ]));
    }

    public function testValidarFiltrosDesdePosteriorAHastaEsInvalido(): void
    {
        $error = Reportes::validarFiltros([
            'fecha_desde' => '2026-07-31',
            'fecha_hasta' => '2026-07-01',
        ]);
        $this->assertNotNull($error);
        $this->assertStringContainsString('no puede ser posterior', $error);
    }

    public function testValidarFiltrosDesdeFormatoInvalido(): void
    {
        $error = Reportes::validarFiltros(['fecha_desde' => 'no-es-fecha']);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Desde', $error);
    }

    public function testValidarFiltrosHastaFormatoInvalido(): void
    {
        $error = Reportes::validarFiltros(['fecha_hasta' => '31/07/2026']);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Hasta', $error);
    }

    public function testValidarFiltrosYearMonthAntiguoIgnorado(): void
    {
        $this->assertNull(Reportes::validarFiltros(['year_month' => '2026-07']));
    }

    public function testBuildWhereSinFechasNoAgregaRestricciones(): void
    {
        $where = $this->invocarBuildWhere(1, [], 'g');
        $this->assertSame('1=1', $where['sql']);
        $this->assertSame([1], $where['binds']);
    }

    public function testBuildWhereSoloDesdeUsaMayorIgual(): void
    {
        $where = $this->invocarBuildWhere(1, ['fecha_desde' => '2026-07-01'], 'g');
        $this->assertStringContainsString('g.fecha >= ?', $where['sql']);
        $this->assertContains('2026-07-01', $where['binds']);
    }

    public function testBuildWhereSoloHastaUsaMenorIgual(): void
    {
        $where = $this->invocarBuildWhere(1, ['fecha_hasta' => '2026-07-31'], 'g');
        $this->assertStringContainsString('g.fecha <= ?', $where['sql']);
        $this->assertContains('2026-07-31', $where['binds']);
    }

    public function testBuildWhereRangoCompletoConAmbosLimites(): void
    {
        $where = $this->invocarBuildWhere(1, [
            'fecha_desde' => '2026-07-01',
            'fecha_hasta' => '2026-07-31',
        ], 'g');
        $this->assertStringContainsString('g.fecha >= ?', $where['sql']);
        $this->assertStringContainsString('g.fecha <= ?', $where['sql']);
        $this->assertContains('2026-07-01', $where['binds']);
        $this->assertContains('2026-07-31', $where['binds']);
    }

    public function testBuildWhereMismoDia(): void
    {
        $where = $this->invocarBuildWhere(1, [
            'fecha_desde' => '2026-07-15',
            'fecha_hasta' => '2026-07-15',
        ], 'g');
        $this->assertStringContainsString('g.fecha >= ?', $where['sql']);
        $this->assertStringContainsString('g.fecha <= ?', $where['sql']);
    }

    public function testBuildWhereGrupoConFechas(): void
    {
        $where = $this->invocarBuildWhere(1, [
            'grupo_id' => '5',
            'fecha_desde' => '2026-07-01',
            'fecha_hasta' => '2026-07-31',
        ], 'g');
        $this->assertStringContainsString('g.grupo_id = ?', $where['sql']);
        $this->assertStringContainsString('g.fecha >= ?', $where['sql']);
        $this->assertStringContainsString('g.fecha <= ?', $where['sql']);
    }

    public function testBuildWhereCategoriaConFechas(): void
    {
        $where = $this->invocarBuildWhere(1, [
            'categoria_id' => '3',
            'fecha_desde' => '2026-07-01',
        ], 'g');
        $this->assertStringContainsString('g.categoria_id = ?', $where['sql']);
        $this->assertStringContainsString('g.fecha >= ?', $where['sql']);
    }

    public function testBuildWhereIgnoraYearMonthAntiguo(): void
    {
        $where = $this->invocarBuildWhere(1, ['year_month' => '2026-07'], 'g');
        $this->assertSame('1=1', $where['sql']);
    }

    public function testBuildWherePagosSinFechas(): void
    {
        $where = $this->invocarBuildWherePagos(1, [], 'p');
        $this->assertSame('1=1', $where['sql']);
        $this->assertSame([1], $where['binds']);
    }

    public function testBuildWherePagosRangoCompleto(): void
    {
        $where = $this->invocarBuildWherePagos(1, [
            'fecha_desde' => '2026-07-01',
            'fecha_hasta' => '2026-07-31',
        ], 'p');
        $this->assertStringContainsString('p.fecha >= ?', $where['sql']);
        $this->assertStringContainsString('p.fecha <= ?', $where['sql']);
    }

    public function testBuildWherePagosIgnoraYearMonth(): void
    {
        $where = $this->invocarBuildWherePagos(1, ['year_month' => '2026-07'], 'p');
        $this->assertSame('1=1', $where['sql']);
    }

    private function invocarBuildWhere(int $userId, array $filters, string $alias): array
    {
        $reflection = new ReflectionClass(Reportes::class);
        $method = $reflection->getMethod('buildWhere');
        $method->setAccessible(true);
        return $method->invoke(null, $userId, $filters, $alias);
    }

    private function invocarBuildWherePagos(int $userId, array $filters, string $alias): array
    {
        $reflection = new ReflectionClass(Reportes::class);
        $method = $reflection->getMethod('buildWherePagos');
        $method->setAccessible(true);
        return $method->invoke(null, $userId, $filters, $alias);
    }
}
