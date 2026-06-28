<?php

if (!function_exists('numero_arg')) {
    function numero_arg(float|int|string|null $value, int $decimals = 2): string
    {
        return number_format((float) ($value ?? 0), $decimals, ',', '.');
    }
}

if (!function_exists('moneda')) {
    function moneda(float|int|string|null $value, bool $withSymbol = true, int $decimals = 2): string
    {
        return ($withSymbol ? '$' : '') . numero_arg($value, $decimals);
    }
}

if (!function_exists('pluralizar')) {
    function pluralizar(int $count, string $singular, string $plural): string
    {
        return $count === 1 ? $singular : $plural;
    }
}