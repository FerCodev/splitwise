<?php

return [
    'title' => 'Sugerencias de pago',
    'description' => 'Alertas con pagos sugeridos y recomendaciones de liquidacion.',
    'screen' => 'system_alerts',
    'component' => 'alert_payment_suggestion',
    'selected' => $selectedAlertPaymentSuggestionVariant,
    'variants' => [
        require __DIR__ . '/variants/suggested_payment.php',
        require __DIR__ . '/variants/settlement_recommendation.php',
    ],
];
