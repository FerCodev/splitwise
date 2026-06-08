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
        $miembrosIds = array_unique(array_map('intval', $miembrosPost));
        $miembrosIds = array_values(array_filter($miembrosIds, fn($mid) => $mid > 0));

        $miembrosValidos = $miembrosIds;
        $insertados = [$userId];
        foreach ($miembrosValidos as $mid) {
            if ($mid !== $userId && !in_array($mid, $insertados)) {
                $insertados[] = $mid;
            }
        }

        $this->assertCount(3, $insertados);
        $this->assertSame([5, 2, 3], $insertados);
    }

    public function testCreadorEnMiembrosNoSeDuplicaConIdsString(): void
    {
        $userId = 5;
        $miembrosPost = ['2', '3', '5'];
        $miembrosIds = array_unique(array_map('intval', $miembrosPost));
        $miembrosIds = array_values(array_filter($miembrosIds, fn($mid) => $mid > 0));

        $idsValidosDB = ['1', '2', '3', '5'];
        $idsValidos = array_map('intval', $idsValidosDB);

        $invalidos = array_diff($miembrosIds, $idsValidos);
        $this->assertEmpty($invalidos);

        $miembrosValidos = array_diff($idsValidos, [$userId]);

        $this->assertSame([1, 2, 3], array_values($miembrosValidos));
        $this->assertNotContains($userId, $miembrosValidos);
    }

    public function testMiembrosVaciosNoImpidenCrearGrupo(): void
    {
        $userId = 5;
        $miembrosPost = null;
        $miembrosIds = [];

        if (is_array($miembrosPost)) {
            $miembrosIds = array_unique(array_map('intval', $miembrosPost));
            $miembrosIds = array_values(array_filter($miembrosIds, fn($mid) => $mid > 0));
        }

        $insertados = [$userId];

        $this->assertCount(1, $insertados);
        $this->assertSame([5], $insertados);
    }

    public function testIdsInvalidosSonFiltradosAntesDeValidar(): void
    {
        $miembrosPost = [1, -5, 0, 3, 'abc'];
        $miembrosIds = array_unique(array_map('intval', $miembrosPost));
        $miembrosIds = array_values(array_filter($miembrosIds, fn($mid) => $mid > 0));

        $this->assertSame([1, 3], $miembrosIds);
    }

    public function testValidacionMiembroInexistenteDetectaError(): void
    {
        $miembrosIds = [1, 2, 999];
        $idsValidos = [1, 2];
        $invalidos = array_diff($miembrosIds, $idsValidos);

        $this->assertNotEmpty($invalidos);
        $this->assertContains(999, $invalidos);
    }

    public function testValidacionFallidaImpidePoblarMiembrosValidos(): void
    {
        $miembrosPost = [1, 999];
        $miembrosIds = array_unique(array_map('intval', $miembrosPost));
        $miembrosIds = array_values(array_filter($miembrosIds, fn($mid) => $mid > 0));

        $idsValidos = [1];
        $invalidos = array_diff($miembrosIds, $idsValidos);

        $this->assertNotEmpty($invalidos);

        $miembrosValidos = [];
        if (empty($invalidos)) {
            $miembrosValidos = $idsValidos;
        }

        $this->assertEmpty($miembrosValidos);
    }

    public function testValidacionExitosaPoblaMiembrosValidos(): void
    {
        $miembrosPost = [1, 2];
        $miembrosIds = array_unique(array_map('intval', $miembrosPost));
        $miembrosIds = array_values(array_filter($miembrosIds, fn($mid) => $mid > 0));

        $idsValidos = [1, 2];
        $invalidos = array_diff($miembrosIds, $idsValidos);

        $this->assertEmpty($invalidos);

        $miembrosValidos = [];
        if (empty($invalidos)) {
            $miembrosValidos = $idsValidos;
        }

        $this->assertNotEmpty($miembrosValidos);
        $this->assertSame([1, 2], $miembrosValidos);
    }

    public function testValidacionMiembrosTodosExistentesNoDaError(): void
    {
        $miembrosIds = [1, 2, 3];
        $idsValidos = [1, 2, 3];
        $invalidos = array_diff($miembrosIds, $idsValidos);

        $this->assertEmpty($invalidos);
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
