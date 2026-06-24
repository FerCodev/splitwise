<?php

return [
    'title' => 'Advertencias y prevencion',
    'description' => 'Alertas para deudas pendientes, fechas fuera de periodo, importaciones parciales, pagos duplicados y cambios sin guardar.',
    'screen' => 'system_alerts',
    'component' => 'alert_warning',
    'selected' => $selectedAlertWarningVariant,
    'variants' => [
        require __DIR__ . '/variants/warning_debt.php',
        require __DIR__ . '/variants/out_of_period_date.php',
        require __DIR__ . '/variants/import_partial.php',
        require __DIR__ . '/variants/duplicate_payment.php',
        require __DIR__ . '/variants/unsaved_changes.php',
    ],
];
