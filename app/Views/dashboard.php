<?= view('partials/_head', ['title' => 'SplitWise - Dashboard']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Dashboard</h4>
                <p class="text-muted small mb-0">Bienvenido, <?= session()->get('userName') ?>!</p>
            </div>
            <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">+ Nuevo Grupo</a>
        </div>

        <?php if (empty($grupos)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-3">No tenés grupos aún. Creá uno nuevo.</p>
                    <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">Crear Grupo</a>
                </div>
            </div>
        <?php else: ?>

            <!-- Resumen general -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="fs-1 fw-bold text-success"><?= $cantidadActivos ?></div>
                            <div class="text-muted small">Grupos activos</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="fs-1 fw-bold text-warning"><?= $cantidadCerrados ?></div>
                            <div class="text-muted small">Grupos cerrados</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="fs-1 fw-bold text-secondary"><?= $cantidadLiquidados ?></div>
                            <div class="text-muted small">Grupos liquidados</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <?php
                                $saldoClase = $globalSaldo > 0 ? 'text-success' : ($globalSaldo < 0 ? 'text-danger' : 'text-secondary');
                                $saldoTexto = $globalSaldo > 0 ? 'A favor' : ($globalSaldo < 0 ? 'Debe' : 'Saldado');
                            ?>
                            <div class="fs-1 fw-bold <?= $saldoClase ?>">$<?= number_format(abs($globalSaldo), 2) ?></div>
                            <div class="text-muted small">Saldo global: <?= $saldoTexto ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="fs-1 fw-bold text-success"><?= $gruposAFavor ?></div>
                            <div class="text-muted small">Grupos a favor</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <div class="fs-1 fw-bold text-danger"><?= $gruposDebe ?></div>
                            <div class="text-muted small">Grupos donde debés</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mis grupos -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Mis grupos (<?= count($grupos) ?>)</h5>
                </div>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Grupo</th>
                                    <th>Estado</th>
                                    <th class="text-end">Total gastado</th>
                                    <th class="text-end">Total pagado</th>
                                    <th class="text-end">Mi saldo</th>
                                    <th class="text-end">Transf. pendientes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($grupos as $grupo): ?>
                                    <?php
                                        $badgeEstado = [
                                            'activo' => 'bg-success',
                                            'cerrado' => 'bg-warning text-dark',
                                            'liquidado' => 'bg-secondary',
                                        ];
                                        $claseBadge = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                                        $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : '');
                                    ?>
                                    <tr>
                                        <td class="fw-medium"><?= esc($grupo['nombre']) ?></td>
                                        <td><span class="badge <?= $claseBadge ?>"><?= ucfirst($grupo['estado']) ?></span></td>
                                        <td class="text-end">$<?= number_format($grupo['total_gastado'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($grupo['total_pagado'], 2) ?></td>
                                        <td class="text-end fw-medium <?= $saldoClase ?>">$<?= number_format($grupo['mi_saldo'], 2) ?></td>
                                        <td class="text-end"><?= $grupo['deudas_count'] ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-info btn-sm">Detalle</a>
                                                <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-secondary btn-sm">Balance</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($grupos as $grupo): ?>
                        <?php
                            $badgeEstado = [
                                'activo' => 'bg-success',
                                'cerrado' => 'bg-warning text-dark',
                                'liquidado' => 'bg-secondary',
                            ];
                            $claseBadge = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                            $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : '');
                        ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-medium"><?= esc($grupo['nombre']) ?></div>
                                <span class="badge <?= $claseBadge ?>"><?= ucfirst($grupo['estado']) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mt-1">
                                <span class="text-muted">Gastado:</span>
                                <span>$<?= number_format($grupo['total_gastado'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Pagado:</span>
                                <span>$<?= number_format($grupo['total_pagado'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Mi saldo:</span>
                                <span class="fw-medium <?= $saldoClase ?>">$<?= number_format($grupo['mi_saldo'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Transf. pendientes:</span>
                                <span><?= $grupo['deudas_count'] ?></span>
                            </div>
                            <div class="d-flex gap-2 mt-2">
                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-info btn-sm flex-fill">Detalle</a>
                                <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-secondary btn-sm flex-fill">Balance</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Movimientos recientes -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Ultimos gastos</h5>
                            <a href="<?= base_url('gastos') ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
                        </div>
                        <?php if (empty($ultimosGastos)): ?>
                            <div class="card-body text-center py-4">
                                <p class="text-muted mb-0">No hay gastos registrados.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($ultimosGastos as $gasto): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-medium"><?= esc($gasto['descripcion']) ?></span>
                                                <br>
                                                <span class="text-muted small"><?= esc($gasto['grupo_nombre']) ?> &middot; <?= esc($gasto['pagador_nombre']) ?> &middot; <?= date('d/m/Y', strtotime($gasto['fecha'])) ?> &middot; <span class="badge bg-light text-dark"><?= esc($gasto['categoria'] ?? 'Otros') ?></span></span>
                                            </div>
                                            <span class="fw-bold text-primary">$<?= number_format($gasto['monto'], 2) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Ultimos pagos</h5>
                            <a href="<?= base_url('pagos') ?>" class="btn btn-outline-primary btn-sm">Ver todos</a>
                        </div>
                        <?php if (empty($ultimosPagos)): ?>
                            <div class="card-body text-center py-4">
                                <p class="text-muted mb-0">No hay pagos registrados.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($ultimosPagos as $pago): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-medium">Pago</span>
                                                <br>
                                                <span class="text-muted small"><?= esc($pago['grupo_nombre']) ?> &middot; <?= esc($pago['pagador_nombre']) ?> pagó a <?= esc($pago['receptor_nombre']) ?> &middot; <?= date('d/m/Y', strtotime($pago['fecha'])) ?></span>
                                            </div>
                                            <span class="fw-bold text-success">$<?= number_format($pago['monto'], 2) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
