<?php

return [
    'title' => 'Seguridad y sesion',
    'description' => 'Alertas para permisos de administrador y sesion proxima a vencer.',
    'screen' => 'system_alerts',
    'component' => 'alert_security_session',
    'selected' => $selectedAlertSecuritySessionVariant,
    'variants' => [
        require __DIR__ . '/variants/admin_permission.php',
        require __DIR__ . '/variants/session_expiring.php',
    ],
];
