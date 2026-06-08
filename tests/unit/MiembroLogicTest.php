<?php

use App\Models\Grupo;

/**
 * @internal
 */
final class MiembroLogicTest extends \CodeIgniter\Test\CIUnitTestCase
{
    // ---- puedeQuitarMiembro ----

    public function testNoSePuedeQuitarUltimoAdmin(): void
    {
        $error = Grupo::puedeQuitarMiembro(1, 'admin', false);
        $this->assertNotNull($error);
        $this->assertStringContainsString('último administrador', $error);
    }

    public function testNoSePuedeQuitarMiembroConMovimientos(): void
    {
        $error = Grupo::puedeQuitarMiembro(2, 'member', true);
        $this->assertNotNull($error);
        $this->assertStringContainsString('movimientos', $error);
    }

    public function testSePuedeQuitarMiembroSinMovimientosYNoEsUltimoAdmin(): void
    {
        $error = Grupo::puedeQuitarMiembro(2, 'member', false);
        $this->assertNull($error);
    }

    public function testSePuedeQuitarAdminSiHayOtrosAdminsYSinMovimientos(): void
    {
        $error = Grupo::puedeQuitarMiembro(3, 'admin', false);
        $this->assertNull($error);
    }

    // ---- puedeCambiarRol ----

    public function testNoSePuedeCambiarUltimoAdminAMember(): void
    {
        $error = Grupo::puedeCambiarRol(1, 'admin', 'member');
        $this->assertNotNull($error);
        $this->assertStringContainsString('último administrador', $error);
    }

    public function testSePuedeCambiarAdminAMemberSiHayOtroAdmin(): void
    {
        $error = Grupo::puedeCambiarRol(2, 'admin', 'member');
        $this->assertNull($error);
    }

    public function testSePuedeCambiarMemberAAdmin(): void
    {
        $error = Grupo::puedeCambiarRol(1, 'member', 'admin');
        $this->assertNull($error);
    }

    public function testCambiarRolMismoRolNoDaError(): void
    {
        $error = Grupo::puedeCambiarRol(1, 'admin', 'admin');
        $this->assertNull($error);
    }

    // ---- restriccionEstado para miembros ----

    public function testNoSePuedeAgregarMiembroEnGrupoCerrado(): void
    {
        $error = Grupo::restriccionEstado('cerrado', 'miembro_create');
        $this->assertNotNull($error);
    }

    public function testNoSePuedeCambiarRolEnGrupoCerrado(): void
    {
        $error = Grupo::restriccionEstado('cerrado', 'miembro_role');
        $this->assertNotNull($error);
    }

    public function testNoSePuedeQuitarMiembroEnGrupoCerrado(): void
    {
        $error = Grupo::restriccionEstado('cerrado', 'miembro_delete');
        $this->assertNotNull($error);
    }

    public function testNoSePuedeAgregarMiembroEnGrupoLiquidado(): void
    {
        $error = Grupo::restriccionEstado('liquidado', 'miembro_create');
        $this->assertNotNull($error);
    }

    public function testNoSePuedeCambiarRolEnGrupoLiquidado(): void
    {
        $error = Grupo::restriccionEstado('liquidado', 'miembro_role');
        $this->assertNotNull($error);
    }

    public function testNoSePuedeQuitarMiembroEnGrupoLiquidado(): void
    {
        $error = Grupo::restriccionEstado('liquidado', 'miembro_delete');
        $this->assertNotNull($error);
    }

    public function testEnGrupoActivoNoHayBloqueoPorEstadoParaMiembros(): void
    {
        $this->assertNull(Grupo::restriccionEstado('activo', 'miembro_create'));
        $this->assertNull(Grupo::restriccionEstado('activo', 'miembro_role'));
        $this->assertNull(Grupo::restriccionEstado('activo', 'miembro_delete'));
    }
}
