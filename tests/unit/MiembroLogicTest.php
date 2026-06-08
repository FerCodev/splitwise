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
}
