<?php

use App\Services\GroupPermission;
use CodeIgniter\Test\CIUnitTestCase;

final class PermisosGrupoTest extends CIUnitTestCase
{
    private function blocked(string $rol, string $estado, string $accion, ?int $userId = null, ?int $ownerId = null): bool
    {
        return GroupPermission::check($rol, $estado, $accion, $userId, $ownerId) !== null;
    }

    // -- Admin de grupo --

    public function testAdminPuedeEditarGrupo(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'grupo_edit'));
    }

    public function testAdminPuedeEliminarGrupo(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'grupo_delete'));
    }

    public function testAdminPuedeCambiarEstado(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'grupo_estado'));
    }

    public function testAdminPuedeAgregarMiembro(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'miembro_create'));
    }

    public function testAdminPuedeQuitarMiembro(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'miembro_delete'));
    }

    public function testAdminPuedeCambiarRol(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'miembro_role'));
    }

    public function testAdminPuedeCrearGasto(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'gasto_create'));
    }

    public function testAdminPuedeEditarGastoAjeno(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'gasto_edit', 1, 2));
    }

    public function testAdminPuedeEliminarGastoAjeno(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'gasto_delete', 1, 2));
    }

    public function testAdminPuedeCrearPago(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'pago_create'));
    }

    public function testAdminPuedeEditarPagoAjeno(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'pago_edit', 1, 2));
    }

    public function testAdminPuedeEliminarPagoAjeno(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'pago_delete', 1, 2));
    }

    // -- Member de grupo --

    public function testMemberNoPuedeEditarGrupo(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'grupo_edit'));
    }

    public function testMemberNoPuedeEliminarGrupo(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'grupo_delete'));
    }

    public function testMemberNoPuedeCambiarEstado(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'grupo_estado'));
    }

    public function testMemberNoPuedeAgregarMiembro(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'miembro_create'));
    }

    public function testMemberNoPuedeQuitarMiembro(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'miembro_delete'));
    }

    public function testMemberNoPuedeCambiarRol(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'miembro_role'));
    }

    public function testMemberPuedeCrearGasto(): void
    {
        $this->assertFalse($this->blocked('member', 'activo', 'gasto_create'));
    }

    public function testMemberPuedeEditarGastoPropio(): void
    {
        $this->assertFalse($this->blocked('member', 'activo', 'gasto_edit', 1, 1));
    }

    public function testMemberNoPuedeEditarGastoAjeno(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'gasto_edit', 1, 2));
    }

    public function testMemberPuedeEliminarGastoPropio(): void
    {
        $this->assertFalse($this->blocked('member', 'activo', 'gasto_delete', 1, 1));
    }

    public function testMemberNoPuedeEliminarGastoAjeno(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'gasto_delete', 1, 2));
    }

    public function testMemberPuedeCrearPago(): void
    {
        $this->assertFalse($this->blocked('member', 'activo', 'pago_create'));
    }

    public function testMemberPuedeEditarPagoPropio(): void
    {
        $this->assertFalse($this->blocked('member', 'activo', 'pago_edit', 1, 1));
    }

    public function testMemberNoPuedeEditarPagoAjeno(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'pago_edit', 1, 2));
    }

    public function testMemberPuedeEliminarPagoPropio(): void
    {
        $this->assertFalse($this->blocked('member', 'activo', 'pago_delete', 1, 1));
    }

    public function testMemberNoPuedeEliminarPagoAjeno(): void
    {
        $this->assertTrue($this->blocked('member', 'activo', 'pago_delete', 1, 2));
    }

    // -- Estados de grupo (cerrado/liquidado) --

    public function testGrupoCerradoBloqueaCrearGasto(): void
    {
        $this->assertTrue($this->blocked('admin', 'cerrado', 'gasto_create'));
    }

    public function testGrupoCerradoBloqueaCrearPago(): void
    {
        $this->assertTrue($this->blocked('admin', 'cerrado', 'pago_create'));
    }

    public function testGrupoCerradoBloqueaEditarGasto(): void
    {
        $this->assertTrue($this->blocked('admin', 'cerrado', 'gasto_edit'));
    }

    public function testGrupoLiquidadoBloqueaTodo(): void
    {
        $this->assertTrue($this->blocked('admin', 'liquidado', 'gasto_create'));
        $this->assertTrue($this->blocked('admin', 'liquidado', 'pago_create'));
        $this->assertTrue($this->blocked('admin', 'liquidado', 'gasto_edit'));
        $this->assertTrue($this->blocked('admin', 'liquidado', 'miembro_create'));
    }

    // -- Rol global no hereda permisos de grupo --

    public function testNoMiembroNoTienePermisos(): void
    {
        $this->assertTrue($this->blocked('', 'activo', 'grupo_edit'));
        $this->assertTrue($this->blocked('', 'activo', 'gasto_create'));
        $this->assertTrue($this->blocked('', 'activo', 'miembro_create'));
    }

    // -- Accion desconocida --

    public function testAccionDesconocidaBloqueada(): void
    {
        $this->assertTrue($this->blocked('admin', 'activo', 'accion_inventada'));
        $this->assertTrue($this->blocked('member', 'activo', 'otra_accion'));
    }

    // -- getAll retorna array completo --

    public function testGetAllAdminActivo(): void
    {
        $p = GroupPermission::getAll('admin', 'activo', 1);
        $this->assertTrue($p['puede_editar_grupo']);
        $this->assertTrue($p['puede_eliminar_grupo']);
        $this->assertTrue($p['puede_cambiar_estado']);
        $this->assertTrue($p['puede_agregar_miembro']);
        $this->assertTrue($p['puede_quitar_miembro']);
        $this->assertTrue($p['puede_cambiar_rol']);
        $this->assertTrue($p['puede_crear_gasto']);
        $this->assertTrue($p['puede_crear_pago']);
    }

    public function testGetAllMemberActivo(): void
    {
        $p = GroupPermission::getAll('member', 'activo', 1, 1);
        $this->assertFalse($p['puede_editar_grupo']);
        $this->assertFalse($p['puede_eliminar_grupo']);
        $this->assertFalse($p['puede_cambiar_estado']);
        $this->assertFalse($p['puede_agregar_miembro']);
        $this->assertFalse($p['puede_quitar_miembro']);
        $this->assertFalse($p['puede_cambiar_rol']);
        $this->assertTrue($p['puede_crear_gasto']);
        $this->assertTrue($p['puede_crear_pago']);
        $this->assertTrue($p['puede_editar_gasto']);
        $this->assertTrue($p['puede_eliminar_gasto']);
    }

    public function testGetAllMemberGastoAjeno(): void
    {
        $p = GroupPermission::getAll('member', 'activo', 1, 2);
        $this->assertFalse($p['puede_editar_gasto']);
        $this->assertFalse($p['puede_eliminar_gasto']);
    }

    // -- Tests para regla de negocio: member no puede cambiar pagador_id --
    // Estas pruebas verifican que GroupPermission se comporte correctamente
    // respecto al ownerId. Las pruebas de integracion (controlador) validan
    // que el controlador fuerce pagador_id al existente para members.

    public function testMemberPuedeEditarGastoPropioPassandoSuPropioOwnerId(): void
    {
        $this->assertFalse($this->blocked('member', 'activo', 'gasto_edit', 5, 5));
    }

    public function testAdminPuedeEditarGastoDeOtroCambiandoOwnerId(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'gasto_edit', 1, 99));
    }

    public function testMemberPuedeEditarPagoPropioPassandoSuPropioOwnerId(): void
    {
        $this->assertFalse($this->blocked('member', 'activo', 'pago_edit', 5, 5));
    }

    public function testAdminPuedeEditarPagoDeOtroCambiandoOwnerId(): void
    {
        $this->assertFalse($this->blocked('admin', 'activo', 'pago_edit', 1, 99));
    }

    // -- Prueba que getAll incluye permisos de pago --

    public function testGetAllConPagoPropio(): void
    {
        $p = GroupPermission::getAll('member', 'activo', 1, null, 1);
        $this->assertTrue($p['puede_editar_pago']);
        $this->assertTrue($p['puede_eliminar_pago']);
    }

    public function testGetAllConPagoAjeno(): void
    {
        $p = GroupPermission::getAll('member', 'activo', 1, null, 2);
        $this->assertFalse($p['puede_editar_pago']);
        $this->assertFalse($p['puede_eliminar_pago']);
    }

    // -- Mensajes en UTF-8 (sin entidades HTML) --

    public function testMensajeNoMiembroEsUtf8(): void
    {
        $msg = GroupPermission::check('', 'activo', 'grupo_edit');
        $this->assertStringContainsString('tenés', $msg);
        $this->assertStringNotContainsString('&eacute;', $msg);
        $this->assertStringNotContainsString('&oacute;', $msg);
        $this->assertStringNotContainsString('&aacute;', $msg);
    }

    public function testMensajeAdminOnlyEsUtf8(): void
    {
        $msg = GroupPermission::check('member', 'activo', 'grupo_edit');
        $this->assertStringContainsString('acción', $msg);
        $this->assertStringNotContainsString('&oacute;', $msg);
    }

    public function testMensajeGastoEditMemberEsUtf8(): void
    {
        $msg = GroupPermission::check('member', 'activo', 'gasto_edit', 1, 2);
        $this->assertStringContainsString('pagó', $msg);
        $this->assertStringNotContainsString('&oacute;', $msg);
    }

    public function testMensajePagoEditMemberEsUtf8(): void
    {
        $msg = GroupPermission::check('member', 'activo', 'pago_edit', 1, 2);
        $this->assertStringContainsString('realizó', $msg);
        $this->assertStringNotContainsString('&oacute;', $msg);
    }

    public function testMensajeAccionDesconocidaEsUtf8(): void
    {
        $msg = GroupPermission::check('admin', 'activo', 'accion_inventada');
        $this->assertStringContainsString('Acción', $msg);
        $this->assertStringNotContainsString('&oacute;', $msg);
    }

    public function testNingunMensajeContieneEntidadesHtml(): void
    {
        $casos = [
            GroupPermission::check('', 'activo', 'grupo_edit'),
            GroupPermission::check('member', 'activo', 'grupo_edit'),
            GroupPermission::check('member', 'activo', 'grupo_delete'),
            GroupPermission::check('member', 'activo', 'grupo_estado'),
            GroupPermission::check('member', 'activo', 'miembro_create'),
            GroupPermission::check('member', 'activo', 'miembro_delete'),
            GroupPermission::check('member', 'activo', 'miembro_role'),
            GroupPermission::check('member', 'activo', 'gasto_edit', 1, 2),
            GroupPermission::check('member', 'activo', 'gasto_delete', 1, 2),
            GroupPermission::check('member', 'activo', 'pago_edit', 1, 2),
            GroupPermission::check('member', 'activo', 'pago_delete', 1, 2),
            GroupPermission::check('admin', 'activo', 'accion_inventada'),
        ];
        foreach ($casos as $msg) {
            $this->assertNotNull($msg);
            $this->assertStringNotContainsString('&eacute;', $msg);
            $this->assertStringNotContainsString('&oacute;', $msg);
            $this->assertStringNotContainsString('&aacute;', $msg);
            $this->assertStringNotContainsString('&iacute;', $msg);
            $this->assertStringNotContainsString('&ntilde;', $msg);
        }
    }

    // -- Miembro comun sigue sin poder ejecutar acciones administrativas --

    public function testMemberBloqueadoEnTodosLosAdminActions(): void
    {
        $adminActions = ['grupo_edit', 'grupo_delete', 'grupo_estado', 'miembro_create', 'miembro_delete', 'miembro_role'];
        foreach ($adminActions as $accion) {
            $this->assertTrue($this->blocked('member', 'activo', $accion), "Member deberia estar bloqueado para {$accion}");
        }
    }

    public function testAdminConservaTodosLosPermisos(): void
    {
        $adminActions = ['grupo_edit', 'grupo_delete', 'grupo_estado', 'miembro_create', 'miembro_delete', 'miembro_role', 'gasto_create', 'gasto_edit', 'gasto_delete', 'pago_create', 'pago_edit', 'pago_delete'];
        foreach ($adminActions as $accion) {
            $this->assertFalse($this->blocked('admin', 'activo', $accion, 1, 2), "Admin deberia poder {$accion}");
        }
    }
}
