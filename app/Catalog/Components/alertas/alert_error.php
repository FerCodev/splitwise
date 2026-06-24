<?php

return [
    'title' => 'Errores y validacion',
    'description' => 'Alertas para errores recuperables, montos invalidos y datos faltantes.',
    'screen' => 'system_alerts',
    'component' => 'alert_error',
    'selected' => $selectedAlertErrorVariant,
    'variants' => [
        require __DIR__ . '/variants/error_action.php',
        require __DIR__ . '/variants/amount_validation.php',
        require __DIR__ . '/variants/incomplete_payment_method.php',
    ],
];
