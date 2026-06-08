<?php

use App\Models\Grupo;

/**
 * @internal
 */
final class DashboardResumenTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testUsuarioConSaldoPositivoEnUnGrupo(): void
    {
        $grupos = [
            ['id' => 1, 'estado' => 'activo', 'mi_saldo' => 150.0],
        ];

        $r = Grupo::computeDashboardResumen($grupos);

        $this->assertSame(1, $r['cantidadActivos']);
        $this->assertSame(0, $r['cantidadCerrados']);
        $this->assertSame(0, $r['cantidadLiquidados']);
        $this->assertSame(150.0, $r['globalSaldo']);
        $this->assertSame(1, $r['gruposAFavor']);
        $this->assertSame(0, $r['gruposDebe']);
    }

    public function testUsuarioConSaldoNegativoEnUnGrupo(): void
    {
        $grupos = [
            ['id' => 1, 'estado' => 'activo', 'mi_saldo' => -75.0],
        ];

        $r = Grupo::computeDashboardResumen($grupos);

        $this->assertSame(1, $r['cantidadActivos']);
        $this->assertSame(-75.0, $r['globalSaldo']);
        $this->assertSame(0, $r['gruposAFavor']);
        $this->assertSame(1, $r['gruposDebe']);
    }

    public function testSaldoGlobalSumaCorrectamenteVariosGrupos(): void
    {
        $grupos = [
            ['id' => 1, 'estado' => 'activo', 'mi_saldo' => 100.0],
            ['id' => 2, 'estado' => 'cerrado', 'mi_saldo' => -50.0],
            ['id' => 3, 'estado' => 'liquidado', 'mi_saldo' => 25.0],
        ];

        $r = Grupo::computeDashboardResumen($grupos);

        $this->assertSame(1, $r['cantidadActivos']);
        $this->assertSame(1, $r['cantidadCerrados']);
        $this->assertSame(1, $r['cantidadLiquidados']);
        $this->assertSame(75.0, $r['globalSaldo']);  // 100 - 50 + 25 = 75
        $this->assertSame(2, $r['gruposAFavor']);
        $this->assertSame(1, $r['gruposDebe']);
    }

    public function testGruposSeCuentanPorEstado(): void
    {
        $grupos = [
            ['id' => 1, 'estado' => 'activo', 'mi_saldo' => 0.0],
            ['id' => 2, 'estado' => 'activo', 'mi_saldo' => 0.0],
            ['id' => 3, 'estado' => 'cerrado', 'mi_saldo' => 0.0],
            ['id' => 4, 'estado' => 'liquidado', 'mi_saldo' => 0.0],
            ['id' => 5, 'estado' => 'liquidado', 'mi_saldo' => 0.0],
        ];

        $r = Grupo::computeDashboardResumen($grupos);

        $this->assertSame(2, $r['cantidadActivos']);
        $this->assertSame(1, $r['cantidadCerrados']);
        $this->assertSame(2, $r['cantidadLiquidados']);
    }

    public function testSaldoCeroNoCuentaComoFavorNiDebe(): void
    {
        $grupos = [
            ['id' => 1, 'estado' => 'activo', 'mi_saldo' => 0.0],
        ];

        $r = Grupo::computeDashboardResumen($grupos);

        $this->assertSame(0, $r['gruposAFavor']);
        $this->assertSame(0, $r['gruposDebe']);
        $this->assertSame(0.0, $r['globalSaldo']);
    }

    public function testListaVaciaDevuelveCeros(): void
    {
        $r = Grupo::computeDashboardResumen([]);

        $this->assertSame(0, $r['cantidadActivos']);
        $this->assertSame(0, $r['cantidadCerrados']);
        $this->assertSame(0, $r['cantidadLiquidados']);
        $this->assertSame(0.0, $r['globalSaldo']);
        $this->assertSame(0, $r['gruposAFavor']);
        $this->assertSame(0, $r['gruposDebe']);
    }

    public function testEstadoDefaultEsActivo(): void
    {
        $grupos = [
            ['id' => 1, 'mi_saldo' => 10.0],
        ];

        $r = Grupo::computeDashboardResumen($grupos);

        $this->assertSame(1, $r['cantidadActivos']);
        $this->assertSame(10.0, $r['globalSaldo']);
    }
}
