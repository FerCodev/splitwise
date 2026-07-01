<?php

use CodeIgniter\Test\CIUnitTestCase;

final class DeudaPendienteCardTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        service('renderer')->resetData();
    }

    private function render(array $overrides = []): string
    {
        return view('components/cards/deuda_pendiente', array_merge([
            'monto'          => 5000,
            'acreedorNombre' => 'Fernando',
            'acreedorId'     => 2,
            'grupoId'        => 1,
            'grupoEstado'    => 'activo',
            'mediosCobro'    => [],
            'formId'         => 'deuda-0-2',
            'fechaDefault'   => '2026-07-15',
        ], $overrides));
    }

    public function testMuestraMontoYAcreedor(): void
    {
        $html = $this->render();
        $this->assertStringContainsString('$5.000,00', $html);
        $this->assertStringContainsString('a Fernando', $html);
    }

    public function testMuestraPillLeDebes(): void
    {
        $html = $this->render();
        $this->assertStringContainsString('Le debés', $html);
    }

    public function testActivoMuestraRegistrarPago(): void
    {
        $html = $this->render(['grupoEstado' => 'activo']);
        $this->assertStringContainsString('Registrar pago', $html);
        $this->assertStringNotContainsString('Saldar deuda', $html);
    }

    public function testCerradoMuestraSaldarDeuda(): void
    {
        $html = $this->render(['grupoEstado' => 'cerrado']);
        $this->assertStringContainsString('Saldar deuda', $html);
        $this->assertStringNotContainsString('Registrar pago', $html);
    }

    public function testLiquidadoNoMuestraCTA(): void
    {
        $html = $this->render(['grupoEstado' => 'liquidado']);
        $this->assertStringNotContainsString('Registrar pago', $html);
        $this->assertStringNotContainsString('Saldar deuda', $html);
        $this->assertStringNotContainsString('deuda-pendiente-cta', $html);
    }

    public function testActivoApuntaAPagosEndpoint(): void
    {
        $html = $this->render(['grupoEstado' => 'activo']);
        $this->assertStringContainsString('action="', $html);
        $this->assertStringContainsString('pagos"', $html);
        $this->assertStringNotContainsString('saldar-deuda', $html);
    }

    public function testCerradoApuntaASaldarDeudaEndpoint(): void
    {
        $html = $this->render(['grupoEstado' => 'cerrado']);
        $this->assertStringContainsString('saldar-deuda', $html);
    }

    public function testVariasDeudasTienenIdsUnicos(): void
    {
        $html1 = view('components/cards/deuda_pendiente', [
            'monto' => 100, 'acreedorNombre' => 'A', 'acreedorId' => 2,
            'grupoId' => 1, 'grupoEstado' => 'activo', 'mediosCobro' => [],
            'formId' => 'deuda-0-2', 'fechaDefault' => '2026-07-15',
        ]);
        service('renderer')->resetData();
        $html2 = view('components/cards/deuda_pendiente', [
            'monto' => 200, 'acreedorNombre' => 'B', 'acreedorId' => 3,
            'grupoId' => 1, 'grupoEstado' => 'activo', 'mediosCobro' => [],
            'formId' => 'deuda-1-3', 'fechaDefault' => '2026-07-15',
        ]);

        $this->assertStringContainsString('id="deuda-0-2"', $html1);
        $this->assertStringContainsString('id="deuda-1-3"', $html2);
        $this->assertStringContainsString('data-target="deuda-0-2"', $html1);
        $this->assertStringContainsString('data-target="deuda-1-3"', $html2);
    }

    public function testSinMediosDeCobroMuestraMensaje(): void
    {
        $html = $this->render(['mediosCobro' => []]);
        $this->assertStringContainsString('no tiene medios de cobro registrados', $html);
    }

    public function testConMediosDeCobroNoMuestraMensajeVacio(): void
    {
        $html = $this->render(['mediosCobro' => [
            ['nombre' => 'Mercado Pago', 'alias' => 'fer.mp', 'cbu_cvu' => '', 'payment_link' => '', 'banco' => ''],
        ]]);
        $this->assertStringNotContainsString('no tiene medios de cobro registrados', $html);
        $this->assertStringContainsString('fer.mp', $html);
    }

    public function testMontoMaximoPrecargado(): void
    {
        $html = $this->render(['monto' => 119539]);
        $this->assertStringContainsString('value="119539.00"', $html);
    }

    public function testReceptorIdViaHiddenInput(): void
    {
        $html = $this->render(['acreedorId' => 5]);
        $this->assertStringContainsString('name="receptor_id" value="5"', $html);
    }

    public function testTextosConTildesCorrectas(): void
    {
        $html = $this->render(['grupoEstado' => 'cerrado']);
        $this->assertStringContainsString('Le debés', $html);
        $this->assertStringContainsString('está cerrado', $html);
        $this->assertStringContainsString('Saldá', $html);

        $htmlActivo = $this->render(['grupoEstado' => 'activo']);
        $this->assertStringContainsString('Registrá', $htmlActivo);
    }

    public function testMuestraLabelMontoConMax(): void
    {
        $html = $this->render(['monto' => 1500]);
        $this->assertStringContainsString('Monto (máx.', $html);
        $this->assertStringContainsString('$1.500,00)', $html);
    }

    public function testActivoTieneOrigenBalanceDetalle(): void
    {
        $html = $this->render(['grupoEstado' => 'activo']);
        $this->assertStringContainsString('origen" value="grupo_balance_detalle"', $html);
    }
}
