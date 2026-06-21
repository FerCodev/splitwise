<?= view('partials/_head', ['title' => 'SplitWise - Cat&aacute;logo de tarjetas']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Cat&aacute;logo Admin']) ?>

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
                <h2 class="fw-bold mb-1">Cat&aacute;logo por pantalla</h2>
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
                <span class="badge bg-primary">Cat&aacute;logo</span>
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
