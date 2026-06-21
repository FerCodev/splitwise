<?php

namespace App\Services;

use App\Models\UiComponentPreference;
use Throwable;

class UiComponentResolver
{
    public const SCREEN_HOME = 'home';
    public const SCREEN_EXPENSES_INDEX = 'gastos_index';
    public const SCREEN_PAYMENTS_INDEX = 'pagos_index';
    public const SCREEN_PAYMENT_METHODS = 'mis_medios_cobro';
    public const SCREEN_GROUP_SHOW = 'grupo_show';
    public const COMPONENT_DEBT_CARD = 'debt_card';
    public const COMPONENT_HOME_GROUP_CARD = 'home_group_card';
    public const COMPONENT_FILTERED_TOTAL_CARD = 'filtered_total_card';
    public const COMPONENT_PAYMENT_METHOD_CARD = 'payment_method_card';
    public const COMPONENT_GROUP_GAUGE = 'group_gauge';
    public const COMPONENT_GROUP_MOVEMENT_CARD = 'group_movement_card';

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
                'milestone_ring',
            ],
            self::COMPONENT_GROUP_MOVEMENT_CARD => [
                'feed',
                'user_color',
                'compact',
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
}


