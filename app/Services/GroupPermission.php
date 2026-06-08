<?php

namespace App\Services;

class GroupPermission
{
    /**
     * Retorna mensaje de error si el usuario no tiene permiso para la accion,
     * o null si esta permitida.
     */
    public static function check(string $rol, string $estado, string $accion, ?int $userId = null, ?int $ownerId = null): ?string
    {
        if ($rol === '') {
            return 'No ten&eacute;s acceso a este grupo.';
        }

        $bloqueoEstado = \App\Models\Grupo::restriccionEstado($estado, $accion);
        if ($bloqueoEstado !== null) {
            return $bloqueoEstado;
        }

        $adminOnly = [
            'grupo_edit',
            'grupo_delete',
            'grupo_estado',
            'miembro_create',
            'miembro_delete',
            'miembro_role',
        ];

        if (in_array($accion, $adminOnly, true)) {
            return $rol === 'admin' ? null : 'Solo los administradores del grupo pueden realizar esta acci&oacute;n.';
        }

        if ($accion === 'gasto_create' || $accion === 'pago_create') {
            return null;
        }

        if ($accion === 'gasto_edit' || $accion === 'gasto_delete') {
            if ($rol === 'admin') {
                return null;
            }
            if ($userId !== null && $ownerId !== null && $userId === $ownerId) {
                return null;
            }
            $accionLabel = $accion === 'gasto_edit' ? 'editar' : 'eliminar';
            return "Solo el administrador del grupo o quien pag&oacute; el gasto puede {$accionLabel}lo.";
        }

        if ($accion === 'pago_edit' || $accion === 'pago_delete') {
            if ($rol === 'admin') {
                return null;
            }
            if ($userId !== null && $ownerId !== null && $userId === $ownerId) {
                return null;
            }
            $accionLabel = $accion === 'pago_edit' ? 'editar' : 'eliminar';
            return "Solo el administrador del grupo o quien realiz&oacute; el pago puede {$accionLabel}lo.";
        }

        return 'Acci&oacute;n no permitida.';
    }

    /**
     * Retorna array plano de permisos booleanos para la UI.
     */
    public static function getAll(string $rol, string $estado, int $userId, ?int $gastoPagadorId = null, ?int $pagoPagadorId = null): array
    {
        return [
            'puede_editar_grupo'       => self::check($rol, $estado, 'grupo_edit') === null,
            'puede_eliminar_grupo'     => self::check($rol, $estado, 'grupo_delete') === null,
            'puede_cambiar_estado'     => self::check($rol, $estado, 'grupo_estado') === null,
            'puede_agregar_miembro'    => self::check($rol, $estado, 'miembro_create') === null,
            'puede_quitar_miembro'     => self::check($rol, $estado, 'miembro_delete') === null,
            'puede_cambiar_rol'        => self::check($rol, $estado, 'miembro_role') === null,
            'puede_crear_gasto'        => self::check($rol, $estado, 'gasto_create') === null,
            'puede_editar_gasto'       => self::check($rol, $estado, 'gasto_edit', $userId, $gastoPagadorId) === null,
            'puede_eliminar_gasto'     => self::check($rol, $estado, 'gasto_delete', $userId, $gastoPagadorId) === null,
            'puede_crear_pago'         => self::check($rol, $estado, 'pago_create') === null,
            'puede_editar_pago'        => self::check($rol, $estado, 'pago_edit', $userId, $pagoPagadorId) === null,
            'puede_eliminar_pago'      => self::check($rol, $estado, 'pago_delete', $userId, $pagoPagadorId) === null,
        ];
    }
}
