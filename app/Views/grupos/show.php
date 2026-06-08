<?= view('partials/_head', ['title' => 'SplitWise - ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('grupos') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver</a>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="card-title mb-1 fw-bold"><?= esc($grupo['nombre']) ?></h3>
                        <p class="text-muted small mb-0">
                            Creado el <?= date('d/m/Y H:i', strtotime($grupo['created_at'])) ?>
                        </p>
                        <span class="badge bg-<?= $rol === 'admin' ? 'warning' : 'secondary' ?> mt-2">
                            <?= $rol === 'admin' ? 'Administrador' : 'Miembro' ?>
                        </span>
                    </div>
                    <?php if ($rol === 'admin'): ?>
                        <div class="d-flex gap-1">
                            <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="<?= base_url('grupos/' . $grupo['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar grupo?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($grupo['descripcion']): ?>
                    <p class="mt-3 mb-0"><?= esc($grupo['descripcion']) ?></p>
                <?php endif; ?>

                <div class="d-flex gap-2 mt-3">
                    <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-info flex-fill">Ver balance</a>
                    <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary flex-fill">+ Gasto</a>
                    <a href="<?= base_url('pagos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-success flex-fill">+ Pago</a>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Miembros (<?= count($miembros) ?>)</h5>
            </div>
            <div class="card-body p-0 d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Desde</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($miembros as $miembro): ?>
                                <tr>
                                    <td class="fw-medium"><?= esc($miembro['name']) ?></td>
                                    <td><?= esc($miembro['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $miembro['rol'] === 'admin' ? 'warning' : 'secondary' ?>">
                                            <?= $miembro['rol'] === 'admin' ? 'Admin' : 'Miembro' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($miembro['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-md-none">
                <?php foreach ($miembros as $miembro): ?>
                    <div class="mobile-card-item">
                        <div class="fw-medium"><?= esc($miembro['name'])?></div>
                        <div class="text-muted small"><?= esc($miembro['email'])?></div>
                        <div class="mt-1">
                            <span class="badge bg-<?= $miembro['rol'] === 'admin' ? 'warning' : 'secondary' ?>">
                                <?= $miembro['rol'] === 'admin' ? 'Admin' : 'Miembro' ?>
                            </span>
                            <span class="text-muted small ms-2"><?= date('d/m/Y', strtotime($miembro['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Gastos (<?= count($gastos) ?>)</h5>
                <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary btn-sm">+ Nuevo</a>
            </div>
            <?php if (empty($gastos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay gastos en este grupo.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Monto</th>
                                    <th>Pagó</th>
                                    <th>Participantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastos as $gasto): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></td>
                                        <td><?= esc($gasto['descripcion']) ?></td>
                                        <td class="text-end fw-medium">$<?= number_format($gasto['monto'], 2) ?></td>
                                        <td><?= esc($gasto['pagador_nombre']) ?></td>
                                        <td><?= $gasto['total_participantes'] ?></td>
                                        <td>
                                            <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total gastado</th>
                                    <th class="text-end">$<?= number_format($totalGastado, 2) ?></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($gastos as $gasto): ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-medium"><?= esc($gasto['descripcion']) ?></div>
                                <div class="fw-bold text-primary">$<?= number_format($gasto['monto'], 2) ?></div>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= date('d/m/Y', strtotime($gasto['fecha'])) ?> &middot;
                                Pagó: <?= esc($gasto['pagador_nombre']) ?> &middot;
                                <?= $gasto['total_participantes'] ?> participante(s)
                            </div>
                            <div class="mt-2">
                                <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-outline-info btn-sm">Ver</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mobile-card-item bg-light fw-medium">
                        <div class="d-flex justify-content-between">
                            <span>Total gastado</span>
                            <span>$<?= number_format($totalGastado, 2) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Pagos (<?= count($pagos) ?>)</h5>
                <a href="<?= base_url('pagos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-success btn-sm">+ Nuevo</a>
            </div>
            <?php if (empty($pagos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay pagos en este grupo.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Monto</th>
                                    <th>Pagó</th>
                                    <th>Recibió</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pago): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                        <td><?= esc($pago['descripcion'] ?: '-') ?></td>
                                        <td class="text-end fw-medium">$<?= number_format($pago['monto'], 2) ?></td>
                                        <td><?= esc($pago['pagador_nombre']) ?></td>
                                        <td><?= esc($pago['receptor_nombre']) ?></td>
                                        <td>
                                            <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total pagado</th>
                                    <th class="text-end">$<?= number_format($totalPagado, 2) ?></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($pagos as $pago): ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-medium"><?= esc($pago['descripcion'] ?: 'Pago') ?></div>
                                <div class="fw-bold text-success">$<?= number_format($pago['monto'], 2) ?></div>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= date('d/m/Y', strtotime($pago['fecha'])) ?> &middot;
                                <?= esc($pago['pagador_nombre']) ?> pagó a <?= esc($pago['receptor_nombre']) ?>
                            </div>
                            <div class="mt-2">
                                <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-outline-info btn-sm">Ver</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mobile-card-item bg-light fw-medium">
                        <div class="d-flex justify-content-between">
                            <span>Total pagado</span>
                            <span>$<?= number_format($totalPagado, 2) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Saldos</h5>
            </div>
            <?php if (empty($saldos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay saldos para mostrar.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-end">Pagó</th>
                                    <th class="text-end">Debe</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($saldos as $s): ?>
                                    <tr>
                                        <td class="fw-medium"><?= esc($s['name']) ?></td>
                                        <td class="text-end">$<?= number_format($s['pago'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($s['debe'], 2) ?></td>
                                        <td class="text-end fw-medium <?= $s['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            $<?= number_format($s['saldo'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($saldos as $s): ?>
                        <div class="mobile-card-item">
                            <div class="fw-medium"><?= esc($s['name']) ?></div>
                            <div class="d-flex justify-content-between small mt-1">
                                <span>Pagó: $<?= number_format($s['pago'], 2) ?></span>
                                <span>Debe: $<?= number_format($s['debe'], 2) ?></span>
                            </div>
                            <div class="fw-medium mt-1 <?= $s['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                Saldo: $<?= number_format($s['saldo'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($deudas)): ?>
                    <div class="card-footer bg-white">
                        <h6 class="mb-2 fw-bold">Deudas</h6>
                        <?php foreach ($deudas as $d): ?>
                            <div class="py-1">
                                <strong><?= esc($d['deudor']) ?></strong> debe
                                <strong>$<?= number_format($d['monto'], 2) ?></strong> a
                                <strong><?= esc($d['acreedor']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

<?= view('partials/_footer') ?>
