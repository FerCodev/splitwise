<?php

if (!function_exists('sw_alert')) {
    function sw_alert(string $type, string $title, ?string $message = null, array $options = []): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'actionText' => $options['actionText'] ?? null,
            'actionUrl' => $options['actionUrl'] ?? null,
            'dismissible' => $options['dismissible'] ?? true,
        ];
    }
}

if (!function_exists('sw_success')) {
    function sw_success(string $title, ?string $message = null, array $options = []): array
    {
        return sw_alert('success', $title, $message, $options);
    }
}

if (!function_exists('sw_error')) {
    function sw_error(string $title, ?string $message = null, array $options = []): array
    {
        return sw_alert('danger', $title, $message, $options);
    }
}

if (!function_exists('sw_warning')) {
    function sw_warning(string $title, ?string $message = null, array $options = []): array
    {
        return sw_alert('warning', $title, $message, $options);
    }
}

if (!function_exists('sw_info')) {
    function sw_info(string $title, ?string $message = null, array $options = []): array
    {
        return sw_alert('info', $title, $message, $options);
    }
}
