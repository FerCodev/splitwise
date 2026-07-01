<?php

$fechaDemo = $catalogDemoData['fechaDemo'];

return [
    'title' => 'Tarjeta de grupo activo',
    'description' => 'Define c&oacute;mo se muestran los grupos en la pesta&ntilde;a activa del Home.',
    'screen' => 'home',
    'component' => 'home_group_card',
    'selected' => $selectedHomeGroupVariant,
    'variants' => [
        [
            'key' => 'operational',
            'name' => 'Operativa',
            'hint' => 'Acciones directas para entrar o cargar gasto.',
            'render' => static fn () => view('components/cards/grupo', ['variant' => 'operational', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'ultimoTipo' => 'gasto', 'ultimoDescripcion' => 'Supermercado', 'ultimoMonto' => 45200, 'ultimoFecha' => $fechaDemo]),
        ],
        [
            'key' => 'balance_first',
            'name' => 'Balance primero',
            'hint' => 'El saldo es el dato protagonista.',
            'render' => static fn () => view('components/cards/grupo', ['variant' => 'balance_first', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500]),
        ],
        [
            'key' => 'compact',
            'name' => 'Resumen compacto',
            'hint' => 'Listado m&aacute;s denso para muchas tarjetas.',
            'render' => static fn () => view('components/cards/grupo', ['variant' => 'compact', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'ultimoDescripcion' => 'supermercado mensual']),
        ],
        [
            'key' => 'action_large',
            'name' => 'Acci&oacute;n amplia',
            'hint' => 'Botones Entrar y + Gasto m&aacute;s grandes.',
            'render' => static fn () => view('components/cards/grupo', ['variant' => 'action_large', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'ultimoTipo' => 'gasto', 'ultimoDescripcion' => 'Supermercado', 'ultimoMonto' => 45200, 'ultimoFecha' => $fechaDemo, 'gastoUrl' => '#']),
        ],
        [
            'key' => 'activity_split',
            'name' => 'Actividad separada',
            'hint' => 'Saldo arriba y ultimo movimiento en bloque propio.',
            'render' => static fn () => view('components/cards/grupo', ['variant' => 'activity_split', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'ultimoTipo' => 'gasto', 'ultimoDescripcion' => 'Supermercado', 'ultimoMonto' => 45200, 'ultimoFecha' => $fechaDemo, 'gastoUrl' => '#']),
        ],
        [
            'key' => 'minimal_panel',
            'name' => 'Panel simple',
            'hint' => 'Total gastado visible y accesos equilibrados.',
            'render' => static fn () => view('components/cards/grupo', ['variant' => 'minimal_panel', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'totalGastado' => 145000, 'ultimoDescripcion' => 'supermercado mensual', 'entrarUrl' => '#', 'gastoUrl' => '#']),
        ],
    ],
];