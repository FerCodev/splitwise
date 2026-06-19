<?= view('partials/_head', ['title' => 'SplitWise - Reporte: ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Reporte']) ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('reportes') ?>" class="btn btn-secondary btn-sm mb-3">&larr; Volver a reportes</a>

        <div class="balance-strip mb-3">
            <div class="balance-strip-label">Reporte del grupo</div>
            <h3 class="mb-2"><?= esc($grupo['nombre']) ?></h3>
            <div class="balance-strip-detail">
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Gastado:</span>
                    <span class="financial-amount text-primary">$<?= number_format($resumen['total_gastado'], 2) ?></span>
                </div>
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Pagado:</span>
                    <span class="financial-amount text-success">$<?= number_format($resumen['total_pagado'], 2) ?></span>
                </div>
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Gastos:</span>
                    <span class="financial-amount"><?= $resumen['cant_gastos'] ?></span>
                </div>
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Pagos:</span>
                    <span class="financial-amount"><?= $resumen['cant_pagos'] ?></span>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <!-- Balance por miembro -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Balance por miembro</h5>
                    </div>
                    <?php if (empty($balance)): ?>
                        <div class="empty-state" style="padding:24px;">
                            <div class="empty-state-text">Sin datos de balance.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($balance as $b): ?>
                            <div class="financial-list-item">
                                <div class="financial-list-item-info">
                                    <div class="financial-list-item-title" style="font-size:14px;"><?= esc($b['name']) ?></div>
                                    <div class="financial-list-item-subtitle">
                                        Pag&oacute;: $<?= number_format($b['total_pagado_gastos'], 0) ?> &middot;
                                        Consumi&oacute;: $<?= number_format($b['total_consumido'], 0) ?>
                                    </div>
                                </div>
                                <div class="financial-list-item-amount <?= $b['saldo'] > 0 ? 'text-success' : ($b['saldo'] < 0 ? 'text-danger' : 'text-muted') ?>">
                                    $<?= number_format(abs($b['saldo']), 2) ?>
                                    <div style="font-size:10px;font-weight:600;"><?= $b['saldo'] > 0 ? 'a favor' : ($b['saldo'] < 0 ? 'debe' : 'saldado') ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Gastos por categoria -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Gastos por categor&iacute;a</h5>
                    </div>
                    <?php if (empty($porCategoria)): ?>
                        <div class="empty-state" style="padding:24px;">
                            <div class="empty-state-text">Sin gastos categorizados.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($porCategoria as $i => $c): ?>
                            <div class="financial-list-item">
                                <div style="width:24px;text-align:center;font-size:13px;font-weight:700;color:var(--muted);"><?= $i + 1 ?></div>
                                <div class="financial-list-item-info">
                                    <div class="financial-list-item-title" style="font-size:14px;">
                                        <span class="badge bg-secondary"><?= esc($c['categoria']) ?></span>
                                    </div>
                                </div>
                                <div style="flex:1;max-width:80px;">
                                    <div class="progress" style="height:4px;">
                                        <div class="progress-bar" style="width:<?= ($porCategoria[0]['total'] > 0) ? ($c['total'] / $porCategoria[0]['total'] * 100) : 0 ?>%;background:var(--warning);"></div>
                                    </div>
                                </div>
                                <div class="financial-list-item-amount">$<?= number_format($c['total'], 0) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Evolucion mensual -->
        <?php if (!empty($evolucion)):
            $maxEvol = max(array_column($evolucion, 'total'));
        ?>
        <div class="section-header">
            <div class="section-header-title">Evoluci&oacute;n mensual</div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-2">
                    <?php foreach ($evolucion as $e): ?>
                        <div class="col-4 col-md-2">
                            <div style="text-align:center;">
                                <div style="font-size:12px;color:var(--muted);"><?= date('M', strtotime($e['mes'] . '-01')) ?></div>
                                <div class="financial-amount" style="font-size:14px;">$<?= number_format($e['total'], 0) ?></div>
                                <div class="progress mt-1" style="height:4px">
                                    <div class="progress-bar" style="width:<?= max(5, ($maxEvol > 0 ? $e['total'] / $maxEvol * 100 : 5)) ?>%;background:var(--primary);"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Ultimos movimientos -->
        <div class="section-header">
            <div class="section-header-title">&Uacute;ltimos movimientos</div>
        </div>
        <div class="card mb-3">
            <?php if (empty($movimientos)): ?>
                <div class="empty-state" style="padding:24px;">
                    <div class="empty-state-text">Sin movimientos recientes.</div>
                </div>
            <?php else: ?>
                <?php foreach ($movimientos as $m): ?>
                    <div class="financial-list-item" style="border-left:3px solid <?= $m['tipo'] === 'gasto' ? 'var(--primary)' : 'var(--accent)' ?>;">
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title" style="font-size:14px;">
                                <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>" style="font-size:10px;padding:2px 6px;margin-right:4px;"><?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                <?= esc($m['descripcion']) ?>
                            </div>
                            <div class="financial-list-item-subtitle">
                                <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot; <?= esc($m['persona']) ?>
                            </div>
                        </div>
                        <div class="financial-list-item-amount <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?>">
                            $<?= number_format($m['monto'], 0) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Deudas -->
        <?php if (!empty($deudas)): ?>
        <div class="section-header">
            <div class="section-header-title">Transferencias sugeridas</div>
        </div>
        <div class="card mb-3">
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
        </div>
        <?php endif; ?>

    </div>

<?= view('partials/_footer') ?>
