<?php

return [
    'title' => 'Informacion y estado',
    'description' => 'Alertas para contexto filtrado, estado offline, actualizaciones disponibles y avisos de mantenimiento.',
    'screen' => 'system_alerts',
    'component' => 'alert_info',
    'selected' => $selectedAlertInfoVariant,
    'variants' => [
        require __DIR__ . '/variants/info_filter.php',
        require __DIR__ . '/variants/offline.php',
        require __DIR__ . '/variants/update_available.php',
        require __DIR__ . '/variants/maintenance.php',
    ],
];
