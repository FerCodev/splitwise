<?php

namespace Config;

class UiFeedback
{
    public const SLOT_DEFAULTS = [
        'feedback.success' => [
            'component' => 'alert_success',
            'variant' => 'success_compact',
        ],
        'feedback.error' => [
            'component' => 'alert_error',
            'variant' => 'error_action',
        ],
        'feedback.warning' => [
            'component' => 'alert_warning',
            'variant' => 'warning_debt',
        ],
        'feedback.info' => [
            'component' => 'alert_info',
            'variant' => 'info_filter',
        ],
        'confirmation.destructive' => [
            'component' => '_confirm_modal',
            'variant' => 'danger',
        ],
        'confirmation.warning' => [
            'component' => '_confirm_modal',
            'variant' => 'warning',
        ],
    ];

    public const ACTION_MAP = [
        'auth.login.failed' => [
            'slot' => 'feedback.error',
            'component' => 'alert_error',
            'variant' => 'error_action',
            'template' => 'Email o contraseña incorrectos.',
            'params' => [],
            'active' => true,
        ],
        'profile.update.completed' => [
            'slot' => 'feedback.success',
            'component' => 'alert_success',
            'variant' => 'settled',
            'template' => 'Perfil actualizado correctamente.',
            'params' => [],
            'active' => true,
        ],
        'profile.update.failed' => [
            'slot' => 'feedback.error',
            'component' => 'alert_error',
            'variant' => 'error_action',
            'template' => 'Error al actualizar el perfil. {reason}',
            'params' => ['reason'],
            'active' => true,
        ],
        'profile.password.change.completed' => [
            'slot' => 'feedback.success',
            'component' => 'alert_success',
            'variant' => 'synced',
            'template' => 'Contraseña actualizada correctamente.',
            'params' => [],
            'active' => true,
        ],
        'profile.password.change.failed' => [
            'slot' => 'feedback.error',
            'component' => 'alert_error',
            'variant' => 'error_action',
            'template' => 'No se pudo cambiar la contraseña. {reason}',
            'params' => ['reason'],
            'active' => true,
        ],
    ];
}
