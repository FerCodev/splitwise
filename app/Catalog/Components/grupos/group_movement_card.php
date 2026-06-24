<?php

$fechaDemo = $catalogDemoData['fechaDemo'];

return [
    'title' => 'Movimientos del grupo',
    'description' => 'Define las tarjetas del listado de movimientos dentro de un grupo.',
    'screen' => 'grupo_show',
    'component' => 'group_movement_card',
    'selected' => $selectedMovementVariant,
    'variants' => [
        [
            'key' => 'feed',
            'name' => 'Feed',
            'hint' => 'Patr&oacute;n similar a Reportes.',
            'render' => static fn () => view('components/cards/movimiento', ['variant' => 'feed', 'tipo' => 'gasto', 'descripcion' => 'Alquiler', 'monto' => 85000, 'fecha' => $fechaDemo, 'persona' => 'Fernando', 'categoria' => 'Vivienda', 'preview' => true]),
        ],
        [
            'key' => 'user_color',
            'name' => 'Por usuario',
            'hint' => 'Color visual seg&uacute;n qui&eacute;n hizo el movimiento.',
            'render' => static fn () => '<div class="report-movement-list catalog-list-preview">' . view('components/cards/movimiento', ['variant' => 'user_color', 'wrap' => false, 'tipo' => 'gasto', 'descripcion' => 'Gasto chico', 'monto' => 6000, 'fecha' => $fechaDemo, 'persona' => 'Fernando', 'categoria' => 'Otros']) . view('components/cards/movimiento', ['variant' => 'user_color', 'wrap' => false, 'tipo' => 'pago', 'descripcion' => 'Pago parcial', 'monto' => 5000, 'fecha' => $fechaDemo, 'persona' => 'Antonella']) . '</div>',
        ],
        [
            'key' => 'compact',
            'name' => 'Compacto',
            'hint' => 'Listado m&aacute;s denso.',
            'render' => static fn () => view('components/cards/movimiento', ['variant' => 'compact', 'tipo' => 'gasto', 'descripcion' => 'Supermercado mensual', 'monto' => 45200, 'fecha' => $fechaDemo, 'persona' => 'Fernando', 'categoria' => 'Supermercado', 'preview' => true]),
        ],
    ],
];