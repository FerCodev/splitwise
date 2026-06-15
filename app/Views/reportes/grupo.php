<?= view('partials/_head', ['title' => 'SplitWise - Reporte: ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('reportes') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver a reportes</a>

        <h2 class="fw-bold mb-1"><?= esc($grupo['nombre']) ?></h2>
        <p class="text-muted small mb-4">Reporte del grupo</p>

        <!-- Resumen -->
        <div class="row g-2 mb-4">
            <div class="col-4 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="small text-muted">Total gastado</div>
                    <div class="fw-bold fs-5 text-primary">$<?= number_format($resumen['total_gastado'], 0) ?></div>
                </div>
            </div>
            <div class="col-4 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="small text-muted">Total pagado</div>
                    <div class="fw-bold fs-5 text-success">$<?= number_format($resumen['total_pagado'], 0) ?></div>
                </div>
            </div>
            <div class="col-4 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="small text-muted">Gastos</div>
                    <div class="fw-bold fs-5 text-secondary"><?= $resumen['cant_gastos'] ?></div>
                </div>
            </div>
            <div class="col-4 col-md-3">
                <div class="card border-0 shadow-sm text-center py-3 h-100">
                    <div class="small text-muted">Pagos</div>
                    <div class="fw-bold fs-5 text-secondary"><?= $resumen['cant_pagos'] ?></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Balance por miembro -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Balance por miembro</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($balance)): ?>
                            <p class="text-muted small p-3 mb-0">Sin datos de balance.</p>
                        <?php else: ?>
                            <?php foreach ($balance as $b): ?>
                                <div class="mobile-card-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium"><?= esc($b['name']) ?></span>
                                        <span class="fw-bold <?= $b['saldo'] > 0 ? 'text-success' : ($b['saldo'] < 0 ? 'text-danger' : 'text-secondary') ?>">
                                            $<?= number_format(abs($b['saldo']), 2) ?>
                                            <small class="fw-normal"><?= $b['saldo'] > 0 ? 'a favor' : ($b['saldo'] < 0 ? 'debe' : 'saldado') ?></small>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mt-1">
                                        <span>Pag&oacute;: $<?= number_format($b['total_pagado_gastos'], 0) ?></span>
                                        <span>Consumi&oacute;: $<?= number_format($b['total_consumido'], 0) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Gastos por categoria -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Gastos por categor&iacute;a</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($porCategoria)): ?>
                            <p class="text-muted small p-3 mb-0">Sin gastos categorizados.</p>
                        <?php else: ?>
                            <?php foreach ($porCategoria as $c): ?>
                                <div class="mobile-card-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-light text-dark"><?= esc($c['categoria']) ?></span>
                                        <span class="fw-medium">$<?= number_format($c['total'], 0) ?></span>
                                    </div>
                                    <div class="progress mt-1" style="height:4px">
                                        <div class="progress-bar bg-warning" style="width:<?= ($porCategoria[0]['total'] > 0) ? ($c['total'] / $porCategoria[0]['total'] * 100) : 0 ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Evolucion mensual -->
        <?php if (!empty($evolucion)):
            $maxEvol = max(array_column($evolucion, 'total'));
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Evoluci&oacute;n mensual</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <?php foreach ($evolucion as $e): ?>
                        <div class="col-4 col-md-2">
                            <div class="text-center">
                                <div class="small text-muted"><?= date('M', strtotime($e['mes'] . '-01')) ?></div>
                                <div class="fw-bold small">$<?= number_format($e['total'], 0) ?></div>
                                <div class="progress mt-1" style="height:4px">
                                    <div class="progress-bar bg-primary" style="width:<?= max(5, ($maxEvol > 0 ? $e['total'] / $maxEvol * 100 : 5)) ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ultimos movimientos -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">&Uacute;ltimos movimientos</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($movimientos)): ?>
                    <p class="text-muted small p-3 mb-0">Sin movimientos recientes.</p>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>"><?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                    <span class="fw-medium small ms-1"><?= esc($m['descripcion']) ?></span>
                                </div>
                                <span class="fw-bold small <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?>">$<?= number_format($m['monto'], 0) ?></span>
                            </div>
                            <div class="text-muted small"><?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot; <?= esc($m['persona']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Deudas -->
        <?php if (!empty($deudas)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Transferencias sugeridas</h5>
            </div>
            <div class="card-body p-0">
                <?php foreach ($deudas as $d): ?>
                    <div class="mobile-card-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($d['deudor']) ?></strong>
                                <span class="text-muted"> le debe a </span>
                                <strong><?= esc($d['acreedor']) ?></strong>
                            </div>
                            <div class="fw-bold text-danger">$<?= number_format($d['monto'], 2) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

<?= view('partials/_footer') ?>
