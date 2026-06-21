<?= view('partials/_head', ['title' => 'SplitWise - Catálogo de tarjetas']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Catálogo Admin']) ?>

<?php
$fechaDemo = '2026-04-15';
$fechaCorta = date('d/m/Y', strtotime($fechaDemo));
$activeScreen = $activeScreen ?? '';

$selectedDebtVariant = $selectedDebtVariant ?? 'soft';
$selectedGaugeVariant = $selectedGaugeVariant ?? 'semicircle';
$selectedMovementVariant = $selectedMovementVariant ?? 'feed';
$selectedHomeGroupVariant = $selectedHomeGroupVariant ?? 'operational';
$selectedExpensesTotalVariant = $selectedExpensesTotalVariant ?? 'simple';
$selectedPaymentsTotalVariant = $selectedPaymentsTotalVariant ?? 'simple';
$selectedPaymentMethodVariant = $selectedPaymentMethodVariant ?? 'bank_card';

$variantAction = static function (string $screenKey, string $componentKey, string $variant, string $selectedVariant, string $returnScreen): string {
    $selected = $selectedVariant === $variant;
    ob_start();
    ?>
    <form method="post" action="<?= base_url('admin/catalogo-tarjetas/componente') ?>" class="catalog-component-action">
        <?= csrf_field() ?>
        <input type="hidden" name="screen_key" value="<?= esc($screenKey) ?>">
        <input type="hidden" name="component_key" value="<?= esc($componentKey) ?>">
        <input type="hidden" name="variant_key" value="<?= esc($variant) ?>">
        <input type="hidden" name="return_screen" value="<?= esc($returnScreen) ?>">
        <button type="submit" class="btn btn-sm <?= $selected ? 'btn-success' : 'btn-outline-primary' ?>" <?= $selected ? 'disabled' : '' ?>>
            <?= $selected ? 'Seleccionado' : 'Usar este dise&ntilde;o' ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
};

$screens = [
    'home' => [
        'title' => 'Home',
        'description' => 'Grupos activos y deudas pendientes.',
        'badge' => '2 componentes',
    ],
    'grupos' => [
        'title' => 'Grupos',
        'description' => 'Detalle del grupo, movimientos y veloc&iacute;metro.',
        'badge' => '2 componentes',
    ],
    'gastos' => [
        'title' => 'Gastos',
        'description' => 'Totalizador superior de gastos filtrados.',
        'badge' => '1 componente',
    ],
    'pagos' => [
        'title' => 'Pagos',
        'description' => 'Totalizador superior de pagos filtrados.',
        'badge' => '1 componente',
    ],
    'medios' => [
        'title' => 'Medios de cobro',
        'description' => 'Tarjeta del medio favorito, datos copiables y configuraci&oacute;n.',
        'badge' => '1 componente',
    ],
    'tabler' => [
        'title' => 'Tabler',
        'description' => 'Subcat&aacute;logo inspirado en Tabler para evaluar una librer&iacute;a visual externa.',
        'badge' => '57 ejemplos',
    ],
    'propuestas' => [
        'title' => 'Propuestas',
        'description' => 'Ideas visuales que todav&iacute;a no est&aacute;n conectadas a pantallas reales.',
        'badge' => 'sin selector',
    ],
];

$catalog = [
    'home' => [
        [
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
                    'hint' => 'Compacta con CTA inferior.',
                    'render' => static fn () => view('components/cards/grupo', ['variant' => 'minimal_panel', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'ultimoDescripcion' => 'supermercado mensual', 'gastoUrl' => '#']),
                ],
            ],
        ],
        [
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
        ],
    ],
    'grupos' => [
        [
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
        ],
        [
            'title' => 'Veloc&iacute;metro de aporte',
            'description' => 'Widget que compara lo pagado por el usuario contra el total gastado del grupo.',
            'screen' => 'grupo_show',
            'component' => 'group_gauge',
            'selected' => $selectedGaugeVariant,
            'variants' => [
                ['key' => 'semicircle', 'name' => 'Actual', 'hint' => 'Aguja semicircular.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'semicircle', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
                ['key' => 'compact_dial', 'name' => 'Dial compacto', 'hint' => 'Score central y montos.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'compact_dial', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
                ['key' => 'scale_bar', 'name' => 'Barra de escala', 'hint' => 'Lectura horizontal.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'scale_bar', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
                ['key' => 'segmented_arc', 'name' => 'Arco por tramos', 'hint' => 'Zonas verdes, amarillas y rojas.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'segmented_arc', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
                ['key' => 'segmented_donut', 'name' => 'Dona segmentada', 'hint' => 'Dial circular con zonas.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'segmented_donut', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
                ['key' => 'clean_arc', 'name' => 'Medialuna limpia', 'hint' => 'Escala numerada amplia.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'clean_arc', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
                ['key' => 'milestone_ring', 'name' => 'Aro con hitos', 'hint' => 'Importes clave visibles.', 'render' => static fn () => view('components/widgets/velocimetro_aporte', ['variant' => 'milestone_ring', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723])],
            ],
        ],
    ],
    'gastos' => [
        [
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
        ],
    ],
    'pagos' => [
        [
            'title' => 'Total filtrado de pagos',
            'description' => 'Define la card superior que totaliza lo filtrado en Pagos.',
            'screen' => 'pagos_index',
            'component' => 'filtered_total_card',
            'selected' => $selectedPaymentsTotalVariant,
            'variants' => [
                ['key' => 'simple', 'name' => 'KPI simple', 'hint' => 'Monto protagonista.', 'render' => static fn () => view('components/cards/resumen', ['variant' => 'simple', 'titulo' => 'Total filtrado', 'monto' => 45000, 'detalle' => 'Suma de pagos filtrados', 'color' => 'text-success'])],
                ['key' => 'detail', 'name' => 'Balance detallado', 'hint' => 'Cuatro datos en grilla.', 'render' => static fn () => view('components/cards/resumen', ['variant' => 'detail', 'titulo' => 'Total filtrado', 'monto' => 45000, 'detalle' => 'Suma de pagos filtrados', 'color' => 'text-success', 'secundarios' => [['label' => 'Registros', 'value' => '3'], ['label' => 'Periodo', 'value' => 'Junio'], ['label' => 'Filtro', 'value' => 'Activo']]])],
                ['key' => 'compare', 'name' => 'Comparativo', 'hint' => 'Dos bloques de lectura.', 'render' => static fn () => view('components/cards/resumen', ['variant' => 'compare', 'titulo' => 'Total filtrado', 'monto' => 45000, 'detalle' => 'Filtro aplicado', 'color' => 'text-success'])],
            ],
        ],
    ],
    'medios' => [
        [
            'title' => 'Tarjeta de medio de cobro',
            'description' => 'Define c&oacute;mo se muestra cada medio de cobro guardado.',
            'screen' => 'mis_medios_cobro',
            'component' => 'payment_method_card',
            'selected' => $selectedPaymentMethodVariant,
            'variants' => [
                ['key' => 'bank_card', 'name' => 'Credencial actual', 'hint' => 'Azul, favorita y configuraci&oacute;n.', 'render' => static fn () => view('components/cards/medio_cobro', ['variant' => 'bank_card', 'nombre' => 'Banco Galicia', 'activo' => true, 'favorito' => true, 'titular' => 'Fernando Montes de Oca', 'alias' => 'fernando.montesdeoca'])],
                ['key' => 'compact', 'name' => 'Compacta operativa', 'hint' => 'Listado eficiente.', 'render' => static fn () => view('components/cards/medio_cobro', ['variant' => 'compact', 'nombre' => 'CBU Santander', 'activo' => true, 'favorito' => false, 'titular' => 'Fernando Montes de Oca', 'alias' => 'fer.santander'])],
                ['key' => 'favorite', 'name' => 'Favorita destacada', 'hint' => 'Medio principal.', 'render' => static fn () => view('components/cards/medio_cobro', ['variant' => 'favorite', 'nombre' => 'Mercado Pago', 'activo' => true, 'favorito' => true, 'titular' => 'Fernando', 'alias' => 'fer.mp'])],
            ],
        ],
    ],
];

$tablerCatalog = [
    [
        'title' => 'M&eacute;tricas',
        'description' => 'Cards de resumen tipo dashboard para Home y Reportes.',
        'items' => [
            ['name' => 'Stat card', 'hint' => 'M&eacute;trica compacta con tendencia.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><span class="tabler-proposal-icon tabler-proposal-icon-blue"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-7"/></svg></span><span class="badge bg-success-subtle text-success">+12%</span></div><div class="tabler-proposal-label">Total del mes</div><div class="tabler-proposal-value">' . moneda(1563723) . '</div><div class="text-muted small">58 movimientos cargados</div></div>'],
            ['name' => 'KPI horizontal', 'hint' => 'Resumen con icono y subtotales.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-card-flat"><div class="tabler-proposal-row"><span class="tabler-proposal-icon tabler-proposal-icon-green">$</span><div class="text-end"><div class="tabler-proposal-value text-success">' . moneda(898212) . '</div><div class="text-muted small">Vos pagaste</div></div></div><div class="tabler-proposal-split"><span>Tu parte <b>' . moneda(449106) . '</b></span><span>Registros <b>18</b></span></div></div>'],
            ['name' => 'Balance compacto', 'hint' => 'Saldo y estado del periodo.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-label">Saldo del periodo</div><div class="tabler-proposal-value text-success">' . moneda(0) . '</div><div class="tabler-proposal-progress"><span style="width: 100%;"></span></div><div class="tabler-proposal-row"><small class="text-muted">Liquidado</small><span class="badge bg-success">OK</span></div></div>'],
        ],
    ],
    [
        'title' => 'Grupos',
        'description' => 'Cards para listado de grupos activos, cerrados o con deuda.',
        'items' => [
            ['name' => 'Grupo dashboard', 'hint' => 'Card limpia con acciones.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row align-items-start"><div><div class="tabler-proposal-title">Junio</div><div class="text-muted small">Grupo activo</div></div><span class="badge bg-success">Activo</span></div><div class="tabler-proposal-balance text-danger">' . moneda(112697) . ' debe</div><div class="tabler-proposal-progress"><span style="width: 62%;"></span></div><div class="tabler-proposal-actions"><button class="btn btn-outline-primary btn-sm" type="button">Entrar</button><button class="btn btn-primary btn-sm" type="button">+ Gasto</button></div></div>'],
            ['name' => 'Grupo con avatar', 'hint' => 'Avatar, saldo y CTA principal.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row align-items-start"><div class="catalog-row"><span class="tabler-proposal-avatar">J</span><div><div class="tabler-proposal-title">Junio</div><div class="text-muted small">2 integrantes</div></div></div><span class="badge bg-success">Activo</span></div><div class="tabler-proposal-copy"><span>Tu balance</span><strong class="text-success">' . moneda(5000) . ' a favor</strong></div><button class="btn btn-primary btn-sm w-100" type="button">Cargar gasto</button></div>'],
            ['name' => 'Grupo cerrado', 'hint' => 'Historial sobrio y consultable.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-muted"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Abril</div><div class="text-muted small">41 movimientos</div></div><span class="badge bg-secondary">Cerrado</span></div><div class="tabler-proposal-split"><span>Total <b>' . moneda(1563723) . '</b></span><span>Saldo <b>' . moneda(0) . '</b></span></div><button class="btn btn-outline-secondary btn-sm w-100" type="button">Ver historial</button></div>'],
        ],
    ],
    [
        'title' => 'Movimientos',
        'description' => 'Listas y feeds para gastos, pagos y actividad del grupo.',
        'items' => [
            ['name' => 'Lista operativa', 'hint' => 'Movimientos tipo timeline.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-list"><div><span class="tabler-proposal-dot bg-primary"></span><div><strong>Supermercado</strong><small>Fernando &middot; Gasto</small></div><b class="text-primary">' . moneda(45200) . '</b></div><div><span class="tabler-proposal-dot bg-success"></span><div><strong>Pago parcial</strong><small>Antonella &middot; Pago</small></div><b class="text-success">' . moneda(30000) . '</b></div><div><span class="tabler-proposal-dot bg-danger"></span><div><strong>Saldo pendiente</strong><small>Junio &middot; Deuda</small></div><b class="text-danger">' . moneda(12600) . '</b></div></div></div>'],
            ['name' => 'Movimiento compacto', 'hint' => 'Una fila densa por movimiento.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><span class="badge bg-primary me-1">Gasto</span><strong>Farmacia</strong><div class="text-muted small">20/06/2026 &middot; Antonella</div></div><b class="text-primary">' . moneda(9200) . '</b></div><div class="tabler-proposal-row"><div><span class="badge bg-success me-1">Pago</span><strong>Transferencia</strong><div class="text-muted small">21/06/2026 &middot; Fernando</div></div><b class="text-success">' . moneda(5000) . '</b></div></div>'],
            ['name' => 'Feed con filtros', 'hint' => 'Header operativo con bot&oacute;n de filtro.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Movimientos</div><div class="text-muted small">Junio &middot; 12 registros</div></div><button class="btn btn-outline-primary btn-sm" type="button">Filtrar</button></div><div class="tabler-proposal-progress"><span style="width: 72%;"></span></div><div class="text-muted small">Mostrando gastos y pagos del grupo.</div></div>'],
        ],
    ],
    [
        'title' => 'Deudas y pagos',
        'description' => 'Tarjetas para pendientes, cobros y comprobantes.',
        'items' => [
            ['name' => 'Deuda suave', 'hint' => 'Rojo sobrio estilo Tabler.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-danger"><div class="tabler-proposal-row"><div><span class="badge bg-danger">Deb&eacute;s</span><strong class="d-block mt-2">Fernando</strong><div class="text-muted small">Junio &middot; Pago pendiente</div></div><b class="text-danger">' . moneda(112697) . '</b></div></div>'],
            ['name' => 'Cobro pendiente', 'hint' => 'Verde para dinero a favor.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-success"><div class="tabler-proposal-row"><div><span class="badge bg-success">Te deben</span><strong class="d-block mt-2">Antonella</strong><div class="text-muted small">Mayo &middot; Cobro pendiente</div></div><b class="text-success">' . moneda(14570) . '</b></div></div>'],
            ['name' => 'Comprobante', 'hint' => 'Formato de pago confirmado.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Pago confirmado</div><div class="text-muted small">Fernando &rarr; Antonella</div></div><span class="badge bg-success">OK</span></div><div class="tabler-proposal-copy"><span>Importe</span><strong class="text-success">' . moneda(30000) . '</strong></div><div class="text-muted small">21/06/2026 &middot; Mercado Pago</div></div>'],
        ],
    ],
    [
        'title' => 'Medios y formularios',
        'description' => 'Credenciales, controles y formularios compactos.',
        'items' => [
            ['name' => 'Medio mini', 'hint' => 'Medio de cobro con datos copiables.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Mercado Pago</div><div class="text-muted small">Favorito</div></div><span class="tabler-proposal-icon tabler-proposal-icon-green"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg></span></div><div class="tabler-proposal-copy"><span>Alias</span><strong>fernando.montesdeoca</strong></div><div class="tabler-proposal-copy"><span>CBU/CVU</span><strong>0000003100012345678901</strong></div></div>'],
            ['name' => 'Campo pagador', 'hint' => 'Selector de persona estilo control.', 'render' => static fn () => '<div class="tabler-proposal-card"><label class="tabler-proposal-label">Pagado por</label><div class="tabler-proposal-input"><span class="tabler-proposal-avatar tabler-proposal-avatar-sm">F</span><strong>Fernando</strong><span>Seleccionar</span></div><label class="tabler-proposal-label">Monto</label><div class="tabler-proposal-input"><strong>' . moneda(10000) . '</strong><span>ARS</span></div></div>'],
            ['name' => 'Botonera', 'hint' => 'Acciones claras para ABM.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-title">Acciones</div><div class="tabler-proposal-actions"><button class="btn btn-primary" type="button">Guardar</button><button class="btn btn-outline-secondary" type="button">Cancelar</button></div><button class="btn btn-outline-danger btn-sm w-100" type="button">Eliminar registro</button><div class="text-muted small">Botones sobrios y previsibles.</div></div>'],
        ],
    ],
    [
        'title' => 'Navegaci&oacute;n y filtros',
        'description' => 'Patrones para tabs, filtros y acciones r&aacute;pidas.',
        'items' => [
            ['name' => 'Tabs segmentados', 'hint' => 'Selector de estado para Home.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-tabs"><span>Todos</span><span class="active">Activos</span><span>Cerrados</span></div><div class="text-muted small">Ideal para alternar listados sin perder contexto.</div></div>'],
            ['name' => 'Barra de filtros', 'hint' => 'Filtros compactos con contador.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Filtros</div><div class="text-muted small">Fecha, categor&iacute;a e integrante</div></div><span class="badge bg-primary">3 activos</span></div><div class="tabler-proposal-filter-row"><span>Junio</span><span>Otros</span><span>Fernando</span></div><button class="btn btn-primary btn-sm w-100" type="button">Aplicar filtros</button></div>'],
            ['name' => 'Acciones r&aacute;pidas', 'hint' => 'Botones densos para exportar.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-title">Exportar movimientos</div><div class="tabler-proposal-actions"><button class="btn btn-danger btn-sm" type="button">PDF</button><button class="btn btn-success btn-sm" type="button">Excel</button></div><button class="btn btn-outline-primary btn-sm w-100" type="button">Filtrar</button><div class="text-muted small">Controles separados y claros.</div></div>'],
        ],
    ],
    [
        'title' => 'Estados',
        'description' => 'Empty states, alertas y confirmaciones con est&eacute;tica Tabler.',
        'items' => [
            ['name' => 'Sin grupos', 'hint' => 'Empty state con acci&oacute;n primaria.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-empty"><span class="tabler-proposal-icon tabler-proposal-icon-blue">+</span><strong>No ten&eacute;s grupos activos</strong><div class="text-muted small">Cre&aacute; uno para empezar a cargar gastos.</div><button class="btn btn-primary btn-sm" type="button">Crear grupo</button></div>'],
            ['name' => 'Sin deudas', 'hint' => 'Estado positivo y liviano.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-empty tabler-proposal-success"><span class="tabler-proposal-icon tabler-proposal-icon-green">&check;</span><strong>Todo saldado</strong><div class="text-muted small">No ten&eacute;s deudas pendientes.</div></div>'],
            ['name' => 'Alerta guardado', 'hint' => 'Feedback de operaci&oacute;n exitosa.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-alert tabler-proposal-alert-success"><strong>Gasto creado</strong><span>El movimiento se agreg&oacute; correctamente.</span></div><div class="tabler-proposal-alert tabler-proposal-alert-danger"><strong>Revis&aacute; el monto</strong><span>El importe ingresado no es v&aacute;lido.</span></div></div>'],
        ],
    ],
    [
        'title' => 'Tablas y listas',
        'description' => 'Vistas densas para escritorio o panel admin.',
        'items' => [
            ['name' => 'Tabla compacta', 'hint' => 'Movimientos escaneables.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-table"><div><b>Fecha</b><b>Tipo</b><b>Monto</b></div><div><span>20/06</span><span>Gasto</span><strong>' . moneda(9200) . '</strong></div><div><span>21/06</span><span>Pago</span><strong class="text-success">' . moneda(5000) . '</strong></div><div><span>22/06</span><span>Gasto</span><strong>' . moneda(12600) . '</strong></div></div></div>'],
            ['name' => 'Ranking', 'hint' => 'Top grupos o categor&iacute;as.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-ranking"><div><span>1</span><b>Mayo</b><strong>' . moneda(1630862) . '</strong></div><div><span>2</span><b>Abril</b><strong>' . moneda(1563723) . '</strong></div><div><span>3</span><b>Junio</b><strong>' . moneda(112697) . '</strong></div></div></div>'],
            ['name' => 'Lista de usuarios', 'hint' => 'Integrantes y roles.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-list"><div><span class="tabler-proposal-avatar tabler-proposal-avatar-sm">F</span><div><strong>Fernando</strong><small>Administrador</small></div><span class="badge bg-primary">Admin</span></div><div><span class="tabler-proposal-avatar tabler-proposal-avatar-sm">A</span><div><strong>Antonella</strong><small>Miembro</small></div><span class="badge bg-secondary">Member</span></div></div></div>'],
        ],
    ],
    [
        'title' => 'Visualizaci&oacute;n',
        'description' => 'Mini gr&aacute;ficos y barras para reportes.',
        'items' => [
            ['name' => 'Barras mensuales', 'hint' => 'Comparaci&oacute;n r&aacute;pida por mes.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-title">Gasto mensual</div><div class="tabler-proposal-bars"><span style="height: 46%;"></span><span style="height: 72%;"></span><span style="height: 58%;"></span><span style="height: 88%;"></span><span style="height: 62%;"></span></div><div class="tabler-proposal-row"><small class="text-muted">Abr</small><small class="text-muted">Ago</small></div></div>'],
            ['name' => 'Progreso de grupo', 'hint' => 'Pagado vs total.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Tu aporte</div><div class="text-muted small">' . moneda(860000) . ' de ' . moneda(1563723) . '</div></div><strong>55%</strong></div><div class="tabler-proposal-meter"><span style="width: 55%;"></span></div><div class="tabler-proposal-filter-row"><span>Bajo</span><span>Medio</span><span>Alto</span></div></div>'],
            ['name' => 'Dona simple', 'hint' => 'Distribuci&oacute;n de categor&iacute;as.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-donut-wrap"><div class="tabler-proposal-donut"><strong>72%</strong></div><div><div class="tabler-proposal-title">Otros</div><div class="text-muted small">Categor&iacute;a principal</div><div class="tabler-proposal-filter-row mt-2"><span>Otros</span><span>Pagos</span></div></div></div>'],
        ],
    ],
    [
        'title' => 'Home operativo',
        'description' => 'Variantes para abrir la app y cargar gastos con pocos toques.',
        'items' => [
            ['name' => 'Grupo acceso directo', 'hint' => 'Card con CTA dominante.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-home-card"><div class="tabler-proposal-row align-items-start"><div><div class="tabler-proposal-title">Junio</div><div class="text-muted small">Activo &middot; 2 integrantes</div></div><span class="badge bg-success">Activo</span></div><div class="tabler-proposal-home-total">' . moneda(25000) . '</div><div class="tabler-proposal-actions tabler-proposal-actions-large"><button class="btn btn-outline-primary" type="button">Entrar</button><button class="btn btn-primary" type="button">+ Gasto</button></div></div>'],
            ['name' => 'Grupo con deuda visible', 'hint' => 'Prioriza saldo antes de acciones.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-danger-soft"><div class="tabler-proposal-row align-items-start"><span class="tabler-proposal-avatar">J</span><div class="flex-grow-1"><div class="tabler-proposal-title">Junio</div><div class="text-muted small">Saldo actual</div><strong class="text-danger">' . moneda(112697) . ' deb&eacute;s</strong></div></div><button class="btn btn-danger btn-sm w-100" type="button">Resolver deuda</button></div>'],
            ['name' => 'Grupo minimalista', 'hint' => 'Una tarjeta limpia para listas largas.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-quiet"><div class="tabler-proposal-row"><div><strong>Junio</strong><div class="text-muted small">Ultimo movimiento: hoy</div></div><b class="text-success">' . moneda(5000) . '</b></div><div class="tabler-proposal-row"><button class="btn btn-light btn-sm" type="button">Ver</button><button class="btn btn-primary btn-sm" type="button">Agregar</button></div></div>'],
        ],
    ],
    [
        'title' => 'Detalle de grupo',
        'description' => 'Bloques para cabecera, resumen y estado del grupo.',
        'items' => [
            ['name' => 'Cabecera balance', 'hint' => 'Resumen visual compacto.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row align-items-start"><span class="tabler-proposal-avatar">M</span><div><div class="tabler-proposal-title">Mayo</div><span class="badge bg-success">Activo</span></div><button class="btn btn-light btn-sm ms-auto" type="button">Config.</button></div><div class="tabler-proposal-copy tabler-proposal-copy-center"><span>Tu balance</span><strong class="text-success">' . moneda(130920) . ' a favor</strong></div></div>'],
            ['name' => 'Resumen doble', 'hint' => 'Total y tu parte juntos.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-grid-2"><div><span>Total grupo</span><strong>' . moneda(1630862) . '</strong></div><div><span>Tu parte</span><strong>' . moneda(815431) . '</strong></div></div><div class="tabler-proposal-meter mt-2"><span style="width: 51%;"></span></div><div class="text-muted small">Participaci&oacute;n equilibrada</div></div>'],
            ['name' => 'Estado liquidado', 'hint' => 'Grupo cerrado o sin saldo.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-success"><div class="tabler-proposal-row"><span class="tabler-proposal-icon tabler-proposal-icon-green">&check;</span><div><div class="tabler-proposal-title">Grupo liquidado</div><div class="text-muted small">No quedan pagos pendientes</div></div></div><button class="btn btn-outline-success btn-sm w-100" type="button">Ver movimientos</button></div>'],
        ],
    ],
    [
        'title' => 'Movimientos avanzados',
        'description' => 'Patrones m&aacute;s visuales para gastos y pagos.',
        'items' => [
            ['name' => 'Movimiento por color', 'hint' => 'Diferencia pagador visualmente.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-movement tabler-proposal-user-a"><div><span class="badge bg-primary">Gasto</span><strong>Supermercado</strong><small>Antonella &middot; 20/06</small></div><b>' . moneda(59300) . '</b></div><div class="tabler-proposal-movement tabler-proposal-user-f"><div><span class="badge bg-primary">Gasto</span><strong>Garrafa</strong><small>Fernando &middot; 21/06</small></div><b>' . moneda(57000) . '</b></div></div>'],
            ['name' => 'Timeline vertical', 'hint' => 'Secuencia por fecha.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-timeline"><div><span></span><strong>Gasto creado</strong><small>10:20 &middot; Fernando</small></div><div><span></span><strong>Pago cargado</strong><small>14:05 &middot; Antonella</small></div><div><span></span><strong>Grupo liquidado</strong><small>18:30 &middot; Sistema</small></div></div></div>'],
            ['name' => 'Movimiento con acciones', 'hint' => 'Fila con ABM r&aacute;pido.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><span class="badge bg-primary me-1">Gasto</span><strong>Farmacia</strong><div class="text-muted small">Otros &middot; 2 participantes</div></div><strong>' . moneda(9200) . '</strong></div><div class="tabler-proposal-actions"><button class="btn btn-light btn-sm" type="button">Ver</button><button class="btn btn-light btn-sm" type="button">Editar</button><button class="btn btn-outline-danger btn-sm" type="button">Borrar</button></div></div>'],
        ],
    ],
    [
        'title' => 'Pagos',
        'description' => 'Opciones para pagos pendientes, confirmados y sugeridos.',
        'items' => [
            ['name' => 'Pago pendiente', 'hint' => 'Rojo suave con acci&oacute;n.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-danger-soft"><div class="tabler-proposal-row"><div><span class="badge bg-danger">Pendiente</span><strong class="d-block mt-2">Pagar a Fernando</strong><small class="text-muted">Junio &middot; Transferencia</small></div><b class="text-danger">' . moneda(112697) . '</b></div><button class="btn btn-danger btn-sm w-100" type="button">Registrar pago</button></div>'],
            ['name' => 'Pago recibido', 'hint' => 'Confirmaci&oacute;n verde.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-success"><div class="tabler-proposal-row"><span class="tabler-proposal-icon tabler-proposal-icon-green">$</span><div><strong>Pago recibido</strong><div class="text-muted small">Antonella pag&oacute; su parte</div></div><b class="text-success">' . moneda(14570) . '</b></div></div>'],
            ['name' => 'Sugerencia de pago', 'hint' => 'Indica la transferencia ideal.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-route"><span>Antonella</span><b>&rarr;</b><span>Fernando</span></div><div class="tabler-proposal-copy"><span>Importe sugerido</span><strong class="text-primary">' . moneda(116351) . '</strong></div><button class="btn btn-primary btn-sm w-100" type="button">Usar este pago</button></div>'],
        ],
    ],
    [
        'title' => 'Reportes',
        'description' => 'Cards para lectura de datos y exportaci&oacute;n.',
        'items' => [
            ['name' => 'Resumen mensual', 'hint' => 'Cuatro KPIs en bloque.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-grid-2"><div><span>Total</span><strong>' . moneda(1563723) . '</strong></div><div><span>Pagaste</span><strong class="text-success">' . moneda(898212) . '</strong></div><div><span>Tu parte</span><strong>' . moneda(781862) . '</strong></div><div><span>Saldo</span><strong class="text-success">' . moneda(0) . '</strong></div></div></div>'],
            ['name' => 'Export panel', 'hint' => 'Reportes con PDF y Excel.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Abril 2026</div><div class="text-muted small">41 movimientos filtrados</div></div><strong>' . moneda(1563723) . '</strong></div><div class="tabler-proposal-actions"><button class="btn btn-danger btn-sm" type="button">PDF</button><button class="btn btn-success btn-sm" type="button">Excel</button></div></div>'],
            ['name' => 'Categoria destacada', 'hint' => 'Ranking con barra.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><strong>Otros</strong><span>58 gastos</span></div><div class="tabler-proposal-meter tabler-proposal-meter-gold"><span style="width: 86%;"></span></div><div class="tabler-proposal-row"><small class="text-muted">Categoria principal</small><b>' . moneda(1630862) . '</b></div></div>'],
        ],
    ],
    [
        'title' => 'Formularios de gasto',
        'description' => 'Campos y controles para crear o editar gastos.',
        'items' => [
            ['name' => 'Monto destacado', 'hint' => 'Importe grande y claro.', 'render' => static fn () => '<div class="tabler-proposal-card"><label class="tabler-proposal-label">Monto total</label><div class="tabler-proposal-money-input"><span>$</span><strong>10.000,00</strong><small>ARS</small></div><label class="tabler-proposal-label">Descripci&oacute;n</label><div class="tabler-proposal-input"><span>Gasto</span></div></div>'],
            ['name' => 'Pagador selector', 'hint' => 'Control clickeable completo.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-payer"><span class="tabler-proposal-icon tabler-proposal-icon-blue">$</span><div><small>Pagado por</small><strong>Fernando</strong></div><span class="text-primary fw-bold">Cambiar</span></div>'],
            ['name' => 'Division simple', 'hint' => 'Regla de reparto visible.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-choice"><div><strong>Dividido en partes iguales</strong><small>Fernando y Antonella pagan 50/50</small></div><span>&rsaquo;</span></div><div class="tabler-proposal-split mt-2"><span>Fernando <b>' . moneda(5000) . '</b></span><span>Antonella <b>' . moneda(5000) . '</b></span></div></div>'],
        ],
    ],
    [
        'title' => 'Medios de cobro premium',
        'description' => 'Variantes de credencial, datos copiables y estado favorito.',
        'items' => [
            ['name' => 'Credencial compacta', 'hint' => 'Tarjeta azul tipo bancaria.', 'render' => static fn () => '<div class="tabler-proposal-bank-card"><div class="tabler-proposal-row"><strong>Mercado Pago</strong><span>&starf;</span></div><small>TITULAR</small><b>Fernando Montes de Oca</b><small>ALIAS</small><b>fernando.montesdeoca</b></div>'],
            ['name' => 'Medio con engranaje', 'hint' => 'Acceso directo a edici&oacute;n.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Banco Galicia</div><span class="badge bg-success">Activo</span></div><button class="btn btn-light btn-sm" type="button">&#9881;</button></div><div class="tabler-proposal-copy"><span>CBU/CVU</span><strong>0000003100012345678901</strong></div></div>'],
            ['name' => 'Datos copiables', 'hint' => 'Alias y CBU en filas.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-copy"><span>Alias</span><strong>fer.mercadopago</strong></div><div class="tabler-proposal-copy"><span>CBU/CVU</span><strong>0000003100012345678901</strong></div><button class="btn btn-outline-primary btn-sm w-100" type="button">Copiar datos</button></div>'],
        ],
    ],
    [
        'title' => 'Administraci&oacute;n',
        'description' => 'Componentes para pantallas internas y cat&aacute;logo.',
        'items' => [
            ['name' => 'Selector de pantalla', 'hint' => 'Entrada al cat&aacute;logo por pantalla.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><strong>Home</strong><div class="text-muted small">Grupos activos y deudas</div></div><span class="badge bg-primary-subtle text-primary">2 componentes</span></div><button class="btn btn-primary btn-sm w-100" type="button">Abrir pantalla</button></div>'],
            ['name' => 'Componente elegible', 'hint' => 'Selecci&oacute;n &uacute;nica.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-selected"><div class="tabler-proposal-row"><div><strong>Card principal</strong><div class="text-muted small">Usada en Home</div></div><span class="badge bg-success">Activo</span></div><button class="btn btn-outline-primary btn-sm w-100" type="button">Usar dise&ntilde;o</button></div>'],
            ['name' => 'Propuesta aislada', 'hint' => 'No conectada a pantallas.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-muted"><div class="tabler-proposal-title">Dial experimental</div><div class="text-muted small">Solo para exploraci&oacute;n visual.</div><div class="tabler-proposal-meter mt-2"><span style="width: 38%;"></span></div><span class="badge bg-secondary mt-2">Propuesta</span></div>'],
        ],
    ],
    [
        'title' => 'Navegaci&oacute;n mobile',
        'description' => 'Patrones para men&uacute;, bottom nav y acciones flotantes.',
        'items' => [
            ['name' => 'Bottom nav', 'hint' => 'Navegaci&oacute;n inferior compacta.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-bottom-nav"><span>Home</span><span>Reportes</span><span>Perfil</span><span>Men&uacute;</span></div></div>'],
            ['name' => 'FAB gasto', 'hint' => 'Acci&oacute;n principal flotante.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-fab-stage"><button class="btn btn-success tabler-proposal-fab" type="button">+ Nuevo gasto</button></div>'],
            ['name' => 'Menu item', 'hint' => 'Fila de navegaci&oacute;n con icono.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-menu-item"><span class="tabler-proposal-icon tabler-proposal-icon-blue">R</span><div><strong>Reportes</strong><small>Resumen mensual</small></div><b>&rsaquo;</b></div><div class="tabler-proposal-menu-item tabler-proposal-danger-soft"><span class="tabler-proposal-icon">S</span><div><strong>Cerrar sesi&oacute;n</strong><small>Salir de la cuenta</small></div><b>&rsaquo;</b></div></div>'],
        ],
    ],
    [
        'title' => 'Veloc&iacute;metros',
        'description' => 'Variantes extra para medir gasto propio sobre total del grupo.',
        'items' => [
            ['name' => 'Dial Tabler compacto', 'hint' => 'Aguja y escala simple.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-gauge"><span style="--rotation: 42deg;"></span><strong>55%</strong></div><div class="tabler-proposal-row"><small>' . moneda(0) . '</small><small>' . moneda(1563723) . '</small></div></div>'],
            ['name' => 'Gauge por zonas', 'hint' => 'Verde, amarillo y rojo.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-zone-gauge"><span style="--rotation: 68deg;"></span></div><div class="tabler-proposal-row"><strong>' . moneda(898212) . '</strong><small class="text-muted">de ' . moneda(1563723) . '</small></div></div>'],
            ['name' => 'Escala con ticks', 'hint' => 'Valores visibles en la escala.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-tick-scale"><span></span><span></span><span></span><span></span><b style="left: 58%;"></b></div><div class="tabler-proposal-row"><small>0</small><small>50%</small><small>100%</small></div><strong class="text-primary">' . moneda(898212) . '</strong></div>'],
        ],
    ],
    [
        'title' => 'Microinteracciones',
        'description' => 'Estados visuales para hover, loading y confirmaci&oacute;n.',
        'items' => [
            ['name' => 'Skeleton card', 'hint' => 'Carga inicial.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-skeleton w-75"></div><div class="tabler-proposal-skeleton w-100"></div><div class="tabler-proposal-skeleton w-50"></div></div>'],
            ['name' => 'Confirmaci&oacute;n inline', 'hint' => 'Estado guardado sin modal.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-alert tabler-proposal-alert-success"><strong>Listo</strong><span>El cambio qued&oacute; guardado.</span></div><button class="btn btn-outline-success btn-sm w-100" type="button">Continuar</button></div>'],
            ['name' => 'Acci&oacute;n destructiva', 'hint' => 'Rojo suave y seguro.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-danger-soft"><strong>Eliminar registro</strong><div class="text-muted small">Esta acci&oacute;n requiere confirmaci&oacute;n.</div><button class="btn btn-outline-danger btn-sm w-100" type="button">Eliminar</button></div>'],
        ],
    ],
];

$proposals = [
    [
        'title' => 'Grupo cerrado',
        'description' => 'Opciones visuales para historial de grupos. Todav&iacute;a no est&aacute;n conectadas al selector.',
        'items' => [
            ['name' => 'Hist&oacute;rica', 'hint' => 'Contexto de cierre.', 'render' => static fn () => '<div class="dash-card catalog-preview-card catalog-muted-card"><div class="dash-card-body"><div class="catalog-row"><div class="catalog-avatar catalog-avatar-muted">A</div><div class="min-width-0 flex-grow-1"><div class="catalog-title-row"><strong>Abril</strong><span class="badge bg-warning text-dark">Cerrado</span></div><div class="text-muted small">Cerrado el 30/04/2026</div><div class="small mt-2">Saldo final: <span class="financial-amount text-muted">' . moneda(0) . '</span></div></div></div></div></div>'],
            ['name' => 'Archivo', 'hint' => 'Neutral y sobria.', 'render' => static fn () => '<div class="dash-card catalog-preview-card catalog-archive-card"><div class="dash-card-body"><div class="d-flex justify-content-between align-items-start gap-2"><div><strong>Abril</strong><div class="text-muted small">41 movimientos guardados</div></div><span class="badge bg-secondary">Archivo</span></div><button class="btn btn-outline-secondary btn-sm w-100 mt-3" type="button">Ver historial</button></div></div>'],
            ['name' => 'Liquidada', 'hint' => 'Sin pendientes.', 'render' => static fn () => '<div class="dash-card catalog-preview-card catalog-settled-card"><div class="dash-card-body text-center"><div class="catalog-status-circle catalog-status-ok mx-auto mb-2">&check;</div><strong>Abril liquidado</strong><div class="text-muted small">No quedan deudas pendientes.</div></div></div>'],
        ],
    ],
    [
        'title' => 'Gasto',
        'description' => 'Propuestas para gasto gen&eacute;rico. Hoy las pantallas reales todav&iacute;a usan sus propios listados.',
        'items' => [
            ['name' => 'Movimiento azul', 'hint' => 'Patr&oacute;n actual.', 'render' => static fn () => '<div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card report-movement-expense catalog-border-primary"><div class="catalog-card-top"><div><span class="badge bg-primary me-1">Gasto</span><span class="fw-medium small">Supermercado mensual</span></div><span class="fw-bold small text-primary">' . moneda(45200) . '</span></div><div class="text-muted small mt-1">' . $fechaCorta . ' &middot; Fernando</div><div class="text-muted small"><span class="badge bg-light text-dark">Supermercado</span> Grupo: Mayo &middot; 2 part.</div></div></a></div>'],
            ['name' => 'Pagador protagonista', 'hint' => 'Color por usuario.', 'render' => static fn () => '<div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card catalog-user-fer"><div class="catalog-card-top"><div class="catalog-row"><div class="catalog-avatar catalog-avatar-primary catalog-avatar-xs">F</div><div><span class="badge bg-primary me-1">Gasto</span><span class="fw-medium small">Garrafa</span></div></div><span class="fw-bold small text-primary">' . moneda(57000) . '</span></div><div class="text-muted small mt-1">' . $fechaCorta . ' &middot; Pag&oacute; Fernando</div></div></a></div>'],
            ['name' => 'Recibo compacto', 'hint' => 'Datos en chips.', 'render' => static fn () => '<div class="dash-card catalog-preview-card"><div class="dash-card-body"><div class="catalog-card-top"><strong>Diferencia</strong><span class="financial-amount text-primary">' . moneda(193701) . '</span></div><div class="catalog-chip-row mt-2"><span class="badge bg-primary">Gasto</span><span class="badge bg-light text-dark">Otros</span><span class="badge bg-light text-dark">2 part.</span></div><div class="text-muted small mt-2">01/05/2026 &middot; Fernando &middot; Mayo</div></div></div>'],
        ],
    ],
    [
        'title' => 'Pago',
        'description' => 'Propuestas para pagos y comprobantes. A&uacute;n no est&aacute;n unificadas como componente elegible.',
        'items' => [
            ['name' => 'Movimiento verde', 'hint' => 'Patr&oacute;n actual.', 'render' => static fn () => '<div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card report-movement-payment catalog-border-success"><div class="catalog-card-top"><div><span class="badge bg-success me-1">Pago</span><span class="fw-medium small">Pago</span></div><span class="fw-bold small text-success">' . moneda(30000) . '</span></div><div class="text-muted small mt-1">18/04/2026 &middot; Fernando pag&oacute; a Antonella</div><div class="text-muted small">Grupo: Mayo</div></div></a></div>'],
            ['name' => 'Transferencia', 'hint' => 'De una persona a otra.', 'render' => static fn () => '<div class="dash-card catalog-preview-card"><div class="dash-card-body"><div class="catalog-transfer"><div><div class="catalog-avatar catalog-avatar-primary catalog-avatar-sm">F</div><small>Fernando</small></div><div class="catalog-transfer-arrow">&rarr;</div><div><div class="catalog-avatar catalog-avatar-success catalog-avatar-sm">A</div><small>Antonella</small></div></div><div class="catalog-balance-amount text-success text-center mt-2">' . moneda(30000) . '</div></div></div>'],
            ['name' => 'Comprobante', 'hint' => 'Formato formal.', 'render' => static fn () => '<div class="dash-card catalog-preview-card"><div class="dash-card-header"><div class="catalog-title-row"><strong>Comprobante</strong><span class="badge bg-success">Confirmado</span></div></div><div class="dash-card-body"><div class="catalog-card-top"><span class="text-muted small">Mayo</span><span class="financial-amount text-success">' . moneda(42500) . '</span></div><div class="text-muted small mt-2">05/04/2026 &middot; Antonella &rarr; Fernando</div></div></div>'],
        ],
    ],
];

$activeScreenMeta = $screens[$activeScreen] ?? null;
?>

<div class="container catalog-page mt-3 mt-md-4 pb-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="catalog-hero mb-2">
        <div>
            <p class="catalog-kicker mb-1">Administraci&oacute;n visual</p>
            <?php if ($activeScreenMeta): ?>
                <h2 class="fw-bold mb-1"><?= $activeScreenMeta['title'] ?></h2>
                <p class="text-muted mb-0"><?= $activeScreenMeta['description'] ?></p>
            <?php else: ?>
                <h2 class="fw-bold mb-1">Catálogo por pantalla</h2>
                <p class="text-muted mb-0">Eleg&iacute; una pantalla para abrir sus componentes disponibles. Cada componente permite activar un solo dise&ntilde;o a la vez.</p>
            <?php endif; ?>
        </div>
        <?php if ($activeScreenMeta): ?>
            <a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/catalogo-tarjetas') ?>">Volver al cat&aacute;logo</a>
        <?php else: ?>
            <span class="badge bg-primary-subtle text-primary catalog-count"><?= count($screens) ?> pantallas</span>
        <?php endif; ?>
    </div>

    <?php if ($activeScreen === ''): ?>
        <section class="catalog-section">
            <div class="catalog-section-head">
                <div>
                    <h5>Pantallas</h5>
                    <p>Entr&aacute; a una pantalla para elegir qu&eacute; dise&ntilde;o usa cada componente configurable.</p>
                </div>
                <span class="badge bg-primary">Catálogo</span>
            </div>
            <div class="catalog-screen-grid">
                <?php foreach ($screens as $key => $screen): ?>
                    <a class="catalog-screen-card" href="<?= base_url('admin/catalogo-tarjetas/' . rawurlencode($key)) ?>">
                        <div>
                            <strong><?= $screen['title'] ?></strong>
                            <span><?= $screen['description'] ?></span>
                        </div>
                        <small><?= $screen['badge'] ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php elseif ($activeScreen === 'tabler'): ?>
        <?php foreach ($tablerCatalog as $section): ?>
            <section class="catalog-section">
                <div class="catalog-section-head">
                    <div>
                        <h5><?= $section['title'] ?></h5>
                        <p><?= $section['description'] ?></p>
                    </div>
                    <span class="badge bg-primary">Tabler</span>
                </div>
                <div class="catalog-grid">
                    <?php foreach ($section['items'] as $item): ?>
                        <article class="catalog-variant">
                            <div class="catalog-variant-meta">
                                <span><?= $item['name'] ?></span>
                                <small><?= $item['hint'] ?></small>
                            </div>
                            <div class="catalog-source-badge">Ejemplo Tabler</div>
                            <?= $item['render']() ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php elseif ($activeScreen === 'propuestas'): ?>
        <?php foreach ($proposals as $proposal): ?>
            <section class="catalog-section">
                <div class="catalog-section-head">
                    <div>
                        <h5><?= $proposal['title'] ?></h5>
                        <p><?= $proposal['description'] ?></p>
                    </div>
                    <span class="badge bg-secondary">Propuesta</span>
                </div>
                <div class="catalog-grid">
                    <?php foreach ($proposal['items'] as $item): ?>
                        <article class="catalog-variant">
                            <div class="catalog-variant-meta">
                                <span><?= $item['name'] ?></span>
                                <small><?= $item['hint'] ?></small>
                            </div>
                            <?php if (!empty($proposal['label'])): ?>
                                <div class="catalog-source-badge"><?= $proposal['label'] ?></div>
                            <?php endif; ?>
                            <?= $item['render']() ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php elseif (isset($catalog[$activeScreen])): ?>
        <?php foreach ($catalog[$activeScreen] as $component): ?>
            <section class="catalog-section">
                <div class="catalog-section-head">
                    <div>
                        <h5><?= $component['title'] ?></h5>
                        <p><?= $component['description'] ?></p>
                    </div>
                    <span class="badge bg-success">1 activo</span>
                </div>
                <div class="catalog-grid">
                    <?php foreach ($component['variants'] as $variant): ?>
                        <?php $isSelected = $component['selected'] === $variant['key']; ?>
                        <article class="catalog-variant <?= $isSelected ? 'catalog-variant-selected' : '' ?>">
                            <div class="catalog-variant-meta">
                                <span><?= $variant['name'] ?></span>
                                <small><?= $variant['hint'] ?></small>
                            </div>
                            <?= $variant['render']() ?>
                            <?= $variantAction($component['screen'], $component['component'], $variant['key'], $component['selected'], $activeScreen) ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="catalog-section">
            <div class="catalog-empty-state">
                <strong>La pantalla seleccionada no existe.</strong>
                <span>Volv&eacute; al listado y eleg&iacute; una pantalla disponible.</span>
            </div>
        </section>
    <?php endif; ?>
</div>

<?= view('partials/_footer') ?>
