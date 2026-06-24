<?php

namespace App\Services;

use Config\UiFeedback;

class UiFeedbackResolver
{
    public static function resolve(string $actionKey, array $params = []): ?array
    {
        $config = UiFeedback::ACTION_MAP[$actionKey] ?? null;

        if ($config === null) {
            return null;
        }

        if (($config['active'] ?? true) === false) {
            return null;
        }

        $slotKey = $config['slot'];
        $slotDefaults = UiFeedback::SLOT_DEFAULTS[$slotKey] ?? [];

        $component = $config['component'] ?? $slotDefaults['component'] ?? null;
        $variant = $config['variant'] ?? $slotDefaults['variant'] ?? null;
        $template = $config['template'] ?? null;

        if ($component === null || $variant === null) {
            return null;
        }

        return [
            'slot' => $slotKey,
            'component' => $component,
            'variant' => $variant,
            'template' => $template,
            'params' => $config['params'] ?? [],
            'active' => true,
        ];
    }

    public static function message(string $actionKey, array $params = [], ?string $fallback = null): ?string
    {
        $resolved = self::resolve($actionKey, $params);

        if ($resolved === null) {
            return $fallback;
        }

        $template = $resolved['template'];

        if ($template === null) {
            return $fallback;
        }

        $expectedParams = $resolved['params'];

        if (empty($expectedParams)) {
            return $template;
        }

        $replacements = [];

        foreach ($expectedParams as $param) {
            $value = $params[$param] ?? '';
            $replacements['{' . $param . '}'] = $value;
        }

        return strtr($template, $replacements);
    }
}
