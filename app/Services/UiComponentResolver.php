<?php

namespace App\Services;

use App\Models\UiComponentPreference;
use Throwable;

class UiComponentResolver
{
    public const SCREEN_GROUP_SHOW = 'grupo_show';
    public const COMPONENT_GROUP_GAUGE = 'group_gauge';

    private const DEFAULT_VARIANTS = [
        self::SCREEN_GROUP_SHOW => [
            self::COMPONENT_GROUP_GAUGE => 'semicircle',
        ],
    ];

    private const ALLOWED_VARIANTS = [
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
