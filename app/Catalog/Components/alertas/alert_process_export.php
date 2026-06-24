<?php

return [
    'title' => 'Procesos y exportacion',
    'description' => 'Alertas para exportaciones listas y reportes pesados en proceso.',
    'screen' => 'system_alerts',
    'component' => 'alert_process_export',
    'selected' => $selectedAlertProcessExportVariant,
    'variants' => [
        require __DIR__ . '/variants/export_ready.php',
        require __DIR__ . '/variants/heavy_report.php',
    ],
];
