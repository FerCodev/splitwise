<?= view('partials/_head', ['title' => 'SplitWise - Reportes']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Reportes']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0 d-none d-md-block">Reportes</h2>
            <div class="d-flex gap-1">
                <a href="<?= base_url('reportes/exportar-pdf') ?>" class="btn btn-danger btn-sm">PDF</a>
                <a href="<?= base_url('reportes/exportar?' . http_build_query($filters)) ?>" class="btn btn-success btn-sm">CSV</a>
            </div>
        </div>

        <!-- Resumen mensual -->
        <div class="row g-2 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #fff 100%);">
                    <div class="card-body py-3 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width:40px;height:40px;background:#dbeafe;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#2563eb" viewBox="0 0 16 16"><path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5h-2v12h2V2zm-4-2v2h2V2H9zm-4 4v2h2V7H5zm-4 4v2h2v-2H1z"/></svg>
                        </div>
                        <div class="small text-muted">Gastado este mes</div>
                        <div class="fw-bold fs-5 text-primary">$<?= number_format($resumenMensual['total_gastado'], 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);">
                    <div class="card-body py-3 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width:40px;height:40px;background:#dcfce7;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#16a34a" viewBox="0 0 16 16"><path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V3zm1 0v10h12V3H2zm10.5 5.5a.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0 0 1h7a.5.5 0 0 0 .5-.5zm0 3a.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0 0 1h7a.5.5 0 0 0 .5-.5z"/></svg>
                        </div>
                        <div class="small text-muted">Pagado este mes</div>
                        <div class="fw-bold fs-5 text-success">$<?= number_format($resumenMensual['total_pagado'], 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, <?= $resumenMensual['saldo'] >= 0 ? '#f0fdf4' : '#fef2f2' ?> 0%, #fff 100%);">
                    <div class="card-body py-3 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width:40px;height:40px;background:<?= $resumenMensual['saldo'] >= 0 ? '#dcfce7' : '#fee2e2' ?>;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="<?= $resumenMensual['saldo'] >= 0 ? '#16a34a' : '#dc2626' ?>" viewBox="0 0 16 16"><path d="M8 1a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13zM0 7.5A8 8 0 1 1 16 7.5a8 8 0 0 1-16 0zM8 3a.5.5 0 0 1 .5.5v4h4a.5.5 0 0 1 0 1h-4v4a.5.5 0 0 1-1 0v-4h-4a.5.5 0 0 1 0-1h4v-4A.5.5 0 0 1 8 3z"/></svg>
                        </div>
                        <div class="small text-muted">Saldo del mes</div>
                        <div class="fw-bold fs-5 <?= $resumenMensual['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">$<?= number_format(abs($resumenMensual['saldo']), 0) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);">
                    <div class="card-body py-3 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-2" style="width:40px;height:40px;background:#e2e8f0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="#475569" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.26.26 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.27.27 0 0 1-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.5 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/></svg>
                        </div>
                        <div class="small text-muted">Grupos activos</div>
                        <div class="fw-bold fs-5 text-secondary"><?= $resumenMensual['grupos_activos'] ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Top grupos -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:28px;height:28px;background:#dbeafe;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#2563eb" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.26.26 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.27.27 0 0 1-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.5 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/></svg>
                            </div>
                            <h5 class="mb-0 fw-bold">Top grupos por gasto</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($topGrupos)): ?>
                            <p class="text-muted small p-3 mb-0">Sin actividad este mes.</p>
                        <?php else: ?>
                            <?php foreach ($topGrupos as $i => $g): ?>
                                <div class="mobile-card-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-muted" style="min-width:20px;"><?= $i + 1 ?></span>
                                            <div>
                                                <a href="<?= base_url('grupos/' . $g['id'] . '/reportes') ?>" class="fw-medium text-decoration-none"><?= esc($g['nombre']) ?></a>
                                                <span class="text-muted small ms-2"><?= $g['cantidad'] ?> gasto(s)</span>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-primary">$<?= number_format($g['total'], 0) ?></span>
                                    </div>
                                    <div class="progress mt-1" style="height:4px">
                                        <div class="progress-bar bg-primary" style="width:<?= ($i === 0 && $topGrupos[0]['total'] > 0) ? ($g['total'] / $topGrupos[0]['total'] * 100) : ($topGrupos[0]['total'] > 0 ? $g['total'] / $topGrupos[0]['total'] * 100 : 0) ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top categorias -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:28px;height:28px;background:#fef3c7;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#d97706" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.393-.13-.803-.264-1.186-.406-1.078-.401-1.868-.465-2.604-.138v-6.972c0-.507-.133-.954-.4-1.358a1.2 1.2 0 0 0-.479-.458c-.295-.166-.651-.19-1.078-.093-.47.106-.896.336-1.356.628V2.828zm0 10.344c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v.174c-.393.13-.803.264-1.186.406-1.078.401-1.868.465-2.604.138a6.017 6.017 0 0 1-2.71-.576v-.174z"/></svg>
                            </div>
                            <h5 class="mb-0 fw-bold">Top categor&iacute;as</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($topCategorias)): ?>
                            <p class="text-muted small p-3 mb-0">Sin gastos categorizados este mes.</p>
                        <?php else: ?>
                            <?php foreach ($topCategorias as $i => $c): ?>
                                <div class="mobile-card-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold text-muted" style="min-width:20px;"><?= $i + 1 ?></span>
                                            <div>
                                                <span class="badge bg-light text-dark"><?= esc($c['categoria']) ?></span>
                                                <span class="text-muted small ms-2"><?= $c['cantidad'] ?></span>
                                            </div>
                                        </div>
                                        <span class="fw-medium">$<?= number_format($c['total'], 0) ?></span>
                                    </div>
                                    <div class="progress mt-1" style="height:4px">
                                        <div class="progress-bar bg-warning" style="width:<?= ($topCategorias[0]['total'] > 0) ? ($c['total'] / $topCategorias[0]['total'] * 100) : 0 ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Ultimos movimientos -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:28px;height:28px;background:#dbeafe;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#2563eb" viewBox="0 0 16 16"><path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5h-2v12h2V2zm-4-2v2h2V2H9zm-4 4v2h2V7H5zm-4 4v2h2v-2H1z"/></svg>
                            </div>
                            <h5 class="mb-0 fw-bold">&Uacute;ltimos movimientos</h5>
                        </div>
                        <button type="button" class="btn btn-primary feed-filter-btn" data-bs-toggle="modal" data-bs-target="#reportFilterModal" aria-label="Filtros">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.708l4 4.646V14l2-.667V7.854l4-4.646V2z"/></svg>
                        </button>
                    </div>
                    <div class="card-body report-movement-list">
                        <?php if (empty($movimientos)): ?>
                            <p class="text-muted small p-3 mb-0">No hay movimientos recientes.</p>
                        <?php else: ?>
                            <?php foreach ($movimientos as $m): ?>
                                <a href="<?= base_url(($m['tipo'] === 'gasto' ? 'gastos/' : 'pagos/') . $m['id']) ?>" class="report-movement-link">
                                <div class="report-movement-card report-movement-<?= $m['tipo'] === 'gasto' ? 'expense' : 'payment' ?>" style="border-left: 3px solid <?= $m['tipo'] === 'gasto' ? '#2563eb' : '#16a34a' ?>;">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>"><?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                            <span class="fw-medium small ms-1"><?= esc(mb_substr($m['descripcion'], 0, 30)) ?></span>
                                        </div>
                                        <span class="fw-bold small <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?>">$<?= number_format($m['monto'], 0) ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot; <?= esc($m['persona']) ?> &middot; <?= esc($m['grupo']) ?>
                                    </div>
                                </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pagos pendientes -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:28px;height:28px;background:#fee2e2;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#dc2626" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Zm-9 2a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1H6Z"/></svg>
                            </div>
                            <h5 class="mb-0 fw-bold">Pagos pendientes</h5>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($deudas)): ?>
                            <p class="text-muted small p-3 mb-0">No hay deudas pendientes. Est&aacute;s al d&iacute;a.</p>
                        <?php else: ?>
                            <?php foreach ($deudas as $d): ?>
                                <div class="mobile-card-item" style="border-left: 3px solid #dc2626;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= esc($d['deudor']) ?></strong>
                                            <span class="text-muted"> le debe a </span>
                                            <strong><?= esc($d['acreedor']) ?></strong>
                                        </div>
                                        <div class="fw-bold text-danger">$<?= number_format($d['monto'], 0) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Por categoria (tabla desktop) -->
        <?php if (!empty($porCategoria)): ?>
        <div class="card border-0 shadow-sm report-card mb-4 d-none d-md-block">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Detalle por categor&iacute;a</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Categor&iacute;a</th><th class="text-end">Cantidad</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($porCategoria as $c): ?>
                            <tr><td><span class="badge bg-light text-dark"><?= esc($c['categoria']) ?></span></td><td class="text-end"><?= $c['cantidad'] ?></td><td class="text-end fw-medium">$<?= number_format($c['total'], 2) ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Por grupo (tabla desktop) -->
        <?php if (!empty($porGrupo)): ?>
        <div class="card border-0 shadow-sm report-card mb-4 d-none d-md-block">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Detalle por grupo</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Grupo</th><th class="text-end">Cantidad</th><th class="text-end">Total</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($porGrupo as $g): ?>
                            <tr><td class="fw-medium"><?= esc($g['grupo']) ?></td><td class="text-end"><?= $g['cantidad'] ?></td><td class="text-end fw-medium">$<?= number_format($g['total'], 2) ?></td></tr>
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
                        <h5 class="modal-title fw-bold" id="reportFilterModalLabel">Filtrar reportes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Los filtros aplican al detalle y exportaci&oacute;n. El resumen mensual siempre muestra el mes actual.</p>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Grupo</label>
                            <select name="grupo_id" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($grupos as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ($filters['grupo_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-medium">Categor&iacute;a</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-medium">Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-medium">Hasta</label>
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
