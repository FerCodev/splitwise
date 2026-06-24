<?php

return [
    'title' => 'Exito y confirmacion',
    'description' => 'Alertas para operaciones exitosas, elementos liquidados, importaciones completas y eventos positivos.',
    'screen' => 'system_alerts',
    'component' => 'alert_success',
    'selected' => $selectedAlertSuccessVariant,
    'variants' => [
        require __DIR__ . '/variants/success_compact.php',
        require __DIR__ . '/variants/settled.php',
        require __DIR__ . '/variants/favorite_updated.php',
        require __DIR__ . '/variants/import_ready.php',
        require __DIR__ . '/variants/backup_created.php',
        require __DIR__ . '/variants/synced.php',
        require __DIR__ . '/variants/member_added.php',
    ],
];
