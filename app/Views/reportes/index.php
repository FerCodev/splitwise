<?= view('partials/_head', ['title' => 'SplitWise - Reportes']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Reportes</h2>
            <div class="d-flex gap-1">
                <a href="<?= base_url('reportes/exportar-pdf') ?>" class="btn btn-danger btn-sm">PDF</a>
                <a href="<?= base_url('reportes/exportar?' . http_build_query($filters)) ?>" class="btn btn-success btn-sm">CSV</a>
            </div>
        </div>

        <!-- Resumen mensual -->
        <div class="row g-2 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="small text-muted">Gastado este mes</div>
                    <div class="fw-bold fs-5 text-primary">$<?= number_format($resumenMensual['total_gastado'], 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="small text-muted">Pagado este mes</div>
                    <div class="fw-bold fs-5 text-success">$<?= number_format($resumenMensual['total_pagado'], 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="small text-muted">Saldo del mes</div>
                    <div class="fw-bold fs-5 <?= $resumenMensual['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">$<?= number_format(abs($resumenMensual['saldo']), 0) ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="small text-muted">Grupos activos</div>
                    <div class="fw-bold fs-5 text-secondary"><?= $resumenMensual['grupos_activos'] ?></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Top grupos -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Top grupos por gasto</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($topGrupos)): ?>
                            <p class="text-muted small p-3 mb-0">Sin actividad este mes.</p>
                        <?php else: ?>
                            <?php foreach ($topGrupos as $i => $g): ?>
                                <div class="mobile-card-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="<?= base_url('grupos/' . $g['id'] . '/reportes') ?>" class="fw-medium text-decoration-none"><?= esc($g['nombre']) ?></a>
                                            <span class="text-muted small ms-2"><?= $g['cantidad'] ?> gasto(s)</span>
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
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Top categor&iacute;as</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($topCategorias)): ?>
                            <p class="text-muted small p-3 mb-0">Sin gastos categorizados este mes.</p>
                        <?php else: ?>
                            <?php foreach ($topCategorias as $i => $c): ?>
                                <div class="mobile-card-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-light text-dark"><?= esc($c['categoria']) ?></span>
                                            <span class="text-muted small ms-2"><?= $c['cantidad'] ?></span>
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
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">&Uacute;ltimos movimientos</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($movimientos)): ?>
                            <p class="text-muted small p-3 mb-0">No hay movimientos recientes.</p>
                        <?php else: ?>
                            <?php foreach ($movimientos as $m): ?>
                                <div class="mobile-card-item">
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
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pagos pendientes -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Pagos pendientes</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($deudas)): ?>
                            <p class="text-muted small p-3 mb-0">No hay deudas pendientes. Est&aacute;s al d&iacute;a.</p>
                        <?php else: ?>
                            <?php foreach ($deudas as $d): ?>
                                <div class="mobile-card-item">
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

        <!-- Filtros (secundarios) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Filtros</h5>
                <span class="small text-muted d-none d-md-inline">Los filtros aplican al detalle y exportaci&oacute;n. El resumen mensual siempre muestra el mes actual.</span>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-medium">Grupo</label>
                        <select name="grupo_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= ($filters['grupo_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Categor&iacute;a</label>
                        <select name="categoria_id" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-1">
                        <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
                        <a href="<?= base_url('reportes') ?>" class="btn btn-secondary flex-fill">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Por categoria (tabla desktop) -->
        <?php if (!empty($porCategoria)): ?>
        <div class="card border-0 shadow-sm mb-4 d-none d-md-block">
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
        <div class="card border-0 shadow-sm mb-4 d-none d-md-block">
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

<?= view('partials/_footer') ?>
