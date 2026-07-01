<?php

return [
    'alertas' => [
        require __DIR__ . '/Components/alertas/alert_success.php',
        require __DIR__ . '/Components/alertas/alert_error.php',
        require __DIR__ . '/Components/alertas/alert_warning.php',
        require __DIR__ . '/Components/alertas/alert_info.php',
        require __DIR__ . '/Components/alertas/alert_destructive_confirmation.php',
        require __DIR__ . '/Components/alertas/alert_empty_state.php',
        require __DIR__ . '/Components/alertas/alert_security_session.php',
        require __DIR__ . '/Components/alertas/alert_process_export.php',
        require __DIR__ . '/Components/alertas/alert_payment_suggestion.php',
        require __DIR__ . '/Components/alertas/alert_group_event.php',
    ],
    'home' => [
        require __DIR__ . '/Components/home/home_group_card.php',
        require __DIR__ . '/Components/home/debt_card.php',
    ],
    'grupos' => [
        require __DIR__ . '/Components/grupos/group_movement_card.php',
        require __DIR__ . '/Components/grupos/group_balance_card.php',
        require __DIR__ . '/Components/grupos/group_gauge.php',
    ],
    'balance' => [
        require __DIR__ . '/Components/balance/deuda_pendiente_card.php',
    ],
    'gastos' => [
        require __DIR__ . '/Components/gastos/filtered_total_card.php',
    ],
    'pagos' => [
        require __DIR__ . '/Components/pagos/filtered_total_card.php',
    ],
    'medios' => [
        require __DIR__ . '/Components/medios/payment_method_card.php',
    ],
];