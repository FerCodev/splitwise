<?php
    $mesReporte = $resumenMensual['mes'] ?? ($filters['year_month'] ?? date('Y-m'));
    $meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
    ];
    $mesFecha = $mesReporte ? DateTimeImmutable::createFromFormat('!Y-m', $mesReporte) : false;
    $mesReporteTexto = $mesFecha
        ? $meses[(int) $mesFecha->format('n')] . ' ' . $mesFecha->format('Y')
        : 'Seleccionar periodo';
    $csvUrl = base_url('reportes/exportar?' . http_build_query($filters));
    $pdfUrl = base_url('reportes/exportar-pdf?' . http_build_query($filters));
    $saldo = (float) ($resumenMensual['saldo'] ?? 0);

    // Contar filtros avanzados activos (excluyendo el mes)
    $activeCount = 0;
    $activeChips = [];

    if (!empty($filters['grupo_id'])) {
        $activeCount++;
        $grupoNombre = '';
        foreach ($grupos as $g) {
            if ((string) $g['id'] === (string) $filters['grupo_id']) { $grupoNombre = $g['nombre']; break; }
        }
        $activeChips[] = ['label' => 'Grupo', 'value' => $grupoNombre ?: '#' . $filters['grupo_id']];
    }
    if (!empty($filters['categoria_id'])) {
        $activeCount++;
        $catNombre = '';
        foreach ($categorias as $c) {
            if ((string) $c['id'] === (string) $filters['categoria_id']) { $catNombre = $c['nombre']; break; }
        }
        $activeChips[] = ['label' => 'Categoría', 'value' => $catNombre ?: '#' . $filters['categoria_id']];
    }
    if (!empty($filters['fecha_desde'])) {
        $activeCount++;
        $activeChips[] = ['label' => 'Desde', 'value' => date('d/m/Y', strtotime($filters['fecha_desde']))];
    }
    if (!empty($filters['fecha_hasta'])) {
        $activeCount++;
        $activeChips[] = ['label' => 'Hasta', 'value' => date('d/m/Y', strtotime($filters['fecha_hasta']))];
    }

    $fechaDesdeVal = esc($filters['fecha_desde'] ?? '');
    $fechaHastaVal = esc($filters['fecha_hasta'] ?? '');
    $grupoSelected = esc($filters['grupo_id'] ?? '');
    $catSelected = esc($filters['categoria_id'] ?? '');
    $yearMonthVal = esc($filters['year_month'] ?? $mesReporte);
    $hasDateRange = !empty($filters['fecha_desde']) || !empty($filters['fecha_hasta']);

    ob_start();
?>
<div class="dropdown">
    <button type="button" class="mobile-topbar-action" data-bs-toggle="dropdown"
            aria-expanded="false" aria-label="Exportar reporte" title="Exportar reporte">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 3v12m0 0 5-5m-5 5-5-5M5 14v5h14v-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
    <ul class="dropdown-menu dropdown-menu-end report-export-menu">
        <li>
            <a class="dropdown-item report-export-item" href="<?= $pdfUrl ?>">
                <span class="report-export-item-icon report-export-item-icon-pdf" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M4 0h5.5L14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2Zm5 1.5V5h3.5L9 1.5Z"/><path d="M4.5 8.25h2a1.5 1.5 0 0 1 0 3h-.75V13h-1.25V8.25Zm1.25 1v1h.65a.5.5 0 0 0 0-1h-.65ZM8 8.25h1.65A2.35 2.35 0 0 1 12 10.6 2.4 2.4 0 0 1 9.6 13H8V8.25Zm1.25 1V12h.35a1.35 1.35 0 0 0 0-2.75h-.35Z"/></svg>
                </span>
                <span><strong>Exportar PDF</strong><small>Documento para imprimir</small></span>
            </a>
        </li>
        <li>
            <a class="dropdown-item report-export-item" href="<?= $csvUrl ?>">
                <span class="report-export-item-icon report-export-item-icon-csv" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path d="M1 3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V3Zm1 2v3h3V5H2Zm4 0v3h4V5H6Zm5 0v3h3V5h-3ZM2 9v3h3V9H2Zm4 0v3h4V9H6Zm5 0v3h3V9h-3Z"/></svg>
                </span>
                <span><strong>Exportar CSV</strong><small>Datos para Excel</small></span>
            </a>
        </li>
    </ul>
</div>
<?php
    $mobileTopbarActions = ob_get_clean();
?>
<?= view('partials/_head', ['title' => 'Gastito - Reportes']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Reportes', 'mobileTopbarActions' => $mobileTopbarActions]) ?>

<div class="container mt-3 mt-md-4">

    <!-- Selector compacto de periodo y acciones -->
    <div class="report-toolbar">
        <button type="button" class="report-period-trigger"
                data-bs-toggle="offcanvas" data-bs-target="#reportFilterPanel"
                aria-label="Filtrar reportes para <?= esc($mesReporteTexto) ?>" id="reportFilterBtn">
            <span class="report-period-value"><?= esc($mesReporteTexto) ?></span>
            <span class="report-period-filter" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/>
                </svg>
                <?php if ($activeCount > 0): ?>
                    <span class="report-filter-badge"><?= $activeCount ?></span>
                <?php endif; ?>
            </span>
        </button>

        <div class="dropdown d-none d-lg-block">
            <button type="button" class="report-export-button" data-bs-toggle="dropdown"
                    aria-expanded="false" aria-label="Exportar reporte">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                <span>Exportar</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= $pdfUrl ?>">Exportar PDF</a></li>
                <li><a class="dropdown-item" href="<?= $csvUrl ?>">Exportar CSV</a></li>
            </ul>
        </div>
    </div>
    <!-- Chips de filtros activos -->
    <?php if (!empty($activeChips)): ?>
    <div class="report-active-chips">
        <?php foreach ($activeChips as $chip): ?>
        <span class="report-chip">
            <span class="report-chip-label"><?= esc($chip['label']) ?></span>
            <span class="report-chip-value"><?= esc($chip['value']) ?></span>
        </span>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- KPIs -->
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3"><div class="small text-muted">Total gastado</div><div class="fw-bold fs-5 text-primary"><?= moneda($resumenMensual['total_gastado']) ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3"><div class="small text-muted">Vos pagaste</div><div class="fw-bold fs-5 text-success"><?= moneda($resumenMensual['total_pagado']) ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3"><div class="small text-muted">Tu parte</div><div class="fw-bold fs-5 text-secondary"><?= moneda($resumenMensual['total_consumido']) ?></div></div></div></div>
        <div class="col-6 col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body py-3"><div class="small text-muted">Saldo del periodo</div><div class="fw-bold fs-5 <?= $saldo >= 0 ? 'text-success' : 'text-danger' ?>"><?= moneda(abs($saldo)) ?> <span class="small fw-normal"><?= $saldo > 0 ? 'a favor' : ($saldo < 0 ? 'debe' : 'saldado') ?></span></div></div></div></div>
    </div>

    <!-- Resumenes por grupo, categoria y saldos -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-4">
            <details class="card border-0 shadow-sm report-breakdown-card">
                <summary class="card-header bg-white border-bottom report-breakdown-summary">
                    <span class="report-breakdown-title">Gastitos por grupo</span>
                    <span class="report-breakdown-meta"><span class="text-muted small"><?= (int) ($resumenMensual['grupos_activos'] ?? 0) ?> activo(s)</span><span class="report-breakdown-arrow" aria-hidden="true"></span></span>
                </summary>
                <div class="card-body p-0">
                    <?php if (empty($porGrupo)): ?>
                        <p class="text-muted small p-3 mb-0">Sin gastitos para el periodo.</p>
                    <?php else: ?>
                        <?php $maxGrupo = max(array_map(static fn($g) => (float) $g['total'], $porGrupo)); ?>
                        <?php foreach ($porGrupo as $g): ?>
                            <div class="mobile-card-item">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div class="fw-medium"><?= esc($g['grupo']) ?> <span class="text-muted small"><?= $g['cantidad'] ?> <?= pluralizar((int) $g['cantidad'], 'gastito', 'gastitos') ?></span></div>
                                    <div class="fw-bold text-primary"><?= moneda($g['total']) ?></div>
                                </div>
                                <div class="progress mt-2" style="height:4px"><div class="progress-bar bg-primary" style="width:<?= $maxGrupo > 0 ? ($g['total'] / $maxGrupo * 100) : 0 ?>%"></div></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </details>
        </div>

        <div class="col-12 col-lg-4">
            <details class="card border-0 shadow-sm report-breakdown-card">
                <summary class="card-header bg-white border-bottom report-breakdown-summary">
                    <span class="report-breakdown-title">Gastitos por categoría</span>
                    <span class="report-breakdown-arrow" aria-hidden="true"></span>
                </summary>
                <div class="card-body p-0">
                    <?php if (empty($porCategoria)): ?>
                        <p class="text-muted small p-3 mb-0">Sin categorias para el periodo.</p>
                    <?php else: ?>
                        <?php $maxCategoria = max(array_map(static fn($c) => (float) $c['total'], $porCategoria)); ?>
                        <?php foreach ($porCategoria as $c): ?>
                            <div class="mobile-card-item">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div><span class="badge bg-light text-dark"><?= esc($c['categoria']) ?></span> <span class="text-muted small"><?= $c['cantidad'] ?> <?= pluralizar((int) $c['cantidad'], 'gastito', 'gastitos') ?></span></div>
                                    <div class="fw-bold"><?= moneda($c['total']) ?></div>
                                </div>
                                <div class="progress mt-2" style="height:4px"><div class="progress-bar bg-warning" style="width:<?= $maxCategoria > 0 ? ($c['total'] / $maxCategoria * 100) : 0 ?>%"></div></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </details>
        </div>

        <div class="col-12 col-lg-4">
            <details class="card border-0 shadow-sm report-breakdown-card">
                <summary class="card-header bg-white border-bottom report-breakdown-summary">
                    <span class="report-breakdown-title">Saldos actuales</span>
                    <span class="report-breakdown-arrow" aria-hidden="true"></span>
                </summary>
                <div class="card-body report-debt-list">
                    <?php if (empty($deudas)): ?>
                        <p class="text-muted small mb-0">No hay deudas pendientes.</p>
                    <?php else: ?>
                        <?php foreach ($deudas as $d): ?>
                            <div class="report-debt-item">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <div><strong><?= esc($d['deudor']) ?></strong><span class="text-muted"> le debe a </span><strong><?= esc($d['acreedor']) ?></strong><?php if (!empty($d['grupo'])): ?><div class="text-muted small"><?= esc($d['grupo']) ?></div><?php endif; ?></div>
                                    <div class="fw-bold text-danger"><?= moneda($d['monto']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </details>
        </div>
    </div>

    <!-- Movimientos -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0 fw-bold">Movimientos del periodo</h5></div>
                <div class="card-body report-movement-list">
                    <?php if (empty($movimientos)): ?>
                        <p class="text-muted small p-3 mb-0">No hay movimientos para el periodo.</p>
                    <?php else: ?>
                        <?php foreach ($movimientos as $m): ?>
                            <?= view('components/cards/movimiento', ['tipo' => $m['tipo'], 'descripcion' => mb_substr((string) $m['descripcion'], 0, 40), 'monto' => $m['monto'], 'fecha' => $m['fecha'], 'persona' => $m['persona'], 'grupo' => $m['grupo'] ?? null, 'url' => base_url(($m['tipo'] === 'gasto' ? 'gastos/' : 'pagos/') . $m['id'])]) ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel de filtros offcanvas -->
<div class="offcanvas offcanvas-bottom report-filter-panel" tabindex="-1" id="reportFilterPanel"
     aria-labelledby="reportFilterPanelLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="reportFilterPanelLabel">Filtros</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        <form method="get" id="reportFilterForm">
            <div class="mb-3">
                <label for="report_mes" class="form-label small fw-medium">Mes</label>
                <input type="month" id="report_mes" name="year_month" class="form-control" value="<?= $yearMonthVal ?>">
            </div>
            <div class="mb-3">
                <label for="report_grupo" class="form-label small fw-medium">Grupo</label>
                <select id="report_grupo" name="grupo_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($grupos as $g): ?>
                    <option value="<?= $g['id'] ?>" <?= $grupoSelected == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="report_categoria" class="form-label small fw-medium">Categoría</label>
                <select id="report_categoria" name="categoria_id" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $catSelected == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <details class="report-date-range" <?= $hasDateRange ? 'open' : '' ?>>
                <summary>
                    <span>Rango de fechas</span>
                    <span class="report-date-range-optional">Opcional</span>
                </summary>
                <div class="row g-2">
                    <div class="col-6">
                        <label for="report_desde" class="form-label small fw-medium">Desde</label>
                        <input type="date" id="report_desde" name="fecha_desde" class="form-control" value="<?= $fechaDesdeVal ?>">
                    </div>
                    <div class="col-6">
                        <label for="report_hasta" class="form-label small fw-medium">Hasta</label>
                        <input type="date" id="report_hasta" name="fecha_hasta" class="form-control" value="<?= $fechaHastaVal ?>">
                    </div>
                </div>
            </details>
        </form>
    </div>
    <div class="offcanvas-footer border-top p-3 d-flex gap-2">
        <a href="<?= base_url('reportes') ?>" class="btn btn-outline-secondary flex-fill">Limpiar</a>
        <button type="submit" form="reportFilterForm" class="btn btn-primary flex-fill">Aplicar</button>
    </div>
</div>

<script>
(function () {
    var panel = document.getElementById('reportFilterPanel');
    var btn = document.getElementById('reportFilterBtn');

    if (panel && btn) {
        panel.addEventListener('hidden.bs.offcanvas', function () {
            btn.focus();
        });
    }
})();
</script>

<?= view('partials/_footer') ?>
