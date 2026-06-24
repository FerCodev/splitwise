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
            'detail' => 'Balance detallado',
            'compare' => 'Comparativo',
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
            'segmented_arc' => 'Arco por tramos',
            'segmented_donut' => 'Dona segmentada',
            'clean_arc' => 'Medialuna limpia',
        ],
        self::COMPONENT_GROUP_MOVEMENT_CARD => [
            'feed' => 'Feed',
            'user_color' => 'Por usuario',
            'compact' => 'Compacto',
        ],
        self::COMPONENT_SYSTEM_ALERT_CARD => [
            'success_compact' => 'Exito compacto',
            'error_action' => 'Error con acci&oacute;n',
            'warning_debt' => 'Warning deuda',
            'info_filter' => 'Info filtro',
            'settled' => 'Liquidado',
            'export_ready' => 'Exportacion lista',
            'offline' => 'Conexion',
            'delete_confirmation' => 'Confirmacion borrar',
            'suggested_payment' => 'Pago sugerido',
            'update_available' => 'Actualizacion disponible',
            'amount_validation' => 'Validacion monto',
            'out_of_period_date' => 'Fecha fuera de periodo',
            'admin_permission' => 'Permiso admin',
            'favorite_updated' => 'Favorito actualizado',
            'import_ready' => 'Importacion lista',
            'import_partial' => 'Importacion parcial',
            'settlement_recommendation' => 'Liquidacion recomendada',
            'empty_group' => 'Grupo sin movimientos',
            'empty_filter' => 'Filtro sin resultados',
            'duplicate_payment' => 'Pago duplicado',
            'session_expiring' => 'Sesion por vencer',
            'unsaved_changes' => 'Cambios sin guardar',
            'heavy_report' => 'Reporte pesado',
            'member_added' => 'Nuevo integrante',
            'member_removed' => 'Integrante removido',
            'balance_recalculated' => 'Balance recalculado',
            'incomplete_payment_method' => 'Medio incompleto',
            'backup_created' => 'Backup creado',
            'synced' => 'Sincronizado',
            'maintenance' => 'Mantenimiento',
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
    public const COMPONENT_GROUP_GAUGE = 'group_gauge';
    public const COMPONENT_GROUP_MOVEMENT_CARD = 'group_movement_card';
    public const COMPONENT_SYSTEM_ALERT_CARD = 'system_alert_card';

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
            self::COMPONENT_GROUP_GAUGE => 'semicircle',
            self::COMPONENT_GROUP_MOVEMENT_CARD => 'feed',
        ],
        self::SCREEN_SYSTEM_ALERTS => [
            self::COMPONENT_SYSTEM_ALERT_CARD => 'success_compact',
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
                'detail',
                'compare',
            ],
        ],
        self::SCREEN_PAYMENTS_INDEX => [
            self::COMPONENT_FILTERED_TOTAL_CARD => [
                'simple',
                'detail',
                'compare',
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
            self::COMPONENT_GROUP_GAUGE => [
                'semicircle',
                'compact_dial',
                'scale_bar',
                'segmented_arc',
                'segmented_donut',
                'clean_arc',
            ],
            self::COMPONENT_GROUP_MOVEMENT_CARD => [
                'feed',
                'user_color',
                'compact',
            ],
        ],
        self::SCREEN_SYSTEM_ALERTS => [
            self::COMPONENT_SYSTEM_ALERT_CARD => [
                'success_compact',
                'error_action',
                'warning_debt',
                'info_filter',
                'settled',
                'export_ready',
                'offline',
                'delete_confirmation',
                'suggested_payment',
                'update_available',
                'amount_validation',
                'out_of_period_date',
                'admin_permission',
                'favorite_updated',
                'import_ready',
                'import_partial',
                'settlement_recommendation',
                'empty_group',
                'empty_filter',
                'duplicate_payment',
                'session_expiring',
                'unsaved_changes',
                'heavy_report',
                'member_added',
                'member_removed',
                'balance_recalculated',
                'incomplete_payment_method',
                'backup_created',
                'synced',
                'maintenance',
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
