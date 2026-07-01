<?php

use CodeIgniter\Test\CIUnitTestCase;

final class GrupoCardRenderTest extends CIUnitTestCase
{
    private function render(array $overrides = []): string
    {
        return view('components/cards/grupo', array_merge([
            'variant' => 'operational',
            'nombre' => 'Test',
            'estado' => 'activo',
            'saldo' => 0,
            'entrarUrl' => '/grupos/1',
            'gastoUrl' => '/gastos/nuevo?grupo_id=1',
        ], $overrides));
    }

    public function testActivoMuestraGastito(): void
    {
        $html = $this->render(['estado' => 'activo', 'gastoUrl' => '/gastos/nuevo?grupo_id=1']);
        $this->assertStringContainsString('+ Gastito', $html);
    }

    public function testActivoMuestraEntrar(): void
    {
        $html = $this->render(['estado' => 'activo']);
        $this->assertStringContainsString('Entrar', $html);
    }

    public function testCerradoConDeudaPropiaMuestraSaldarDeuda(): void
    {
        $html = $this->render([
            'estado' => 'cerrado',
            'saldo' => -5000,
            'balanceUrl' => '/grupos/1/balance',
            'tieneDeuda' => true,
            'deudaAcreedor' => 'Juan',
        ]);
        $this->assertStringContainsString('Saldar deuda', $html);
    }

    public function testCerradoSinDeudaPropiaPeroConDeudasDeTercerosMuestraVerBalance(): void
    {
        $html = $this->render([
            'estado' => 'cerrado',
            'saldo' => 0,
            'tieneDeuda' => false,
            'esAdmin' => true,
            'grupoSaldado' => false,
        ]);
        $this->assertStringContainsString('Ver balance', $html);
        $this->assertStringNotContainsString('Liquidar grupo', $html);
    }

    public function testCerradoGrupoSaldadoAdminMuestraLiquidarGrupo(): void
    {
        $html = $this->render([
            'estado' => 'cerrado',
            'saldo' => 0,
            'tieneDeuda' => false,
            'esAdmin' => true,
            'grupoSaldado' => true,
        ]);
        $this->assertStringContainsString('Liquidar grupo', $html);
    }

    public function testCerradoGrupoSaldadoMiembroMuestraVerBalance(): void
    {
        $html = $this->render([
            'estado' => 'cerrado',
            'saldo' => 0,
            'tieneDeuda' => false,
            'esAdmin' => false,
            'grupoSaldado' => true,
        ]);
        $this->assertStringContainsString('Ver balance', $html);
        $this->assertStringNotContainsString('Liquidar grupo', $html);
    }

    public function testLiquidadoSinAcciones(): void
    {
        $html = $this->render(['estado' => 'liquidado']);
        $this->assertStringNotContainsString('+ Gastito', $html);
        $this->assertStringNotContainsString('Liquidar grupo', $html);
        $this->assertStringNotContainsString('Saldar deuda', $html);
    }

    public function testMinimalPanelCuerpoEsEnlace(): void
    {
        $html = view('components/cards/grupo', [
            'variant' => 'minimal_panel',
            'nombre' => 'Test',
            'estado' => 'activo',
            'saldo' => 0,
            'totalGastado' => 1000,
            'entrarUrl' => '/grupos/1',
            'gastoUrl' => '/gastos/nuevo?grupo_id=1',
        ]);
        $this->assertStringContainsString('<a href', $html);
        $this->assertStringContainsString('group-card-minimal-link', $html);
        $this->assertStringNotContainsString('>Entrar<', $html);
    }

    public function testMinimalPanelMuestraGastito(): void
    {
        $html = view('components/cards/grupo', [
            'variant' => 'minimal_panel',
            'nombre' => 'Test',
            'estado' => 'activo',
            'saldo' => 0,
            'totalGastado' => 1000,
            'entrarUrl' => '/grupos/1',
            'gastoUrl' => '/gastos/nuevo?grupo_id=1',
        ]);
        $this->assertStringContainsString('+ Gastito', $html);
    }

    public function testNoExistenEnlacesAnidados(): void
    {
        $html = $this->render(['estado' => 'activo']);
        $count = substr_count($html, '<a ');
        $this->assertLessThanOrEqual(3, $count, 'No deben existir mas de 3 enlaces (Entrar + accion + gecko)');
    }

    public function testCompactCuerpoEsEnlace(): void
    {
        $html = view('components/cards/grupo', [
            'variant' => 'compact',
            'nombre' => 'Test',
            'estado' => 'activo',
            'saldo' => 100,
            'entrarUrl' => '/grupos/1',
            'gastoUrl' => '/gastos/nuevo?grupo_id=1',
        ]);
        $this->assertStringContainsString('dash-list-item', $html);
        $this->assertStringNotContainsString('>Entrar<', $html);
    }
}
