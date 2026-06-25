<?= view('partials/_head', ['title' => 'SplitWise - ' . $gasto['descripcion']]) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Detalle de gasto']) ?>

    <div class="container mt-3 mt-md-4">

        <?= view('partials/_feedback') ?>

        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="fw-bold text-primary mb-0"><?= moneda($gasto['monto']) ?></h2>
                        <p class="text-muted small mb-0">Pag&oacute; <?= esc($gasto['pagador_nombre']) ?></p>
                    </div>
                    <?php if ($permisos['puede_editar_gasto'] || $permisos['puede_eliminar_gasto']): ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#accionesGastoModal">Acciones</button>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold mb-1"><?= esc($gasto['descripcion']) ?></h5>
                <p class="text-muted small mb-0 d-flex flex-wrap align-items-center gap-1">
                    <a href="<?= base_url('grupos/' . $gasto['grupo_id']) ?>" class="meta-pill-link"><?= esc($gasto['grupo_nombre']) ?></a>
                    <span>&middot;</span>
                    <span><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></span>
                    <span>&middot;</span>
                    <span class="badge bg-light text-dark"><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></span>
                </p>

            </div>
        </div>

        <?php if (!empty($gasto['nota']) || !empty($gasto['recibo_path'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <?php if (!empty($gasto['nota'])): ?>
                <div class="mb-2">
                    <span class="text-muted small fw-medium">Nota:</span>
                    <p class="mb-0"><?= esc($gasto['nota']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($gasto['recibo_path'])): ?>
                <div>
                    <span class="text-muted small fw-medium">Comprobante:</span>
                    <div class="mt-1">
                        <span class="small"><?= esc($gasto['recibo_nombre'] ?? 'Archivo adjunto') ?></span>
                        <a href="<?= base_url('gastos/' . $gasto['id'] . '/recibo') ?>" class="btn btn-sm btn-primary ms-2" target="_blank">Ver</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Participantes -->
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
                                            <span class="badge bg-info ms-1">Pag&oacute;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($p['email']) ?></td>
                                    <td class="text-end fw-medium"><?= moneda($p['monto_asignado']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-end"><?= moneda(array_sum(array_column($participantes, 'monto_asignado'))) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="d-md-none">
                <div class="d-flex flex-wrap gap-2 p-3">
                    <?php foreach ($participantes as $p): ?>
                        <span class="badge bg-light text-dark border d-inline-flex align-items-center gap-2 py-2 px-3" style="font-size:14px">
                            <?= esc($p['name']) ?>
                            <?php if ($p['user_id'] == $gasto['pagador_id']): ?>
                                <span class="badge bg-info">Pag&oacute;</span>
                            <?php endif; ?>
                            <span class="fw-bold"><?= moneda($p['monto_asignado']) ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
                <div class="px-3 pb-3 text-end fw-bold">
                    Total: <?= moneda(array_sum(array_column($participantes, 'monto_asignado'))) ?>
                </div>
            </div>
        </div>

        <!-- Resumen -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Resumen</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <strong><?= esc($gasto['pagador_nombre']) ?></strong> pag&oacute; <strong><?= moneda($gasto['monto']) ?></strong>
                    y debe recibir de los dem&aacute;s participantes.
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
                                        <td class="text-end fw-medium"><?= moneda($p['monto_asignado']) ?></td>
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
                                <span class="fw-bold"><?= moneda($p['monto_asignado']) ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

<?= view('partials/_confirm_modal') ?>

<div class="modal fade" id="accionesGastoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Acciones del gasto</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-2">
                <?php if ($permisos['puede_editar_gasto']): ?>
                    <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-primary w-100">Editar</a>
                <?php endif; ?>
                <?php if ($permisos['puede_eliminar_gasto']): ?>
                    <?= view('components/forms/delete_form', [
                        'action' => base_url('gastos/' . $gasto['id']),
                        'formId' => 'delete-gasto-' . $gasto['id'],
                        'buttonClass' => 'btn btn-danger w-100',
                        'confirmTitle' => 'Eliminar gasto',
                        'confirmMsg' => 'Se eliminará este gasto del grupo y el balance se recalculará. Esta acción no se puede deshacer.',
                        'confirmBtn' => 'Eliminar gasto',
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= view('partials/_footer') ?>
