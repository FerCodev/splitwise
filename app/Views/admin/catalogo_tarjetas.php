<?= view('partials/_head', ['title' => 'SplitWise - Cat&aacute;logo de tarjetas']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Cat&aacute;logo Admin']) ?>

<?php
$fechaDemo = '2026-04-15';
$fechaCorta = date('d/m/Y', strtotime($fechaDemo));
$selectedGaugeVariant = $selectedGaugeVariant ?? 'semicircle';
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
            <article class="catalog-variant">
                <div class="catalog-variant-meta">
                    <span>Operativa</span>
                    <small>Acciones directas</small>
                </div>
                <div class="dash-card catalog-preview-card">
                    <div class="dash-card-body">
                        <div class="catalog-row align-items-start">
                            <div class="catalog-avatar catalog-avatar-primary">M</div>
                            <div class="min-width-0 flex-grow-1">
                                <div class="catalog-title-row"><strong>Mayo</strong><span class="badge bg-success">Activo</span></div>
                                <div class="text-muted small">Saldo: <span class="financial-amount text-success"><?= moneda(12500) ?></span></div>
                                <div class="text-muted small mt-1"><span class="badge bg-primary">Gasto</span> Supermercado</div>
                            </div>
                        </div>
                        <div class="catalog-actions mt-3">
                            <button class="btn btn-outline-primary btn-sm" type="button">Entrar</button>
                            <button class="btn btn-primary btn-sm" type="button">+ Gasto</button>
                        </div>
                    </div>
                </div>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta">
                    <span>Balance primero</span>
                    <small>Saldo protagonista</small>
                </div>
                <div class="dash-card catalog-preview-card catalog-balance-first">
                    <div class="dash-card-body text-center">
                        <div class="small text-muted">Mayo &middot; Activo</div>
                        <div class="catalog-balance-amount text-success"><?= moneda(12500) ?></div>
                        <div class="small text-muted">a favor</div>
                        <div class="catalog-actions justify-content-center mt-3">
                            <button class="btn btn-outline-primary btn-sm" type="button">Entrar</button>
                            <button class="btn btn-primary btn-sm" type="button">+ Gasto</button>
                        </div>
                    </div>
                </div>
            </article>
            <article class="catalog-variant">
                <div class="catalog-variant-meta">
                    <span>Resumen compacto</span>
                    <small>Listado denso</small>
                </div>
                <div class="dash-card catalog-preview-card">
                    <a href="#" class="dash-list-item text-decoration-none">
                        <div class="catalog-avatar catalog-avatar-primary catalog-avatar-sm">M</div>
                        <div class="dash-list-item-info">
                            <div class="dash-list-item-title">Mayo <span class="badge bg-success ms-1">Activo</span></div>
                            <div class="dash-list-item-subtitle">Ultimo: supermercado mensual</div>
                        </div>
                        <div class="dash-list-item-amount text-success"><?= moneda(12500) ?></div>
                    </a>
                </div>
            </article>
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
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Alerta actual</span><small>Rojo suave sin mezcla azul</small></div><div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card home-debt-card home-debt-card-owes"><div class="catalog-card-top"><div><span class="badge me-1">Deb&eacute;s</span><span class="fw-medium small">Antonella</span></div><span class="fw-bold small text-danger"><?= moneda(8500) ?></span></div><div class="text-muted small mt-1">Mayo &middot; Pago pendiente</div></div></a></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Acci&oacute;n directa</span><small>CTA visible</small></div><div class="dash-card catalog-preview-card catalog-soft-danger"><div class="dash-card-body"><div class="catalog-card-top"><div><span class="badge bg-danger">Deb&eacute;s</span><div class="fw-semibold mt-1">Antonella</div></div><span class="financial-amount text-danger"><?= moneda(8500) ?></span></div><button class="btn btn-danger btn-sm w-100 mt-3" type="button">Pagar</button></div></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Resumen persona</span><small>Agrupa movimientos</small></div><div class="dash-card catalog-preview-card"><div class="dash-card-body"><div class="catalog-row"><div class="catalog-avatar catalog-avatar-success catalog-avatar-sm">A</div><div class="flex-grow-1"><strong>Antonella</strong><div class="text-muted small">2 grupos &middot; 5 movimientos</div></div><span class="financial-amount text-success"><?= moneda(12300) ?></span></div><div class="text-muted small mt-2">Te deben en Mayo y Junio</div></div></div></article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head"><div><h5>Movimiento de grupo</h5><p>Usado dentro del detalle del grupo. Tiene que filtrar, listar y diferenciar gasto/pago.</p></div><span class="badge bg-info text-dark">Movimiento</span></div>
        <div class="catalog-grid">
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Timeline</span><small>Fecha como gu&iacute;a</small></div><div class="dash-card catalog-preview-card catalog-timeline"><div class="dash-card-body"><div class="catalog-timeline-item"><span></span><div><strong>Alquiler</strong><div class="text-muted small">01/04 &middot; Fernando</div></div><b class="text-primary"><?= moneda(85000) ?></b></div><div class="catalog-timeline-item"><span></span><div><strong>Pago</strong><div class="text-muted small">05/04 &middot; Antonella</div></div><b class="text-success"><?= moneda(42500) ?></b></div></div></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Feed</span><small>Como reportes</small></div><div class="card report-card catalog-preview-card"><div class="card-header d-flex justify-content-between align-items-center"><h6 class="fw-bold mb-0">Movimientos</h6><button type="button" class="btn btn-primary feed-filter-btn" aria-label="Filtros"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .39.812L10 7.3V13.5a.5.5 0 0 1-.724.447l-3-1.5A.5.5 0 0 1 6 12V7.3L1.61 1.812A.5.5 0 0 1 1.5 1.5z"/></svg></button></div><div class="card-body report-movement-list"><a href="#" class="report-movement-link"><div class="report-movement-card report-movement-expense catalog-border-primary"><div class="catalog-card-top"><span><span class="badge bg-primary">Gasto</span> Alquiler</span><b class="text-primary"><?= moneda(85000) ?></b></div><div class="text-muted small">01/04/2026 &middot; Fernando</div></div></a></div></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Por usuario</span><small>Color de pagador</small></div><div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card catalog-user-fer"><div class="catalog-card-top"><span><span class="catalog-user-dot catalog-user-dot-fer"></span>Fernando &middot; Gasto</span><b class="text-primary"><?= moneda(6000) ?></b></div><div class="text-muted small">Gasto chico</div></div></a><a href="#" class="report-movement-link"><div class="report-movement-card catalog-user-anto"><div class="catalog-card-top"><span><span class="catalog-user-dot catalog-user-dot-anto"></span>Antonella &middot; Pago</span><b class="text-success"><?= moneda(5000) ?></b></div><div class="text-muted small">Pago parcial</div></div></a></div></article>
        </div>
    </section>

    <section class="catalog-section">
        <div class="catalog-section-head"><div><h5>Total / Resumen</h5><p>Usado en Home, Reportes y tarjetas de totales filtrados.</p></div><span class="badge bg-secondary">Resumen</span></div>
        <div class="catalog-grid">
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>KPI simple</span><small>Monto protagonista</small></div><div class="dash-card catalog-preview-card"><div class="dash-card-body"><div class="balance-strip-label">Total filtrado</div><div class="balance-strip-amount text-primary"><?= moneda(898212) ?></div><div class="text-muted small">Suma de gastos filtrados</div></div></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Balance detallado</span><small>Cuatro datos</small></div><div class="dash-card catalog-preview-card"><div class="dash-card-body catalog-metric-grid"><div><small>Total</small><b><?= moneda(1563723) ?></b></div><div><small>Vos pagaste</small><b class="text-success"><?= moneda(898212) ?></b></div><div><small>Tu parte</small><b><?= moneda(781862) ?></b></div><div><small>Saldo</small><b class="text-success"><?= moneda(116351) ?></b></div></div></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Comparativo</span><small>Gastos vs pagos</small></div><div class="dash-card catalog-preview-card"><div class="dash-card-body"><div class="catalog-compare"><div><span class="status-dot status-dot-danger"></span><small>Gastos</small><b><?= moneda(1630862) ?></b></div><div><span class="status-dot status-dot-active"></span><small>Pagos</small><b><?= moneda(830001) ?></b></div></div><div class="catalog-total-line mt-3"><span>Saldo</span><b class="text-success"><?= moneda(0) ?> saldado</b></div></div></div></article>
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
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Compacta operativa</span><small>Listado eficiente</small></div><div class="dash-card catalog-preview-card catalog-payment-method"><div class="dash-card-body"><div class="catalog-card-top"><div class="catalog-row"><div class="catalog-payment-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm1 2.5V12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6.5H1Z"/></svg></div><div><strong>CBU Santander</strong><div class="text-muted small">Alias: fer.santander <button type="button" class="copiar-icon-btn" aria-label="Copiar alias"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3z"/></svg></button></div></div></div><button type="button" class="medio-fav-btn" aria-label="Marcar favorito"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73z"/></svg></button></div><div class="catalog-actions mt-3"><button class="btn btn-primary btn-sm" type="button">Editar</button><button class="btn btn-outline-secondary btn-sm" type="button">M&aacute;s</button></div></div></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Favorita destacada</span><small>Medio principal</small></div><div class="dash-card catalog-preview-card catalog-payment-favorite"><div class="dash-card-body"><div class="catalog-card-top"><span class="badge bg-success">Favorito</span><button type="button" class="medio-fav-btn medio-fav-active" aria-label="Quitar favorito"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg></button></div><strong class="d-block mt-2">Mercado Pago</strong><div class="catalog-copy-line mt-2"><span>Alias</span><b>fer.mp</b><button type="button" class="copiar-icon-btn" aria-label="Copiar alias"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3z"/></svg></button></div><div class="text-muted small mt-2">Titular: Fernando</div></div></div></article>
            <article class="catalog-variant"><div class="catalog-variant-meta"><span>Credencial actual</span><small>Azul, favorita y configuraci&oacute;n</small></div><div class="payment-bank-card"><div class="payment-bank-card-top"><div class="payment-bank-name">Banco Galicia</div><div class="payment-bank-controls"><span class="badge bg-light text-dark">Activo</span><button type="button" class="payment-bank-fav payment-bank-fav-active" aria-label="Quitar favorito"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg></button><a href="#" class="payment-bank-gear" aria-label="Configurar medio de cobro"><svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.902 3.433 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.892 3.433-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.892-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319z"/></svg></a></div></div><div class="payment-bank-body"><div class="payment-bank-meta payment-bank-meta-primary"><div><span>Titular</span><strong>Fernando Montes de Oca</strong></div><div class="payment-bank-copy"><div><span>Alias</span><strong>fernando.montesdeoca</strong></div><button type="button" class="copiar-icon-btn" aria-label="Copiar alias"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3z"/></svg></button></div></div></div></div></article>
        </div>
    </section>
</div>

<?= view('partials/_footer') ?>
