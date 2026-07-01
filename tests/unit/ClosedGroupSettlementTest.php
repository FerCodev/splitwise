<?php

use App\Models\Grupo;
use App\Models\Gasto;
use App\Services\GroupPermission;
use CodeIgniter\Test\CIUnitTestCase;

final class ClosedGroupSettlementTest extends CIUnitTestCase
{
    public function testRestriccionEstadoPagoCreateBloqueadoEnCerrado(): void
    {
        $bloqueo = Grupo::restriccionEstado('cerrado', 'pago_create');
        $this->assertNotNull($bloqueo);
        $this->assertStringContainsString('saldar deudas', $bloqueo);
    }

    public function testRestriccionEstadoPagoEditBloqueadoEnCerrado(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'pago_edit'));
    }

    public function testRestriccionEstadoPagoDeleteBloqueadoEnCerrado(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'pago_delete'));
    }

    public function testRestriccionEstadoPagoCreateBloqueadoEnLiquidado(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'pago_create'));
    }

    public function testRestriccionEstadoGastoCreateBloqueadoEnCerrado(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'gasto_create'));
    }

    public function testRestriccionEstadoMiembroCreateBloqueadoEnCerrado(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'miembro_create'));
    }

    public function testTransicionesValidas(): void
    {
        $this->assertTrue(Grupo::transicionValida('activo', 'cerrado'));
        $this->assertTrue(Grupo::transicionValida('cerrado', 'activo'));
        $this->assertTrue(Grupo::transicionValida('cerrado', 'liquidado'));
    }

    public function testTransicionesInvalidas(): void
    {
        $this->assertFalse(Grupo::transicionValida('activo', 'liquidado'));
        $this->assertFalse(Grupo::transicionValida('liquidado', 'activo'));
        $this->assertFalse(Grupo::transicionValida('liquidado', 'cerrado'));
        $this->assertFalse(Grupo::transicionValida('activo', 'activo'));
    }

    public function testAdminPuedeCambiarEstadoEnCerrado(): void
    {
        $this->assertNull(GroupPermission::check('admin', 'cerrado', 'grupo_estado'));
    }

    public function testMemberNoPuedeCambiarEstadoEnCerrado(): void
    {
        $this->assertNotNull(GroupPermission::check('member', 'cerrado', 'grupo_estado'));
    }

    public function testMemberNoPuedeCrearPagoEnCerrado(): void
    {
        $this->assertNotNull(GroupPermission::check('member', 'cerrado', 'pago_create'));
    }

    public function testAdminGetAllCerradoBloqueaGastosYPagos(): void
    {
        $p = GroupPermission::getAll('admin', 'cerrado', 1);
        $this->assertFalse($p['puede_crear_gasto']);
        $this->assertFalse($p['puede_editar_gasto']);
        $this->assertFalse($p['puede_eliminar_gasto']);
        $this->assertFalse($p['puede_crear_pago']);
        $this->assertFalse($p['puede_editar_pago']);
        $this->assertFalse($p['puede_eliminar_pago']);
        $this->assertFalse($p['puede_agregar_miembro']);
        $this->assertFalse($p['puede_quitar_miembro']);
        $this->assertFalse($p['puede_cambiar_rol']);
    }

    public function testAdminGetAllCerradoPermiteGestionBasica(): void
    {
        $p = GroupPermission::getAll('admin', 'cerrado', 1);
        $this->assertFalse($p['puede_editar_grupo']);
        $this->assertTrue($p['puede_cambiar_estado']);
    }

    public function testAdminGetAllLiquidadoBloqueaTodo(): void
    {
        $p = GroupPermission::getAll('admin', 'liquidado', 1);
        $this->assertFalse($p['puede_crear_gasto']);
        $this->assertFalse($p['puede_crear_pago']);
        $this->assertFalse($p['puede_editar_grupo']);
        $this->assertFalse($p['puede_eliminar_grupo']);
        $this->assertFalse($p['puede_agregar_miembro']);
        $this->assertFalse($p['puede_editar_pago']);
        $this->assertFalse($p['puede_eliminar_pago']);
    }

    public function testDeudasAparecenCuandoHayDiferencia(): void
    {
        $balance = Gasto::computeBalance(
            [['user_id' => 1, 'name' => 'A'], ['user_id' => 2, 'name' => 'B']],
            [1 => 300],
            [1 => 150, 2 => 150],
            [],
            []
        );
        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertCount(1, $deudas);
        $this->assertSame(2, (int) $deudas[0]['deudor_id']);
        $this->assertSame(1, (int) $deudas[0]['acreedor_id']);
    }

    public function testPagoTotalEliminaDeuda(): void
    {
        $balance = Gasto::computeBalance(
            [['user_id' => 1, 'name' => 'A'], ['user_id' => 2, 'name' => 'B']],
            [1 => 300],
            [1 => 150, 2 => 150],
            [2 => 150],
            [1 => 150]
        );
        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertEmpty($deudas);
    }

    public function testPagoParcialReduceDeuda(): void
    {
        $balance = Gasto::computeBalance(
            [['user_id' => 1, 'name' => 'A'], ['user_id' => 2, 'name' => 'B']],
            [1 => 500],
            [1 => 250, 2 => 250],
            [2 => 100],
            [1 => 100]
        );
        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertCount(1, $deudas);
        $this->assertSame(2, (int) $deudas[0]['deudor_id']);
        $this->assertSame(1, (int) $deudas[0]['acreedor_id']);
        $this->assertSame(150.0, (float) $deudas[0]['monto']);
    }

    public function testLiquidadoBloqueaTodoEscritura(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'gasto_create'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'pago_create'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'grupo_edit'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'miembro_create'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'miembro_role'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'miembro_delete'));
    }

    public function testCerrarConservaSaldos(): void
    {
        $balance = Gasto::computeBalance(
            [['user_id' => 1, 'name' => 'A'], ['user_id' => 2, 'name' => 'B']],
            [1 => 1000],
            [1 => 500, 2 => 500],
            [],
            []
        );
        $this->assertCount(2, $balance);
        $this->assertSame(500.0, $balance[0]['saldo']);
        $this->assertSame(-500.0, $balance[1]['saldo']);
    }

    public function testDeudasKeysContienenColumnasRequeridas(): void
    {
        $balance = Gasto::computeBalance(
            [['user_id' => 1, 'name' => 'A'], ['user_id' => 2, 'name' => 'B']],
            [1 => 300],
            [1 => 150, 2 => 150],
            [],
            []
        );
        $deudas = Gasto::computeDeudasFromBalance($balance);
        $this->assertArrayHasKey('deudor', $deudas[0]);
        $this->assertArrayHasKey('deudor_id', $deudas[0]);
        $this->assertArrayHasKey('acreedor', $deudas[0]);
        $this->assertArrayHasKey('acreedor_id', $deudas[0]);
        $this->assertArrayHasKey('monto', $deudas[0]);
    }

    public function testGrupoEditBloqueadoEnCerrado(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'grupo_edit'));
    }

    public function testGrupoEditBloqueadoEnLiquidado(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'grupo_edit'));
    }

    public function testAdminNoPuedeEditarGrupoEnCerrado(): void
    {
        $result = GroupPermission::check('admin', 'cerrado', 'grupo_edit');
        $this->assertNotNull($result);
    }

    public function testMemberNoPuedeEditarGrupoEnCerrado(): void
    {
        $result = GroupPermission::check('member', 'cerrado', 'grupo_edit');
        $this->assertNotNull($result);
    }

    public function testMontoACentavosConDecimal(): void
    {
        $reflection = new ReflectionClass(\App\Controllers\Grupos::class);
        $method = $reflection->getMethod('montoACentavos');
        $method->setAccessible(true);

        $this->assertSame(12345, $method->invoke(null, '123.45'));
        $this->assertSame(10000, $method->invoke(null, '100'));
        $this->assertSame(1, $method->invoke(null, '0.01'));
        $this->assertSame(100, $method->invoke(null, 1.0));
    }

    public function testMontoACentavosConComaDecimal(): void
    {
        $reflection = new ReflectionClass(\App\Controllers\Grupos::class);
        $method = $reflection->getMethod('montoACentavos');
        $method->setAccessible(true);

        $this->assertSame(12345, $method->invoke(null, '123,45'));
    }

    public function testMontoACentavosRechazaCero(): void
    {
        $reflection = new ReflectionClass(\App\Controllers\Grupos::class);
        $method = $reflection->getMethod('montoACentavos');
        $method->setAccessible(true);

        $this->assertNull($method->invoke(null, '0'));
        $this->assertNull($method->invoke(null, '0.00'));
        $this->assertNull($method->invoke(null, '-5'));
    }

    public function testMontoACentavosRechazaNoNumerico(): void
    {
        $reflection = new ReflectionClass(\App\Controllers\Grupos::class);
        $method = $reflection->getMethod('montoACentavos');
        $method->setAccessible(true);

        $this->assertNull($method->invoke(null, 'abc'));
        $this->assertNull($method->invoke(null, ''));
        $this->assertNull($method->invoke(null, null));
    }

    public function testMontoACentavosRedondeaTresDecimales(): void
    {
        $reflection = new ReflectionClass(\App\Controllers\Grupos::class);
        $method = $reflection->getMethod('montoACentavos');
        $method->setAccessible(true);

        $this->assertSame(123, $method->invoke(null, '1.234'));
        $this->assertSame(124, $method->invoke(null, '1.236'));
    }

    public function testEsFechaValidaAceptaYMD(): void
    {
        $reflection = new ReflectionClass(\App\Controllers\Grupos::class);
        $method = $reflection->getMethod('esFechaValida');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke(null, '2026-07-15'));
        $this->assertTrue($method->invoke(null, '2026-01-01'));
    }

    public function testEsFechaValidaRechazaFormatosInvalidos(): void
    {
        $reflection = new ReflectionClass(\App\Controllers\Grupos::class);
        $method = $reflection->getMethod('esFechaValida');
        $method->setAccessible(true);

        $this->assertFalse($method->invoke(null, '15/07/2026'));
        $this->assertFalse($method->invoke(null, '2026-13-01'));
        $this->assertFalse($method->invoke(null, ''));
        $this->assertFalse($method->invoke(null, null));
        $this->assertFalse($method->invoke(null, '2026-07-01 12:00:00'));
    }

    public function testRestriccionEstadoCerradoBloqueaGrupoEdit(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'grupo_edit'));
    }

    public function testRestriccionEstadoCerradoBloqueaGrupoDelete(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'grupo_delete'));
    }

    public function testRestriccionEstadoActivoPermiteGrupoEdit(): void
    {
        $this->assertNull(Grupo::restriccionEstado('activo', 'grupo_edit'));
    }
}
