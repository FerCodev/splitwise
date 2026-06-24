<?php

return [
    'title' => 'Total filtrado de gastos',
    'description' => 'Define la card superior que totaliza lo filtrado en Gastos.',
    'screen' => 'gastos_index',
    'component' => 'filtered_total_card',
    'selected' => $selectedExpensesTotalVariant,
    'variants' => [
        ['key' => 'simple', 'name' => 'KPI simple', 'hint' => 'Monto protagonista.', 'render' => static fn () => view('components/cards/resumen', ['variant' => 'simple', 'titulo' => 'Total filtrado', 'monto' => 898212, 'detalle' => 'Suma de gastos filtrados', 'color' => 'text-primary'])],
        ['key' => 'detail', 'name' => 'Balance detallado', 'hint' => 'Cuatro datos en grilla.', 'render' => static fn () => view('components/cards/resumen', ['variant' => 'detail', 'titulo' => 'Total filtrado', 'monto' => 898212, 'detalle' => 'Suma de gastos filtrados', 'color' => 'text-primary', 'secundarios' => [['label' => 'Registros', 'value' => '18'], ['label' => 'Periodo', 'value' => 'Abril'], ['label' => 'Filtro', 'value' => 'Activo']]])],
        ['key' => 'compare', 'name' => 'Comparativo', 'hint' => 'Dos bloques de lectura.', 'render' => static fn () => view('components/cards/resumen', ['variant' => 'compare', 'titulo' => 'Total filtrado', 'monto' => 898212, 'detalle' => 'Filtro aplicado', 'color' => 'text-primary'])],
    ],
];