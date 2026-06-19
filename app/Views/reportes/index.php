<?= view('partials/_head', ['title' => 'SplitWise - Reportes']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Reportes']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0 d-none d-md-block">Reportes</h2>
            <div class="d-flex gap-1">
                <a href="<?= base_url('reportes/exportar-pdf') ?>" class="btn btn-danger btn-sm">PDF</a>
                <a href="<?= base_url('reportes/exportar?' . http_build_query($filters)) ?>" class="btn btn-success btn-sm">CSV</a>
            </div>
        </div>

        <!-- Resumen mensual - compact strip -->
        <div class="balance-strip mb-3">
            <div class="balance-strip-label">Resumen del mes</div>
            <div class="balance-strip-amount <?= $resumenMensual['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                $<?= number_format(abs($resumenMensual['saldo']), 2) ?>
                <span style="font-size:14px;font-weight:600;" class="text-muted"><?= $resumenMensual['saldo'] >= 0 ? 'saldo a favor' : 'saldo en contra' ?></span>
            </div>
            <div class="balance-strip-detail">
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Gastado:</span>
                    <span class="financial-amount text-primary">$<?= number_format($resumenMensual['total_gastado'], 2) ?></span>
                </div>
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Pagado:</span>
                    <span class="financial-amount text-success">$<?= number_format($resumenMensual['total_pagado'], 2) ?></span>
                </div>
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Grupos:</span>
                    <span class="financial-amount"><?= $resumenMensual['grupos_activos'] ?></span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <!-- Top grupos -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Top grupos por gasto</h5>
                    </div>
                    <?php if (empty($topGrupos)): ?>
                        <div class="empty-state" style="padding:24px;">
                            <div class="empty-state-text">Sin actividad este mes.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topGrupos as $i => $g): ?>
                            <a href="<?= base_url('grupos/' . $g['id'] . '/reportes') ?>" class="financial-list-item financial-list-item-clickable text-decoration-none" style="color:inherit;">
                                <div style="width:24px;text-align:center;font-size:13px;font-weight:700;color:var(--muted);"><?= $i + 1 ?></div>
                                <div class="financial-list-item-info">
                                    <div class="financial-list-item-title" style="font-size:14px;"><?= esc($g['nombre']) ?></div>
                                    <div class="financial-list-item-subtitle"><?= $g['cantidad'] ?> gasto(s)</div>
                                </div>
                                <div style="flex:1;max-width:80px;">
                                    <div class="progress" style="height:4px;">
                                        <div class="progress-bar" style="width:<?= ($i === 0 && $topGrupos[0]['total'] > 0) ? ($g['total'] / $topGrupos[0]['total'] * 100) : ($topGrupos[0]['total'] > 0 ? $g['total'] / $topGrupos[0]['total'] * 100 : 0) ?>%;background:var(--primary);"></div>
                                    </div>
                                </div>
                                <div class="financial-list-item-amount text-primary">$<?= number_format($g['total'], 0) ?></div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top categorias -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Top categor&iacute;as</h5>
                    </div>
                    <?php if (empty($topCategorias)): ?>
                        <div class="empty-state" style="padding:24px;">
                            <div class="empty-state-text">Sin gastos categorizados este mes.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($topCategorias as $i => $c): ?>
                            <div class="financial-list-item">
                                <div style="width:24px;text-align:center;font-size:13px;font-weight:700;color:var(--muted);"><?= $i + 1 ?></div>
                                <div class="financial-list-item-info">
                                    <div class="financial-list-item-title" style="font-size:14px;">
                                        <span class="badge bg-secondary"><?= esc($c['categoria']) ?></span>
                                    </div>
                                    <div class="financial-list-item-subtitle"><?= $c['cantidad'] ?> gasto(s)</div>
                                </div>
                                <div style="flex:1;max-width:80px;">
                                    <div class="progress" style="height:4px;">
                                        <div class="progress-bar" style="width:<?= ($topCategorias[0]['total'] > 0) ? ($c['total'] / $topCategorias[0]['total'] * 100) : 0 ?>%;background:var(--warning);"></div>
                                    </div>
                                </div>
                                <div class="financial-list-item-amount">$<?= number_format($c['total'], 0) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <!-- Ultimos movimientos -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">&Uacute;ltimos movimientos</h5>
                        <button type="button" class="btn btn-primary feed-filter-btn btn-sm" data-bs-toggle="modal" data-bs-target="#reportFilterModal" aria-label="Filtros">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.708l4 4.646V14l2-.667V7.854l4-4.646V2z"/></svg>
                        </button>
                    </div>
                    <?php if (empty($movimientos)): ?>
                        <div class="empty-state" style="padding:24px;">
                            <div class="empty-state-text">No hay movimientos recientes.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($movimientos as $m): ?>
                            <a href="<?= base_url(($m['tipo'] === 'gasto' ? 'gastos/' : 'pagos/') . $m['id']) ?>" class="report-movement-link">
                                <div class="financial-list-item financial-list-item-clickable report-movement-card report-movement-<?= $m['tipo'] === 'gasto' ? 'expense' : 'payment' ?>" style="color:inherit;">
                                    <div class="financial-list-item-info">
                                        <div class="financial-list-item-title" style="font-size:14px;">
                                            <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>" style="font-size:10px;padding:2px 6px;margin-right:4px;"><?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                            <?= esc(mb_substr($m['descripcion'], 0, 30)) ?>
                                        </div>
                                        <div class="financial-list-item-subtitle">
                                            <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot; <?= esc($m['persona']) ?> &middot; <?= esc($m['grupo']) ?>
                                        </div>
                                    </div>
                                    <div class="financial-list-item-amount <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?>">
                                        $<?= number_format($m['monto'], 2) ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pagos pendientes -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Pagos pendientes</h5>
                    </div>
                    <?php if (empty($deudas)): ?>
                        <div class="empty-state" style="padding:24px;">
                            <span class="status-dot status-dot-active" style="width:10px;height:10px;margin:0 auto 8px;display:block;"></span>
                            <div class="empty-state-text">No hay deudas pendientes. Est&aacute;s al d&iacute;a.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($deudas as $d): ?>
                            <div class="financial-list-item" style="border-left:3px solid var(--danger);">
                                <div class="financial-list-item-info">
                                    <div class="financial-list-item-title" style="font-size:14px;">
                                        <strong><?= esc($d['deudor']) ?></strong>
                                        <span class="text-muted" style="font-weight:400;"> le debe a </span>
                                        <strong><?= esc($d['acreedor']) ?></strong>
                                    </div>
                                </div>
                                <div class="financial-list-item-amount text-danger">
                                    $<?= number_format($d['monto'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Por categoria (tabla desktop) -->
        <?php if (!empty($porCategoria)): ?>
        <div class="card mb-3 d-none d-md-block">
            <div class="card-header">
                <h5 class="mb-0">Detalle por categor&iacute;a</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Categor&iacute;a</th><th class="text-end">Cantidad</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($porCategoria as $c): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= esc($c['categoria']) ?></span></td>
                                <td class="text-end"><?= $c['cantidad'] ?></td>
                                <td class="text-end financial-amount">$<?= number_format($c['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Por grupo (tabla desktop) -->
        <?php if (!empty($porGrupo)): ?>
        <div class="card mb-3 d-none d-md-block">
            <div class="card-header">
                <h5 class="mb-0">Detalle por grupo</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Grupo</th><th class="text-end">Cantidad</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($porGrupo as $g): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($g['grupo']) ?></td>
                                <td class="text-end"><?= $g['cantidad'] ?></td>
                                <td class="text-end financial-amount">$<?= number_format($g['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <div class="modal fade" id="reportFilterModal" tabindex="-1" aria-labelledby="reportFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="get">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportFilterModalLabel">Filtrar reportes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Los filtros aplican al detalle y exportaci&oacute;n. El resumen mensual siempre muestra el mes actual.</p>
                        <div class="mb-3">
                            <label class="form-label">Grupo</label>
                            <select name="grupo_id" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($grupos as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ($filters['grupo_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categor&iacute;a</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= base_url('reportes') ?>" class="btn btn-secondary flex-fill">Limpiar</a>
                        <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= view('partials/_footer') ?>
