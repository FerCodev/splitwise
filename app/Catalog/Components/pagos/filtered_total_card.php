<?php

return [
    'title' => 'Total filtrado de pagos',
    'description' => 'Define la card superior que totaliza lo filtrado en Pagos.',
    'screen' => 'pagos_index',
    'component' => 'filtered_total_card',
    'selected' => $selectedPaymentsTotalVariant,
    'variants' => [
        ['key' => 'simple', 'name' => 'KPI simple', 'hint' => 'Monto protagonista.', 'render' => static fn () => view('components/cards/resumen', ['variant' => 'simple', 'titulo' => 'Total filtrado', 'monto' => 45000, 'detalle' => 'Suma de pagos filtrados', 'color' => 'text-success'])],
    ],
];