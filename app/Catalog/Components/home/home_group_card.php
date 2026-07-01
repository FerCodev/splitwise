<?php

$fechaDemo = $catalogDemoData['fechaDemo'];

return [
    'title' => 'Tarjeta de grupo',
    'description' => 'Define c&oacute;mo se muestran los grupos en el Home. Se adapta seg&uacute;n el estado del grupo.',
    'screen' => 'home',
    'component' => 'home_group_card',
    'selected' => $selectedHomeGroupVariant,
    'variants' => [
        [
            'key' => 'operational',
            'name' => 'Operativa',
            'hint' => 'Acciones directas para entrar o cargar gasto.',
            'render' => static fn () => '<div style="display:grid;gap:12px">'
                . view('components/cards/grupo', ['variant' => 'operational', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'ultimoTipo' => 'gasto', 'ultimoDescripcion' => 'Supermercado', 'ultimoMonto' => 45200, 'ultimoFecha' => $fechaDemo, 'gastoUrl' => '#', 'balanceUrl' => '#', 'tieneDeuda' => false, 'esAdmin' => false])
                . view('components/cards/grupo', ['variant' => 'operational', 'nombre' => 'Junio', 'estado' => 'cerrado', 'saldo' => -8500, 'ultimoTipo' => 'gasto', 'ultimoDescripcion' => 'Cena', 'ultimoMonto' => 12000, 'ultimoFecha' => $fechaDemo, 'gastoUrl' => '#', 'balanceUrl' => '#', 'tieneDeuda' => true, 'esAdmin' => false, 'deudaAcreedor' => 'Juan'])
                . view('components/cards/grupo', ['variant' => 'operational', 'nombre' => 'Julio', 'estado' => 'liquidado', 'saldo' => 0, 'ultimoDescripcion' => 'finalizado', 'gastoUrl' => '#', 'balanceUrl' => '#', 'tieneDeuda' => false, 'esAdmin' => false])
                . '</div>',
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
            'hint' => 'Total gastado visible, acceso al tocar la tarjeta. Botones segun estado: +Gastito, Saldar deuda, Ver balance.',
            'render' => static fn () => '<div style="display:grid;gap:12px">'
                . view('components/cards/grupo', ['variant' => 'minimal_panel', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'totalGastado' => 145000, 'ultimoDescripcion' => 'supermercado mensual', 'entrarUrl' => '#', 'gastoUrl' => '#', 'balanceUrl' => '#', 'tieneDeuda' => false, 'esAdmin' => false])
                . view('components/cards/grupo', ['variant' => 'minimal_panel', 'nombre' => 'Junio', 'estado' => 'cerrado', 'saldo' => -8500, 'totalGastado' => 98000, 'ultimoDescripcion' => 'sin movimientos', 'entrarUrl' => '#', 'gastoUrl' => '#', 'balanceUrl' => '#', 'tieneDeuda' => true, 'esAdmin' => false, 'deudaAcreedor' => 'Juan'])
                . view('components/cards/grupo', ['variant' => 'minimal_panel', 'nombre' => 'Julio', 'estado' => 'cerrado', 'saldo' => 0, 'totalGastado' => 56000, 'ultimoDescripcion' => 'grupo saldado', 'entrarUrl' => '#', 'gastoUrl' => '#', 'balanceUrl' => '#', 'tieneDeuda' => false, 'esAdmin' => true])
                . view('components/cards/grupo', ['variant' => 'minimal_panel', 'nombre' => 'Agosto', 'estado' => 'liquidado', 'saldo' => 3200, 'totalGastado' => 72000, 'ultimoDescripcion' => 'grupo finalizado', 'entrarUrl' => '#', 'gastoUrl' => '#', 'balanceUrl' => '#', 'tieneDeuda' => false, 'esAdmin' => false])
                . '</div>',
        ],
    ],
];