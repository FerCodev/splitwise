<?php

use App\Services\Reportes;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class ReportesFilterValidationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $refresh = false;
    protected $migrate = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->session = [
            'isLoggedIn' => true,
            'userId'     => 1,
            'userName'   => 'Test',
            'userEmail'  => 'test@test.com',
            'userRole'   => 'user',
        ];
    }
    public function testValidarFiltrosDesdeMayorQueHastaEsInvalido(): void
    {
        $error = Reportes::validarFiltros([
            'fecha_desde' => '2026-07-31',
            'fecha_hasta' => '2026-07-01',
        ]);
        $this->assertNotNull($error);
        $this->assertStringContainsString('no puede ser posterior', $error);
    }

    public function testValidarFiltrosDesdeFormatoInvalidoDevuelveError(): void
    {
        $error = Reportes::validarFiltros(['fecha_desde' => 'no-es-fecha']);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Desde', $error);
        $this->assertStringContainsString('no tiene un formato valido', $error);
    }

    public function testValidarFiltrosHastaFormatoInvalidoDevuelveError(): void
    {
        $error = Reportes::validarFiltros(['fecha_hasta' => '31/07/2026']);
        $this->assertNotNull($error);
        $this->assertStringContainsString('Hasta', $error);
        $this->assertStringContainsString('no tiene un formato valido', $error);
    }

    public function testValidarFiltrosFechasConHoraSonInvalidas(): void
    {
        $error = Reportes::validarFiltros(['fecha_desde' => '2026-07-01 12:00:00']);
        $this->assertNotNull($error);
    }

    public function testValidarFiltrosFechasConTimezoneSonInvalidas(): void
    {
        $error = Reportes::validarFiltros(['fecha_desde' => '2026-07-01T00:00:00+00:00']);
        $this->assertNotNull($error);
    }

    public function testValidarFiltrosRangoCompletoValido(): void
    {
        $this->assertNull(Reportes::validarFiltros([
            'fecha_desde' => '2026-01-01',
            'fecha_hasta' => '2026-12-31',
        ]));
    }

    public function testValidarFiltrosMismoDiaEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros([
            'fecha_desde' => '2026-07-15',
            'fecha_hasta' => '2026-07-15',
        ]));
    }

    public function testValidarFiltrosSoloDesdeEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros(['fecha_desde' => '2026-07-01']));
    }

    public function testValidarFiltrosSoloHastaEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros(['fecha_hasta' => '2026-07-31']));
    }

    public function testValidarFiltrosSinFechasEsValido(): void
    {
        $this->assertNull(Reportes::validarFiltros([]));
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

    public function testValidarFiltrosYearMonthAntiguoEsIgnorado(): void
    {
        $this->assertNull(Reportes::validarFiltros(['year_month' => '2026-07']));
    }

    public function testHttpReportesRangoInvalidoRespondeOkYConError(): void
    {
        $result = $this->get('/reportes?fecha_desde=2026-07-31&fecha_hasta=2026-07-01');

        $result->assertOK();
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('no puede ser posterior', $body);
    }

    public function testHttpReportesRangoInvalidoConservaFechasEnFormulario(): void
    {
        $result = $this->get('/reportes?fecha_desde=2026-07-31&fecha_hasta=2026-07-01');

        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('value="2026-07-31"', $body);
        $this->assertStringContainsString('value="2026-07-01"', $body);
    }

    public function testHttpReportesRangoInvalidoNoMuestraMovimientosFiltrados(): void
    {
        $result = $this->get('/reportes?fecha_desde=2026-07-31&fecha_hasta=2026-07-01');

        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('Total gastado', $body);
    }

    public function testHttpExportarCsvRangoInvalidoDevuelve400(): void
    {
        $result = $this->get('/reportes/exportar?fecha_desde=2026-07-31&fecha_hasta=2026-07-01');

        $result->assertStatus(400);
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('no puede ser posterior', $body);
    }

    public function testHttpExportarPdfRangoInvalidoDevuelve400(): void
    {
        $result = $this->get('/reportes/exportar-pdf?fecha_desde=2026-07-31&fecha_hasta=2026-07-01');

        $result->assertStatus(400);
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('no puede ser posterior', $body);
    }

    public function testHttpFvNoPermiteSaltarValidacion(): void
    {
        $result = $this->get('/reportes?_fv=1&fecha_desde=2026-07-31&fecha_hasta=2026-07-01');

        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('no puede ser posterior', $body);
    }

    public function testHttpFechaDesdeFormatoInvalidoEnExportarRetorna400(): void
    {
        $result = $this->get('/reportes/exportar?fecha_desde=abc');

        $result->assertStatus(400);
        $body = (string) $result->response()->getBody();
        $this->assertStringContainsString('no tiene un formato valido', $body);
    }

    public function testHttpReportesSinFechasRespondeOkSinError(): void
    {
        $result = $this->get('/reportes');

        $result->assertOK();
        $body = (string) $result->response()->getBody();
        $this->assertStringNotContainsString('no puede ser posterior', $body);
        $this->assertStringNotContainsString('no tiene un formato valido', $body);
    }
}
