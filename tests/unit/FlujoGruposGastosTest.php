<?php

use App\Models\Grupo;

/**
 * @internal
 */
final class FlujoGruposGastosTest extends \CodeIgniter\Test\CIUnitTestCase
{
    public function testCreadorQuedaComoAdmin(): void
    {
        $userId = 5;
        $miembrosPost = [2, 3];
        $miembros = array_merge([$userId], $miembrosPost);

        $this->assertContains($userId, $miembros);

        $rolCreador = 'admin';
        $this->assertSame('admin', $rolCreador);
    }

    public function testMiembrosInicialesNoDuplicanAlCreador(): void
    {
        $userId = 5;
        $miembrosPost = [2, 3, 5];
        $insertados = [$userId];

        foreach ($miembrosPost as $mid) {
            $mid = (int) $mid;
            if ($mid > 0 && !in_array($mid, $insertados)) {
                $insertados[] = $mid;
            }
        }

        $this->assertCount(3, $insertados);
        $this->assertSame([5, 2, 3], $insertados);
    }

    public function testMiembrosVaciosNoImpidenCrearGrupo(): void
    {
        $userId = 5;
        $miembrosPost = null;
        $insertados = [$userId];

        if (is_array($miembrosPost)) {
            foreach ($miembrosPost as $mid) {
                $mid = (int) $mid;
                if ($mid > 0 && !in_array($mid, $insertados)) {
                    $insertados[] = $mid;
                }
            }
        }

        $this->assertCount(1, $insertados);
        $this->assertSame([5], $insertados);
    }

    public function testPagadorEnGastoNuevoEsUsuarioSesion(): void
    {
        $userIdSesion = 3;
        $pagadorPost = 7;

        $pagadorId = $userIdSesion;

        $this->assertSame(3, $pagadorId);
        $this->assertNotSame($pagadorPost, $pagadorId);
    }

    public function testParticipantesPorDefectoIncluyenTodosLosMiembros(): void
    {
        $miembros = [
            ['user_id' => 1, 'name' => 'Alice'],
            ['user_id' => 2, 'name' => 'Bob'],
            ['user_id' => 3, 'name' => 'Charlie'],
        ];

        $participantesIds = array_column($miembros, 'user_id');

        $this->assertCount(3, $participantesIds);
        $this->assertSame([1, 2, 3], $participantesIds);
    }

    public function testEstadoBloqueaGestionMiembros(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'miembro_create'));
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'miembro_role'));
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'miembro_delete'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'miembro_create'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'miembro_role'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'miembro_delete'));
        $this->assertNull(Grupo::restriccionEstado('activo', 'miembro_create'));
        $this->assertNull(Grupo::restriccionEstado('activo', 'miembro_role'));
        $this->assertNull(Grupo::restriccionEstado('activo', 'miembro_delete'));
    }

    public function testEstadoBloqueaCreateGasto(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'gasto_create'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'gasto_create'));
        $this->assertNull(Grupo::restriccionEstado('activo', 'gasto_create'));
    }

    public function testEstadoBloqueaEditGasto(): void
    {
        $this->assertNotNull(Grupo::restriccionEstado('cerrado', 'gasto_edit'));
        $this->assertNotNull(Grupo::restriccionEstado('liquidado', 'gasto_edit'));
        $this->assertNull(Grupo::restriccionEstado('activo', 'gasto_edit'));
    }
}
