<?= view('partials/_head', ['title' => 'SplitWise - Pago']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Detalle de pago']) ?>

    <div class="container mt-3 mt-md-4">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <!-- Payment header -->
        <div class="balance-strip mb-3">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="balance-strip-label">Pago</div>
                <?php if ($permisos['puede_editar_pago'] || $permisos['puede_eliminar_pago']): ?>
                <div class="dropdown">
                    <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opciones">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/></svg>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if ($permisos['puede_editar_pago']): ?>
                            <li><a class="dropdown-item" href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>">Editar</a></li>
                        <?php endif; ?>
                        <?php if ($permisos['puede_eliminar_pago']): ?>
                            <li>
                                <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" id="delete-pago-<?= $pago['id'] ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="button" class="dropdown-item text-danger"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-confirm-title="Eliminar pago"
                                        data-confirm-msg="Se eliminar&aacute; este pago y el balance del grupo volver&aacute; a reflejar esa deuda. Esta acci&oacute;n no se puede deshacer."
                                        data-confirm-btn="Eliminar pago"
                                        data-confirm-form="delete-pago-<?= $pago['id'] ?>">Eliminar</button>
                                </form>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <div class="balance-strip-amount text-success">
                $<?= number_format($pago['monto'], 2) ?>
            </div>
            <div class="balance-strip-detail" style="margin-top:8px;">
                <div class="balance-strip-detail-item">
                    <span class="text-muted">De:</span>
                    <span class="fw-semibold"><?= esc($pago['pagador_nombre']) ?></span>
                </div>
                <div class="balance-strip-detail-item">
                    <span class="text-muted">A:</span>
                    <span class="fw-semibold"><?= esc($pago['receptor_nombre']) ?></span>
                </div>
            </div>
        </div>

        <!-- Detail card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Detalle</h5>
            </div>
            <div class="financial-list-item">
                <div class="financial-list-item-info">
                    <div class="financial-list-item-title" style="font-size:14px;"><?= $pago['descripcion'] ? esc($pago['descripcion']) : 'Pago #' . $pago['id'] ?></div>
                    <div class="financial-list-item-subtitle">
                        <a href="<?= base_url('grupos/' . $pago['grupo_id']) ?>"><?= esc($pago['grupo_nombre']) ?></a>
                        &middot; <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <?php if ($permisos['puede_editar_pago'] || $permisos['puede_eliminar_pago']): ?>
        <div class="d-flex gap-2">
            <?php if ($permisos['puede_editar_pago']): ?>
                <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-primary flex-fill">Editar</a>
            <?php endif; ?>
            <?php if ($permisos['puede_eliminar_pago']): ?>
                <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" class="flex-fill" id="delete-pago-m-<?= $pago['id'] ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="button" class="btn btn-danger w-100"
                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                        data-confirm-title="Eliminar pago"
                        data-confirm-msg="Se eliminar&aacute; este pago y el balance del grupo volver&aacute; a reflejar esa deuda. Esta acci&oacute;n no se puede deshacer."
                        data-confirm-btn="Eliminar pago"
                        data-confirm-form="delete-pago-m-<?= $pago['id'] ?>">Eliminar</button>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
