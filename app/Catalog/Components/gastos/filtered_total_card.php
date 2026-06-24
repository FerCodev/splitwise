<?php

return [
    'title' => 'Total filtrado de gastos',
    'description' => 'Define la card superior que totaliza lo filtrado en Gastos.',
    'screen' => 'gastos_index',
    'component' => 'filtered_total_card',
    'selected' => $selectedExpensesTotalVariant,
    'variants' => [
        ['key' => 'simple', 'name' => 'KPI simple', 'hint' => 'Monto protagonista.', 'render' => static fn () => view('components/cards/resumen', ['variant' => 'simple', 'titulo' => 'Total filtrado', 'monto' => 898212, 'detalle' => 'Suma de gastos filtrados', 'color' => 'text-primary'])],
    ],
];