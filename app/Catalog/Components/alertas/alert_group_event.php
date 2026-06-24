<?php

return [
    'title' => 'Eventos de grupo',
    'description' => 'Alertas para balance recalculado, nuevo integrante e integrante removido.',
    'screen' => 'system_alerts',
    'component' => 'alert_group_event',
    'selected' => $selectedAlertGroupEventVariant,
    'variants' => [
        require __DIR__ . '/variants/balance_recalculated.php',
        require __DIR__ . '/variants/member_added.php',
        require __DIR__ . '/variants/member_removed.php',
    ],
];
