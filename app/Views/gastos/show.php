<?= view('partials/_head', ['title' => 'SplitWise - ' . esc($gasto['descripcion'])]) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('gastos') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver</a>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="card-title mb-1"><?= esc($gasto['descripcion']) ?></h3>
                        <p class="text-muted small mb-0">
                            Grupo: <a href="<?= base_url('grupos/' . $gasto['grupo_id']) ?>"><?= esc($gasto['grupo_nombre']) ?></a>
                            &middot; <?= date('d/m/Y', strtotime($gasto['fecha'])) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <h4 class="text-primary mb-0 fw-bold">$<?= number_format($gasto['monto'], 2) ?></h4>
                        <small class="text-muted">Pagó <?= esc($gasto['pagador_nombre']) ?></small>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-outline-primary flex-fill">Editar</a>
                    <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" class="flex-fill" onsubmit="return confirm('¿Eliminar gasto?')">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-outline-danger w-100">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Participantes</h5>
            </div>
            <div class="card-body p-0 d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th class="text-end">Monto asignado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participantes as $p): ?>
                                <tr>
                                    <td><?= esc($p['name']) ?>
                                        <?php if ($p['user_id'] == $gasto['pagador_id']): ?>
                                            <span class="badge bg-info ms-1">Pagó</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($p['email']) ?></td>
                                    <td class="text-end fw-medium">$<?= number_format($p['monto_asignado'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-end">$<?= number_format(array_sum(array_column($participantes, 'monto_asignado')), 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="d-md-none">
                <?php foreach ($participantes as $p): ?>
                    <div class="mobile-card-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-medium"><?= esc($p['name'])?></span>
                                <?php if ($p['user_id'] == $gasto['pagador_id']): ?>
                                    <span class="badge bg-info ms-1">Pagó</span>
                                <?php endif; ?>
                            </div>
                            <div class="fw-bold">$<?= number_format($p['monto_asignado'], 2) ?></div>
                        </div>
                        <div class="text-muted small"><?= esc($p['email'])?></div>
                    </div>
                <?php endforeach; ?>
                <div class="mobile-card-item bg-light fw-bold">
                    <div class="d-flex justify-content-between">
                        <span>Total</span>
                        <span>$<?= number_format(array_sum(array_column($participantes, 'monto_asignado')), 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Resumen</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <strong><?= esc($gasto['pagador_nombre']) ?></strong> pagó <strong>$<?= number_format($gasto['monto'], 2) ?></strong>
                    y debe recibir de los demás participantes.
                </p>
                <div class="d-none d-md-block">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Debe</th>
                                <th>A</th>
                                <th class="text-end">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participantes as $p): ?>
                                <?php if ($p['user_id'] != $gasto['pagador_id']): ?>
                                    <tr>
                                        <td><?= esc($p['name']) ?></td>
                                        <td><?= esc($gasto['pagador_nombre']) ?></td>
                                        <td class="text-end fw-medium">$<?= number_format($p['monto_asignado'], 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-md-none">
                    <?php foreach ($participantes as $p): ?>
                        <?php if ($p['user_id'] != $gasto['pagador_id']): ?>
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span><?= esc($p['name']) ?> debe a <?= esc($gasto['pagador_nombre']) ?></span>
                                <span class="fw-bold">$<?= number_format($p['monto_asignado'], 2) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<?= view('partials/_footer') ?>
