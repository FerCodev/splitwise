<?= view('partials/_head', ['title' => 'SplitWise - Reportes']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Reportes</h2>
            <a href="<?= base_url('reportes/exportar?' . http_build_query($filters)) ?>" class="btn btn-outline-success btn-sm">Exportar CSV</a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
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
                        <a href="<?= base_url('reportes') ?>" class="btn btn-outline-secondary flex-fill">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-medium">Total gastado</div>
                        <div class="fs-4 fw-bold text-primary">$<?= number_format($resumen['total_gastado'], 2) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-medium">Pagado por m&iacute;</div>
                        <div class="fs-4 fw-bold text-success">$<?= number_format($resumen['total_pagado'], 2) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-medium">Recibido</div>
                        <div class="fs-4 fw-bold text-danger">$<?= number_format($resumen['total_recibido'], 2) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-medium">Saldo global</div>
                        <div class="fs-4 fw-bold <?= $resumen['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                            $<?= number_format($resumen['saldo'], 2) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Gastos por categor&iacute;a</h5>
                    </div>
                    <?php if (empty($porCategoria)): ?>
                        <div class="card-body text-center py-4">
                            <p class="text-muted mb-0">Sin datos.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-none d-md-block">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Categor&iacute;a</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($porCategoria as $row): ?>
                                            <tr>
                                                <td><?= esc($row['categoria']) ?></td>
                                                <td class="text-end"><?= (int) $row['cantidad'] ?></td>
                                                <td class="text-end fw-medium">$<?= number_format((float) $row['total'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-md-none">
                            <?php foreach ($porCategoria as $row): ?>
                                <div class="mobile-card-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium"><?= esc($row['categoria']) ?></span>
                                        <span class="fw-bold text-primary">$<?= number_format((float) $row['total'], 2) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= (int) $row['cantidad'] ?> gasto(s)</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Gastos por grupo</h5>
                    </div>
                    <?php if (empty($porGrupo)): ?>
                        <div class="card-body text-center py-4">
                            <p class="text-muted mb-0">Sin datos.</p>
                        </div>
                    <?php else: ?>
                        <div class="d-none d-md-block">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Grupo</th>
                                            <th class="text-end">Cantidad</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($porGrupo as $row): ?>
                                            <tr>
                                                <td><?= esc($row['grupo']) ?></td>
                                                <td class="text-end"><?= (int) $row['cantidad'] ?></td>
                                                <td class="text-end fw-medium">$<?= number_format((float) $row['total'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="d-md-none">
                            <?php foreach ($porGrupo as $row): ?>
                                <div class="mobile-card-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium"><?= esc($row['grupo']) ?></span>
                                        <span class="fw-bold text-primary">$<?= number_format((float) $row['total'], 2) ?></span>
                                    </div>
                                    <div class="text-muted small"><?= (int) $row['cantidad'] ?> gasto(s)</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">&Uacute;ltimos movimientos</h5>
            </div>
            <?php if (empty($movimientos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">Sin movimientos.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripci&oacute;n</th>
                                    <th class="text-end">Monto</th>
                                    <th>Persona</th>
                                    <th>Grupo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movimientos as $m): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($m['fecha'])) ?></td>
                                        <td>
                                            <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>">
                                                <?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?>
                                            </span>
                                        </td>
                                        <td><?= esc($m['descripcion']) ?></td>
                                        <td class="text-end fw-medium">$<?= number_format((float) $m['monto'], 2) ?></td>
                                        <td><?= esc($m['persona']) ?></td>
                                        <td><?= esc($m['grupo']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($movimientos as $m): ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>">
                                        <?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?>
                                    </span>
                                    <span class="fw-medium ms-1"><?= esc($m['descripcion']) ?></span>
                                </div>
                                <div class="fw-bold <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?>">
                                    $<?= number_format((float) $m['monto'], 2) ?>
                                </div>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot;
                                <?= esc($m['persona']) ?> &middot;
                                <?= esc($m['grupo']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

<?= view('partials/_footer') ?>
