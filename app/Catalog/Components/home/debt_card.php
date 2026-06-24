<?php

return [
    'title' => 'Tarjeta de deuda pendiente',
    'description' => 'Define c&oacute;mo se ven las deudas pendientes en Home.',
    'screen' => 'home',
    'component' => 'debt_card',
    'selected' => $selectedDebtVariant,
    'variants' => [
        [
            'key' => 'soft',
            'name' => 'Alerta suave',
            'hint' => 'Rojo suave, parecido a cerrar sesi&oacute;n.',
            'render' => static fn () => view('components/cards/deuda', ['variant' => 'soft', 'modo' => 'debes', 'persona' => 'Antonella', 'grupo' => 'Mayo', 'monto' => 8500, 'preview' => true]),
        ],
        [
            'key' => 'direct_action',
            'name' => 'Acci&oacute;n directa',
            'hint' => 'Incluye CTA visible para resolver.',
            'render' => static fn () => view('components/cards/deuda', ['variant' => 'direct_action', 'modo' => 'debes', 'persona' => 'Antonella', 'grupo' => 'Mayo', 'monto' => 8500, 'preview' => true]),
        ],
        [
            'key' => 'person_summary',
            'name' => 'Resumen persona',
            'hint' => 'Agrupa por persona con avatar.',
            'render' => static fn () => view('components/cards/deuda', ['variant' => 'person_summary', 'modo' => 'te_deben', 'persona' => 'Antonella', 'grupo' => 'Mayo', 'monto' => 12300, 'preview' => true]),
        ],
    ],
];