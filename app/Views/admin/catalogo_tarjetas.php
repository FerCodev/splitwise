<?= view('partials/_head', ['title' => 'SplitWise - Cat&aacute;logo de tarjetas']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Cat&aacute;logo Admin']) ?>

<?php
$fechaDemo = '2026-04-15';
$fechaCorta = date('d/m/Y', strtotime($fechaDemo));
$selectedDebtVariant = $selectedDebtVariant ?? 'soft';
$selectedGaugeVariant = $selectedGaugeVariant ?? 'semicircle';
$selectedMovementVariant = $selectedMovementVariant ?? 'feed';
$selectedHomeGroupVariant = $selectedHomeGroupVariant ?? 'operational';
$selectedExpensesTotalVariant = $selectedExpensesTotalVariant ?? 'simple';
$selectedPaymentsTotalVariant = $selectedPaymentsTotalVariant ?? 'simple';
$selectedPaymentMethodVariant = $selectedPaymentMethodVariant ?? 'bank_card';
$componentVariantAction = static function (string $screenKey, string $componentKey, string $variant, string $selectedVariant, string $activeLabel, string $useLabel): string {
    ob_start();
    ?>
    <form method="post" action="<?= base_url('admin/catalogo-tarjetas/componente') ?>" class="catalog-component-action">
        <?= csrf_field() ?>
        <input type="hidden" name="screen_key" value="<?= esc($screenKey) ?>">
        <input type="hidden" name="component_key" value="<?= esc($componentKey) ?>">
        <input type="hidden" name="variant_key" value="<?= esc($variant) ?>">
        <button type="submit" class="btn btn-sm <?= $selectedVariant === $variant ? 'btn-success' : 'btn-outline-primary' ?>">
            <?= $selectedVariant === $variant ? esc($activeLabel) : esc($useLabel) ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
};
$debtVariantAction = static function (string $variant, string $selectedDebtVariant): string {
    ob_start();
    ?>
    <form method="post" action="<?= base_url('admin/catalogo-tarjetas/componente') ?>" class="catalog-component-action">
        <?= csrf_field() ?>
        <input type="hidden" name="screen_key" value="home">
        <input type="hidden" name="component_key" value="debt_card">
        <input type="hidden" name="variant_key" value="<?= esc($variant) ?>">
        <button type="submit" class="btn btn-sm <?= $selectedDebtVariant === $variant ? 'btn-success' : 'btn-outline-primary' ?>">
            <?= $selectedDebtVariant === $variant ? 'Activo en Home' : 'Usar en Home' ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
};
$movementVariantAction = static function (string $variant, string $selectedMovementVariant): string {
    ob_start();
    ?>
    <form method="post" action="<?= base_url('admin/catalogo-tarjetas/componente') ?>" class="catalog-component-action">
        <?= csrf_field() ?>
        <input type="hidden" name="screen_key" value="grupo_show">
        <input type="hidden" name="component_key" value="group_movement_card">
        <input type="hidden" name="variant_key" value="<?= esc($variant) ?>">
        <button type="submit" class="btn btn-sm <?= $selectedMovementVariant === $variant ? 'btn-success' : 'btn-outline-primary' ?>">
            <?= $selectedMovementVariant === $variant ? 'Activo en Grupo' : 'Usar en Grupo' ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
};
$gaugeVariantAction = static function (string $variant, string $selectedGaugeVariant): string {
    ob_start();
    ?>
    <form method="post" action="<?= base_url('admin/catalogo-tarjetas/componente') ?>" class="catalog-component-action">
        <?= csrf_field() ?>
        <input type="hidden" name="screen_key" value="grupo_show">
        <input type="hidden" name="component_key" value="group_gauge">
        <input type="hidden" name="variant_key" value="<?= esc($variant) ?>">
        <button type="submit" class="btn btn-sm <?= $selectedGaugeVariant === $variant ? 'btn-success' : 'btn-outline-primary' ?>">
            <?= $selectedGaugeVariant === $variant ? 'Activo en Grupo' : 'Usar en Grupo' ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
};
?>

<div class="container catalog-page mt-3 mt-md-4 pb-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="catalog-hero mb-4">
        <div>
            <p class="catalog-kicker mb-1">Administraci&oacute;n visual</p>
            <h2 class="fw-bold mb-1">Cat&aacute;logo de tarjetas</h2>
            <p class="text-muted mb-0">Patrones visuales vigentes y variantes de referencia para comparar estilos sin modificar pantallas reales.</p>
        </div>
        <span class="badge bg-primary-subtle text-primary catalog-count">37 variantes</span>
    </div>

    <section class="catalog-section">
        <div class="catalog-section-head">
            <div>
                <h5>Componentes base</h5>
                <p>Partials reutilizables creados para centralizar los patrones actuales. Por ahora solo los consume este cat&aacute;logo.</p>
            </div>
            <span class="badge bg-primary">Componentes</span>
        </div>
        <div class="catalog-grid">
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>components/cards/grupo</span><small>Grupo activo</small></div>
                <?= view('components/cards/grupo', ['nombre' => 'Junio', 'estado' => 'activo', 'saldo' => -112697, 'ultimoTipo' => 'gasto', 'ultimoDescripcion' => 'Ines']) ?>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>components/cards/movimiento</span><small>Gasto o pago</small></div>
                <?= view('components/cards/movimiento', ['tipo' => 'gasto', 'descripcion' => 'Supermercado mensual', 'monto' => 45200, 'fecha' => $fechaDemo, 'persona' => 'Fernando', 'categoria' => 'Supermercado', 'contexto' => 'Grupo: Mayo - 2 part.']) ?>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>components/cards/deuda</span><small>Deuda pendiente</small></div>
                <?= view('components/cards/deuda', ['modo' => 'debes', 'persona' => 'Antonella', 'grupo' => 'Mayo', 'monto' => 8500]) ?>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>components/cards/medio_cobro</span><small>Credencial de cobro</small></div>
                <?= view('components/cards/medio_cobro', ['nombre' => 'Banco Galicia', 'activo' => true, 'favorito' => true, 'titular' => 'Fernando Montes de Oca', 'alias' => 'fernando.montesdeoca']) ?>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>components/cards/resumen</span><small>Totalizador</small></div>
                <?= view('components/cards/resumen', ['titulo' => 'Total filtrado', 'monto' => 898212, 'detalle' => 'Suma de gastos filtrados']) ?>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>components/widgets/velocimetro_aporte</span><small>Aporte dentro del grupo</small></div>
                <?= view('components/widgets/velocimetro_aporte', ['porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723]) ?>
            </article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head">
            <div>
                <h5>Grupo activo</h5>
                <p>Usado en Home y Grupos. Debe permitir entrar al grupo y cargar un gasto con pocos toques.</p>
            </div>
            <span class="badge bg-success">Grupo</span>
        </div>
        <div class="catalog-grid">
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Operativa</span><small>Acciones directas</small></div><?= view('components/cards/grupo', ['variant' => 'operational', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'ultimoTipo' => 'gasto', 'ultimoDescripcion' => 'Supermercado', 'ultimoMonto' => 45200, 'ultimoFecha' => $fechaDemo]) ?><?= $componentVariantAction('home', 'home_group_card', 'operational', $selectedHomeGroupVariant, 'Activo en Home', 'Usar en Home') ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Balance primero</span><small>Saldo protagonista</small></div><?= view('components/cards/grupo', ['variant' => 'balance_first', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500]) ?><?= $componentVariantAction('home', 'home_group_card', 'balance_first', $selectedHomeGroupVariant, 'Activo en Home', 'Usar en Home') ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Resumen compacto</span><small>Listado denso</small></div><?= view('components/cards/grupo', ['variant' => 'compact', 'nombre' => 'Mayo', 'estado' => 'activo', 'saldo' => 12500, 'ultimoDescripcion' => 'supermercado mensual']) ?><?= $componentVariantAction('home', 'home_group_card', 'compact', $selectedHomeGroupVariant, 'Activo en Home', 'Usar en Home') ?></article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head">
            <div>
                <h5>Grupo cerrado</h5>
                <p>Usado para historial. Tiene que verse consultable, pero claramente fuera del flujo principal.</p>
            </div>
            <span class="badge bg-warning text-dark">Grupo</span>
        </div>
        <div class="catalog-grid">
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>Hist&oacute;rica</span><small>Contexto de cierre</small></div>
                <div class="dash-card catalog-preview-card catalog-muted-card">
                    <div class="dash-card-body">
                        <div class="catalog-row">
                            <div class="catalog-avatar catalog-avatar-muted">A</div>
                            <div class="min-width-0 flex-grow-1">
                                <div class="catalog-title-row"><strong>Abril</strong><span class="badge bg-warning text-dark">Cerrado</span></div>
                                <div class="text-muted small">Cerrado el 30/04/2026</div>
                                <div class="small mt-2">Saldo final: <span class="financial-amount text-muted"><?= moneda(0) ?></span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>Archivo</span><small>Neutral y sobria</small></div>
                <div class="dash-card catalog-preview-card catalog-archive-card">
                    <div class="dash-card-body">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <strong>Abril</strong>
                                <div class="text-muted small">41 movimientos guardados</div>
                            </div>
                            <span class="badge bg-secondary">Archivo</span>
                        </div>
                        <button class="btn btn-outline-secondary btn-sm w-100 mt-3" type="button">Ver historial</button>
                    </div>
                </div>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>Liquidada</span><small>Sin pendientes</small></div>
                <div class="dash-card catalog-preview-card catalog-settled-card">
                    <div class="dash-card-body text-center">
                        <div class="catalog-status-circle catalog-status-ok mx-auto mb-2">&check;</div>
                        <strong>Abril liquidado</strong>
                        <div class="text-muted small">No quedan deudas pendientes.</div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head">
            <div>
                <h5>Gasto</h5>
                <p>Usado en Gastos, Grupo y Reportes. Debe comunicar monto, pagador, fecha y categor&iacute;a.</p>
            </div>
            <span class="badge bg-primary">Gasto</span>
        </div>
        <div class="catalog-grid">
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>Movimiento azul</span><small>Patr&oacute;n actual</small></div>
                <div class="report-movement-list catalog-list-preview">
                    <a href="#" class="report-movement-link"><div class="report-movement-card report-movement-expense catalog-border-primary"><div class="catalog-card-top"><div><span class="badge bg-primary me-1">Gasto</span><span class="fw-medium small">Supermercado mensual</span></div><span class="fw-bold small text-primary"><?= moneda(45200) ?></span></div><div class="text-muted small mt-1"><?= $fechaCorta ?> &middot; Fernando</div><div class="text-muted small"><span class="badge bg-light text-dark">Supermercado</span> Grupo: Mayo &middot; 2 part.</div></div></a>
                </div>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>Pagador protagonista</span><small>Color por usuario</small></div>
                <div class="report-movement-list catalog-list-preview">
                    <a href="#" class="report-movement-link"><div class="report-movement-card catalog-user-fer"><div class="catalog-card-top"><div class="catalog-row"><div class="catalog-avatar catalog-avatar-primary catalog-avatar-xs">F</div><div><span class="badge bg-primary me-1">Gasto</span><span class="fw-medium small">Garrafa</span></div></div><span class="fw-bold small text-primary"><?= moneda(57000) ?></span></div><div class="text-muted small mt-1"><?= $fechaCorta ?> &middot; Pag&oacute; Fernando</div></div></a>
                </div>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta"><span>Recibo compacto</span><small>Datos en chips</small></div>
                <div class="dash-card catalog-preview-card">
                    <div class="dash-card-body">
                        <div class="catalog-card-top"><strong>Diferencia</strong><span class="financial-amount text-primary"><?= moneda(193701) ?></span></div>
                        <div class="catalog-chip-row mt-2"><span class="badge bg-primary">Gasto</span><span class="badge bg-light text-dark">Otros</span><span class="badge bg-light text-dark">2 part.</span></div>
                        <div class="text-muted small mt-2">01/05/2026 &middot; Fernando &middot; Mayo</div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head"><div><h5>Pago</h5><p>Usado en Pagos, Grupo y Reportes. Debe mostrar direcci&oacute;n y monto con claridad.</p></div><span class="badge bg-success">Pago</span></div>
        <div class="catalog-grid">
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Movimiento verde</span><small>Patr&oacute;n actual</small></div><div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card report-movement-payment catalog-border-success"><div class="catalog-card-top"><div><span class="badge bg-success me-1">Pago</span><span class="fw-medium small">Pago</span></div><span class="fw-bold small text-success"><?= moneda(30000) ?></span></div><div class="text-muted small mt-1">18/04/2026 &middot; Fernando pag&oacute; a Antonella</div><div class="text-muted small">Grupo: Mayo</div></div></a></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Transferencia</span><small>De una persona a otra</small></div><div class="dash-card catalog-preview-card"><div class="dash-card-body"><div class="catalog-transfer"><div><div class="catalog-avatar catalog-avatar-primary catalog-avatar-sm">F</div><small>Fernando</small></div><div class="catalog-transfer-arrow">&rarr;</div><div><div class="catalog-avatar catalog-avatar-success catalog-avatar-sm">A</div><small>Antonella</small></div></div><div class="catalog-balance-amount text-success text-center mt-2"><?= moneda(30000) ?></div></div></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Comprobante</span><small>Formato formal</small></div><div class="dash-card catalog-preview-card"><div class="dash-card-header"><div class="catalog-title-row"><strong>Comprobante</strong><span class="badge bg-success">Confirmado</span></div></div><div class="dash-card-body"><div class="catalog-card-top"><span class="text-muted small">Mayo</span><span class="financial-amount text-success"><?= moneda(42500) ?></span></div><div class="text-muted small mt-2">05/04/2026 &middot; Antonella &rarr; Fernando</div></div></div></article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head"><div><h5>Deuda pendiente</h5><p>Usado en Home. Debe separar r&aacute;pido deuda propia de cobro pendiente.</p></div><span class="badge bg-danger">Deuda</span></div>
        <div class="catalog-grid">
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Alerta actual</span><small>Rojo suave sin mezcla azul</small></div><?= view('components/cards/deuda', ['variant' => 'soft', 'modo' => 'debes', 'persona' => 'Antonella', 'grupo' => 'Mayo', 'monto' => 8500]) ?><?= $debtVariantAction('soft', $selectedDebtVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Acci&oacute;n directa</span><small>CTA visible</small></div><?= view('components/cards/deuda', ['variant' => 'direct_action', 'modo' => 'debes', 'persona' => 'Antonella', 'grupo' => 'Mayo', 'monto' => 8500]) ?><?= $debtVariantAction('direct_action', $selectedDebtVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Resumen persona</span><small>Agrupa movimientos</small></div><?= view('components/cards/deuda', ['variant' => 'person_summary', 'modo' => 'te_deben', 'persona' => 'Antonella', 'grupo' => 'Mayo', 'monto' => 12300]) ?><?= $debtVariantAction('person_summary', $selectedDebtVariant) ?></article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head"><div><h5>Movimiento de grupo</h5><p>Usado dentro del detalle del grupo. Tiene que filtrar, listar y diferenciar gasto/pago.</p></div><span class="badge bg-info text-dark">Movimiento</span></div>
        <div class="catalog-grid">
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Feed</span><small>Como reportes</small></div><?= view('components/cards/movimiento', ['variant' => 'feed', 'tipo' => 'gasto', 'descripcion' => 'Alquiler', 'monto' => 85000, 'fecha' => $fechaDemo, 'persona' => 'Fernando', 'categoria' => 'Vivienda']) ?><?= $movementVariantAction('feed', $selectedMovementVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Por usuario</span><small>Color de pagador</small></div><div class="report-movement-list catalog-list-preview"><?= view('components/cards/movimiento', ['variant' => 'user_color', 'wrap' => false, 'tipo' => 'gasto', 'descripcion' => 'Gasto chico', 'monto' => 6000, 'fecha' => $fechaDemo, 'persona' => 'Fernando', 'categoria' => 'Otros']) ?><?= view('components/cards/movimiento', ['variant' => 'user_color', 'wrap' => false, 'tipo' => 'pago', 'descripcion' => 'Pago parcial', 'monto' => 5000, 'fecha' => $fechaDemo, 'persona' => 'Antonella']) ?></div><?= $movementVariantAction('user_color', $selectedMovementVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Compacto</span><small>Listado denso</small></div><?= view('components/cards/movimiento', ['variant' => 'compact', 'tipo' => 'gasto', 'descripcion' => 'Supermercado mensual', 'monto' => 45200, 'fecha' => $fechaDemo, 'persona' => 'Fernando', 'categoria' => 'Supermercado']) ?><?= $movementVariantAction('compact', $selectedMovementVariant) ?></article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head"><div><h5>Total / Resumen</h5><p>Usado en Home, Reportes y tarjetas de totales filtrados.</p></div><span class="badge bg-secondary">Resumen</span></div>
        <div class="catalog-grid">
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>KPI simple</span><small>Monto protagonista</small></div><?= view('components/cards/resumen', ['variant' => 'simple', 'titulo' => 'Total filtrado', 'monto' => 898212, 'detalle' => 'Suma de gastos filtrados', 'color' => 'text-primary']) ?><?= $componentVariantAction('gastos_index', 'filtered_total_card', 'simple', $selectedExpensesTotalVariant, 'Activo en Gastos', 'Usar en Gastos') ?><?= $componentVariantAction('pagos_index', 'filtered_total_card', 'simple', $selectedPaymentsTotalVariant, 'Activo en Pagos', 'Usar en Pagos') ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Balance detallado</span><small>Cuatro datos</small></div><?= view('components/cards/resumen', ['variant' => 'detail', 'titulo' => 'Total filtrado', 'monto' => 898212, 'detalle' => 'Suma de gastos filtrados', 'color' => 'text-primary', 'secundarios' => [['label' => 'Registros', 'value' => '18'], ['label' => 'Periodo', 'value' => 'Abril'], ['label' => 'Filtro', 'value' => 'Activo']]]) ?><?= $componentVariantAction('gastos_index', 'filtered_total_card', 'detail', $selectedExpensesTotalVariant, 'Activo en Gastos', 'Usar en Gastos') ?><?= $componentVariantAction('pagos_index', 'filtered_total_card', 'detail', $selectedPaymentsTotalVariant, 'Activo en Pagos', 'Usar en Pagos') ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Comparativo</span><small>Gastos vs pagos</small></div><?= view('components/cards/resumen', ['variant' => 'compare', 'titulo' => 'Total filtrado', 'monto' => 898212, 'detalle' => 'Filtro aplicado', 'color' => 'text-primary']) ?><?= $componentVariantAction('gastos_index', 'filtered_total_card', 'compare', $selectedExpensesTotalVariant, 'Activo en Gastos', 'Usar en Gastos') ?><?= $componentVariantAction('pagos_index', 'filtered_total_card', 'compare', $selectedPaymentsTotalVariant, 'Activo en Pagos', 'Usar en Pagos') ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Veloc&iacute;metro actual</span><small>Aguja semicircular</small></div><?= view('components/widgets/velocimetro_aporte', ['variant' => 'semicircle', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723]) ?><?= $gaugeVariantAction('semicircle', $selectedGaugeVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Dial compacto</span><small>Score central</small></div><?= view('components/widgets/velocimetro_aporte', ['variant' => 'compact_dial', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723]) ?><?= $gaugeVariantAction('compact_dial', $selectedGaugeVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Barra de escala</span><small>Lectura horizontal</small></div><?= view('components/widgets/velocimetro_aporte', ['variant' => 'scale_bar', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723]) ?><?= $gaugeVariantAction('scale_bar', $selectedGaugeVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Arco por tramos</span><small>Escala 0/25/50/75/100</small></div><?= view('components/widgets/velocimetro_aporte', ['variant' => 'segmented_arc', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723]) ?><?= $gaugeVariantAction('segmented_arc', $selectedGaugeVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Dona segmentada</span><small>Dial con zonas de color</small></div><?= view('components/widgets/velocimetro_aporte', ['variant' => 'segmented_donut', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723]) ?><?= $gaugeVariantAction('segmented_donut', $selectedGaugeVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Medialuna limpia</span><small>Escala numerada amplia</small></div><?= view('components/widgets/velocimetro_aporte', ['variant' => 'clean_arc', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723]) ?><?= $gaugeVariantAction('clean_arc', $selectedGaugeVariant) ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Aro con hitos</span><small>Importes clave visibles</small></div><?= view('components/widgets/velocimetro_aporte', ['variant' => 'milestone_ring', 'porcentaje' => 55, 'pagado' => 860000, 'total' => 1563723]) ?><?= $gaugeVariantAction('milestone_ring', $selectedGaugeVariant) ?></article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head"><div><h5>Medio de cobro</h5><p>Usado en Mis medios de cobro. Debe facilitar copiar datos y entender el favorito.</p></div><span class="badge bg-info text-dark">Cobro</span></div>
        <div class="catalog-grid">
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Compacta operativa</span><small>Listado eficiente</small></div><?= view('components/cards/medio_cobro', ['variant' => 'compact', 'nombre' => 'CBU Santander', 'activo' => true, 'favorito' => false, 'titular' => 'Fernando Montes de Oca', 'alias' => 'fer.santander']) ?><?= $componentVariantAction('mis_medios_cobro', 'payment_method_card', 'compact', $selectedPaymentMethodVariant, 'Activo en Medios', 'Usar en Medios') ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Favorita destacada</span><small>Medio principal</small></div><?= view('components/cards/medio_cobro', ['variant' => 'favorite', 'nombre' => 'Mercado Pago', 'activo' => true, 'favorito' => true, 'titular' => 'Fernando', 'alias' => 'fer.mp']) ?><?= $componentVariantAction('mis_medios_cobro', 'payment_method_card', 'favorite', $selectedPaymentMethodVariant, 'Activo en Medios', 'Usar en Medios') ?></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Credencial actual</span><small>Azul, favorita y configuraci&oacute;n</small></div><?= view('components/cards/medio_cobro', ['variant' => 'bank_card', 'nombre' => 'Banco Galicia', 'activo' => true, 'favorito' => true, 'titular' => 'Fernando Montes de Oca', 'alias' => 'fernando.montesdeoca']) ?><?= $componentVariantAction('mis_medios_cobro', 'payment_method_card', 'bank_card', $selectedPaymentMethodVariant, 'Activo en Medios', 'Usar en Medios') ?></article>
        </div>
    </section>
</div>

<?= view('partials/_footer') ?>


