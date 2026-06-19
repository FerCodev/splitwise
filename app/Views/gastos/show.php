<?= view('partials/_head', ['title' => 'SplitWise - ' . $gasto['descripcion']]) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Detalle de gasto']) ?>

    <div class="container mt-3 mt-md-4">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <!-- Expense header -->
        <div class="balance-strip mb-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="balance-strip-label">Gasto</div>
                <?php if ($permisos['puede_editar_gasto'] || $permisos['puede_eliminar_gasto']): ?>
                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opciones">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/></svg>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if ($permisos['puede_editar_gasto']): ?>
                            <li><a class="dropdown-item" href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>">Editar</a></li>
                        <?php endif; ?>
                        <?php if ($permisos['puede_eliminar_gasto']): ?>
                            <li>
                                <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" id="delete-gasto-<?= $gasto['id'] ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="button" class="dropdown-item text-danger"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-confirm-title="Eliminar gasto"
                                        data-confirm-msg="Se eliminar&aacute; este gasto del grupo y el balance se recalcular&aacute;. Esta acci&oacute;n no se puede deshacer."
                                        data-confirm-btn="Eliminar gasto"
                                        data-confirm-form="delete-gasto-<?= $gasto['id'] ?>">Eliminar</button>
                                </form>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <div class="balance-strip-amount text-primary">
                -$<?= number_format($gasto['monto'], 2) ?>
            </div>
            <div style="margin-top:8px;">
                <div class="financial-list-item-title"><?= esc($gasto['descripcion']) ?></div>
                <div class="financial-list-item-subtitle">
                    Pag&oacute; <?= esc($gasto['pagador_nombre']) ?> &middot;
                    <a href="<?= base_url('grupos/' . $gasto['grupo_id']) ?>"><?= esc($gasto['grupo_nombre']) ?></a>
                    &middot; <?= date('d/m/Y', strtotime($gasto['fecha'])) ?>
                    &middot; <span class="badge bg-secondary"><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($gasto['nota']) || !empty($gasto['recibo_path'])): ?>
        <div class="card mb-3">
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
        <div class="section-header">
            <div class="section-header-title">Participantes</div>
        </div>
        <div class="card mb-3 d-none d-md-block">
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
                                <td class="fw-semibold"><?= esc($p['name']) ?>
                                    <?php if ($p['user_id'] == $gasto['pagador_id']): ?>
                                        <span class="badge bg-info" style="font-size:10px;padding:2px 6px;">Pag&oacute;</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?= esc($p['email']) ?></td>
                                <td class="text-end financial-amount">$<?= number_format($p['monto_asignado'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">Total</th>
                            <th class="text-end financial-amount fw-bold">$<?= number_format(array_sum(array_column($participantes, 'monto_asignado')), 2) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card mb-3 d-md-none">
            <?php foreach ($participantes as $p): ?>
                <div class="financial-list-item">
                    <div class="financial-list-item-info">
                        <div class="financial-list-item-title" style="font-size:14px;">
                            <?= esc($p['name']) ?>
                            <?php if ($p['user_id'] == $gasto['pagador_id']): ?>
                                <span class="badge bg-info" style="font-size:10px;padding:2px 6px;">Pag&oacute;</span>
                            <?php endif; ?>
                        </div>
                        <div class="financial-list-item-subtitle"><?= esc($p['email']) ?></div>
                    </div>
                    <div class="financial-list-item-amount">$<?= number_format($p['monto_asignado'], 2) ?></div>
                </div>
            <?php endforeach; ?>
            <div class="financial-list-item" style="background:var(--surface);">
                <div class="financial-list-item-info">
                    <div class="financial-list-item-title" style="font-size:14px;">Total</div>
                </div>
                <div class="financial-list-item-amount fw-bold">$<?= number_format(array_sum(array_column($participantes, 'monto_asignado')), 2) ?></div>
            </div>
        </div>

        <!-- Resumen -->
        <div class="section-header">
            <div class="section-header-title">Resumen</div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <p class="text-muted small mb-2">
                    <strong><?= esc($gasto['pagador_nombre']) ?></strong> pag&oacute; <strong>$<?= number_format($gasto['monto'], 2) ?></strong>
                    y debe recibir de los dem&aacute;s participantes.
                </p>
            </div>
            <div class="d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
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
                                        <td class="text-end financial-amount">$<?= number_format($p['monto_asignado'], 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-md-none">
                <?php foreach ($participantes as $p): ?>
                    <?php if ($p['user_id'] != $gasto['pagador_id']): ?>
                        <div class="financial-list-item">
                            <div class="financial-list-item-info">
                                <div class="financial-list-item-title" style="font-size:14px;"><?= esc($p['name']) ?></div>
                                <div class="financial-list-item-subtitle">debe a <?= esc($gasto['pagador_nombre']) ?></div>
                            </div>
                            <div class="financial-list-item-amount">$<?= number_format($p['monto_asignado'], 2) ?></div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Actions -->
        <?php if ($permisos['puede_editar_gasto'] || $permisos['puede_eliminar_gasto']): ?>
        <div class="d-flex gap-2">
            <?php if ($permisos['puede_editar_gasto']): ?>
                <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-primary flex-fill">Editar</a>
            <?php endif; ?>
            <?php if ($permisos['puede_eliminar_gasto']): ?>
                <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" class="flex-fill" id="delete-gasto-m-<?= $gasto['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="button" class="btn btn-danger w-100"
                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                        data-confirm-title="Eliminar gasto"
                        data-confirm-msg="Se eliminar&aacute; este gasto del grupo y el balance se recalcular&aacute;. Esta acci&oacute;n no se puede deshacer."
                        data-confirm-btn="Eliminar gasto"
                        data-confirm-form="delete-gasto-m-<?= $gasto['id'] ?>">Eliminar</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
