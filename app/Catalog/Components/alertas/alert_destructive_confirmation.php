<?php

return [
    'title' => 'Confirmacion destructiva',
    'description' => 'Alertas para acciones destructivas como eliminacion o remocion de integrantes.',
    'screen' => 'system_alerts',
    'component' => 'alert_destructive_confirmation',
    'selected' => $selectedAlertDestructiveConfirmationVariant,
    'variants' => [
        require __DIR__ . '/variants/delete_confirmation.php',
        require __DIR__ . '/variants/member_removed.php',
    ],
];
