<?php

namespace App\Services;

use App\Models\UiComponentPreference;
use Throwable;

class UiComponentResolver
{
    private const VARIANT_NAMES = [
        self::COMPONENT_HOME_GROUP_CARD => [
            'operational' => 'Operativa',
            'balance_first' => 'Balance primero',
            'compact' => 'Resumen compacto',
            'action_large' => 'Acci&oacute;n amplia',
            'activity_split' => 'Actividad separada',
            'minimal_panel' => 'Panel simple',
        ],
        self::COMPONENT_DEBT_CARD => [
            'soft' => 'Alerta suave',
            'direct_action' => 'Acci&oacute;n directa',
            'person_summary' => 'Resumen persona',
        ],
        self::COMPONENT_FILTERED_TOTAL_CARD => [
            'simple' => 'KPI simple',
        ],
        self::COMPONENT_PAYMENT_METHOD_CARD => [
            'bank_card' => 'Credencial actual',
            'compact' => 'Compacta operativa',
            'favorite' => 'Favorita destacada',
        ],
        self::COMPONENT_GROUP_GAUGE => [
            'semicircle' => 'Actual',
            'compact_dial' => 'Dial compacto',
            'scale_bar' => 'Barra de escala',
            'segmented_donut' => 'Dona segmentada',
        ],
        self::COMPONENT_GROUP_BALANCE_CARD => [
            'status_pill' => 'Estado con pill',
        ],
        self::COMPONENT_GROUP_MOVEMENT_CARD => [
            'feed' => 'Feed',
            'user_color' => 'Por usuario',
            'compact' => 'Compacto',
        ],
        self::COMPONENT_ALERT_SUCCESS => [
            'success_compact' => 'Exito compacto',
            'settled' => 'Liquidado',
            'favorite_updated' => 'Favorito actualizado',
            'import_ready' => 'Importacion lista',
            'backup_created' => 'Backup creado',
            'synced' => 'Sincronizado',
            'member_added' => 'Nuevo integrante',
        ],
        self::COMPONENT_ALERT_ERROR => [
            'error_action' => 'Error con acci&oacute;n',
            'amount_validation' => 'Validacion monto',
            'incomplete_payment_method' => 'Medio incompleto',
        ],
        self::COMPONENT_ALERT_WARNING => [
            'warning_debt' => 'Warning deuda',
            'out_of_period_date' => 'Fecha fuera de periodo',
            'import_partial' => 'Importacion parcial',
            'duplicate_payment' => 'Pago duplicado',
            'unsaved_changes' => 'Cambios sin guardar',
        ],
        self::COMPONENT_ALERT_INFO => [
            'info_filter' => 'Info filtro',
            'offline' => 'Conexion',
            'update_available' => 'Actualizacion disponible',
            'maintenance' => 'Mantenimiento',
        ],
        self::COMPONENT_ALERT_DESTRUCTIVE_CONFIRMATION => [
            'delete_confirmation' => 'Confirmacion borrar',
            'member_removed' => 'Integrante removido',
        ],
        self::COMPONENT_ALERT_EMPTY_STATE => [
            'empty_group' => 'Grupo sin movimientos',
            'empty_filter' => 'Filtro sin resultados',
        ],
        self::COMPONENT_ALERT_SECURITY_SESSION => [
            'admin_permission' => 'Permiso admin',
            'session_expiring' => 'Sesion por vencer',
        ],
        self::COMPONENT_ALERT_PROCESS_EXPORT => [
            'export_ready' => 'Exportacion lista',
            'heavy_report' => 'Reporte pesado',
        ],
        self::COMPONENT_ALERT_PAYMENT_SUGGESTION => [
            'suggested_payment' => 'Pago sugerido',
            'settlement_recommendation' => 'Liquidacion recomendada',
        ],
        self::COMPONENT_ALERT_GROUP_EVENT => [
            'balance_recalculated' => 'Balance recalculado',
            'member_added' => 'Nuevo integrante',
            'member_removed' => 'Integrante removido',
        ],
    ];

    public const SCREEN_HOME = 'home';
    public const SCREEN_EXPENSES_INDEX = 'gastos_index';
    public const SCREEN_PAYMENTS_INDEX = 'pagos_index';
    public const SCREEN_PAYMENT_METHODS = 'mis_medios_cobro';
    public const SCREEN_GROUP_SHOW = 'grupo_show';
    public const SCREEN_SYSTEM_ALERTS = 'system_alerts';
    public const COMPONENT_DEBT_CARD = 'debt_card';
    public const COMPONENT_HOME_GROUP_CARD = 'home_group_card';
    public const COMPONENT_FILTERED_TOTAL_CARD = 'filtered_total_card';
    public const COMPONENT_PAYMENT_METHOD_CARD = 'payment_method_card';
    public const COMPONENT_GROUP_BALANCE_CARD = 'group_balance_card';
    public const COMPONENT_GROUP_GAUGE = 'group_gauge';
    public const COMPONENT_GROUP_MOVEMENT_CARD = 'group_movement_card';
    public const COMPONENT_ALERT_SUCCESS = 'alert_success';
    public const COMPONENT_ALERT_ERROR = 'alert_error';
    public const COMPONENT_ALERT_WARNING = 'alert_warning';
    public const COMPONENT_ALERT_INFO = 'alert_info';
    public const COMPONENT_ALERT_DESTRUCTIVE_CONFIRMATION = 'alert_destructive_confirmation';
    public const COMPONENT_ALERT_EMPTY_STATE = 'alert_empty_state';
    public const COMPONENT_ALERT_SECURITY_SESSION = 'alert_security_session';
    public const COMPONENT_ALERT_PROCESS_EXPORT = 'alert_process_export';
    public const COMPONENT_ALERT_PAYMENT_SUGGESTION = 'alert_payment_suggestion';
    public const COMPONENT_ALERT_GROUP_EVENT = 'alert_group_event';

    private const DEFAULT_VARIANTS = [
        self::SCREEN_HOME => [
            self::COMPONENT_DEBT_CARD => 'soft',
            self::COMPONENT_HOME_GROUP_CARD => 'operational',
        ],
        self::SCREEN_EXPENSES_INDEX => [
            self::COMPONENT_FILTERED_TOTAL_CARD => 'simple',
        ],
        self::SCREEN_PAYMENTS_INDEX => [
            self::COMPONENT_FILTERED_TOTAL_CARD => 'simple',
        ],
        self::SCREEN_PAYMENT_METHODS => [
            self::COMPONENT_PAYMENT_METHOD_CARD => 'bank_card',
        ],
        self::SCREEN_GROUP_SHOW => [
            self::COMPONENT_GROUP_BALANCE_CARD => 'status_pill',
            self::COMPONENT_GROUP_GAUGE => 'semicircle',
            self::COMPONENT_GROUP_MOVEMENT_CARD => 'feed',
        ],
        self::SCREEN_SYSTEM_ALERTS => [
            self::COMPONENT_ALERT_SUCCESS => 'success_compact',
            self::COMPONENT_ALERT_ERROR => 'error_action',
            self::COMPONENT_ALERT_WARNING => 'warning_debt',
            self::COMPONENT_ALERT_INFO => 'info_filter',
            self::COMPONENT_ALERT_DESTRUCTIVE_CONFIRMATION => 'delete_confirmation',
            self::COMPONENT_ALERT_EMPTY_STATE => 'empty_group',
            self::COMPONENT_ALERT_SECURITY_SESSION => 'admin_permission',
            self::COMPONENT_ALERT_PROCESS_EXPORT => 'export_ready',
            self::COMPONENT_ALERT_PAYMENT_SUGGESTION => 'suggested_payment',
            self::COMPONENT_ALERT_GROUP_EVENT => 'balance_recalculated',
        ],
    ];

    private const ALLOWED_VARIANTS = [
        self::SCREEN_HOME => [
            self::COMPONENT_DEBT_CARD => [
                'soft',
                'direct_action',
                'person_summary',
            ],
            self::COMPONENT_HOME_GROUP_CARD => [
                'operational',
                'balance_first',
                'compact',
                'action_large',
                'activity_split',
                'minimal_panel',
            ],
        ],
        self::SCREEN_EXPENSES_INDEX => [
            self::COMPONENT_FILTERED_TOTAL_CARD => [
                'simple',
            ],
        ],
        self::SCREEN_PAYMENTS_INDEX => [
            self::COMPONENT_FILTERED_TOTAL_CARD => [
                'simple',
            ],
        ],
        self::SCREEN_PAYMENT_METHODS => [
            self::COMPONENT_PAYMENT_METHOD_CARD => [
                'bank_card',
                'compact',
                'favorite',
            ],
        ],
        self::SCREEN_GROUP_SHOW => [
            self::COMPONENT_GROUP_BALANCE_CARD => [
                'status_pill',
            ],
            self::COMPONENT_GROUP_GAUGE => [
                'semicircle',
                'compact_dial',
                'scale_bar',
                'segmented_donut',
            ],
            self::COMPONENT_GROUP_MOVEMENT_CARD => [
                'feed',
                'user_color',
                'compact',
            ],
        ],
        self::SCREEN_SYSTEM_ALERTS => [
            self::COMPONENT_ALERT_SUCCESS => [
                'success_compact',
                'settled',
                'favorite_updated',
                'import_ready',
                'backup_created',
                'synced',
                'member_added',
            ],
            self::COMPONENT_ALERT_ERROR => [
                'error_action',
                'amount_validation',
                'incomplete_payment_method',
            ],
            self::COMPONENT_ALERT_WARNING => [
                'warning_debt',
                'out_of_period_date',
                'import_partial',
                'duplicate_payment',
                'unsaved_changes',
            ],
            self::COMPONENT_ALERT_INFO => [
                'info_filter',
                'offline',
                'update_available',
                'maintenance',
            ],
            self::COMPONENT_ALERT_DESTRUCTIVE_CONFIRMATION => [
                'delete_confirmation',
                'member_removed',
            ],
            self::COMPONENT_ALERT_EMPTY_STATE => [
                'empty_group',
                'empty_filter',
            ],
            self::COMPONENT_ALERT_SECURITY_SESSION => [
                'admin_permission',
                'session_expiring',
            ],
            self::COMPONENT_ALERT_PROCESS_EXPORT => [
                'export_ready',
                'heavy_report',
            ],
            self::COMPONENT_ALERT_PAYMENT_SUGGESTION => [
                'suggested_payment',
                'settlement_recommendation',
            ],
            self::COMPONENT_ALERT_GROUP_EVENT => [
                'balance_recalculated',
                'member_added',
                'member_removed',
            ],
        ],
    ];

    public static function variant(string $screenKey, string $componentKey): string
    {
        $default = self::defaultVariant($screenKey, $componentKey);

        try {
            $variant = model(UiComponentPreference::class)->getVariant($screenKey, $componentKey);
        } catch (Throwable) {
            return $default;
        }

        if (!$variant || !self::isAllowed($screenKey, $componentKey, $variant)) {
            return $default;
        }

        return $variant;
    }

    public static function setVariant(string $screenKey, string $componentKey, string $variantKey): bool
    {
        if (!self::isAllowed($screenKey, $componentKey, $variantKey)) {
            return false;
        }

        model(UiComponentPreference::class)->setVariant($screenKey, $componentKey, $variantKey);
        return true;
    }

    public static function defaultVariant(string $screenKey, string $componentKey): string
    {
        return self::DEFAULT_VARIANTS[$screenKey][$componentKey] ?? 'default';
    }

    public static function isSelected(string $screenKey, string $componentKey, string $variantKey): bool
    {
        return self::variant($screenKey, $componentKey) === $variantKey;
    }

    public static function isAllowed(string $screenKey, string $componentKey, string $variantKey): bool
    {
        return in_array($variantKey, self::ALLOWED_VARIANTS[$screenKey][$componentKey] ?? [], true);
    }

    public static function designId(string $scope, string $group, string $name, ?string $variant = null): string
    {
        $raw = strtolower($scope . '-' . $group . '-' . $name . '-' . (string) $variant);
        $raw = preg_replace('/[^a-z0-9]+/', '-', $raw) ?? $raw;
        return trim($raw, '-');
    }

    public static function activeDesignIds(): array
    {
        $ids = [];

        try {
            $preferences = model(UiComponentPreference::class)->findAll();
        } catch (Throwable) {
            $preferences = [];
        }

        $prefMap = [];

        foreach ($preferences as $pref) {
            $key = $pref['screen_key'] . '::' . $pref['component_key'];
            $prefMap[$key] = $pref['variant_key'];
        }

        foreach (self::ALLOWED_VARIANTS as $screenKey => $components) {
            foreach ($components as $componentKey => $allowed) {
                $mapKey = $screenKey . '::' . $componentKey;
                $variantKey = $prefMap[$mapKey] ?? self::DEFAULT_VARIANTS[$screenKey][$componentKey] ?? null;

                if ($variantKey === null || $variantKey === '') {
                    continue;
                }

                if (!in_array($variantKey, $allowed, true)) {
                    $variantKey = self::DEFAULT_VARIANTS[$screenKey][$componentKey] ?? null;

                    if ($variantKey === null) {
                        continue;
                    }
                }

                $variantName = self::VARIANT_NAMES[$componentKey][$variantKey] ?? null;

                if ($variantName === null) {
                    continue;
                }

                $ids[] = self::designId('componente', $componentKey, $variantName, $variantKey);
            }
        }

        return array_unique($ids);
    }
}
