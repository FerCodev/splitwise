<?php

return [
    'title' => 'Estados vacios',
    'description' => 'Alertas para cuando no hay movimientos en un grupo o filtros sin resultados.',
    'screen' => 'system_alerts',
    'component' => 'alert_empty_state',
    'selected' => $selectedAlertEmptyStateVariant,
    'variants' => [
        require __DIR__ . '/variants/empty_group.php',
        require __DIR__ . '/variants/empty_filter.php',
    ],
];
