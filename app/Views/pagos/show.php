<?= view('partials/_head', ['title' => 'SplitWise - Pago']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Detalle de pago']) ?>

    <div class="container mt-3 mt-md-4">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="fw-bold text-primary mb-0">$<?= number_format($pago['monto'], 2) ?></h2>
                        <p class="text-muted small mb-0">
                            <?= esc($pago['pagador_nombre']) ?> &rarr; <?= esc($pago['receptor_nombre']) ?>
                        </p>
                    </div>
                    <?php if ($permisos['puede_editar_pago'] || $permisos['puede_eliminar_pago']): ?>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Opciones">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M3 9.5a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3m5 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3"/></svg>
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
                                            data-confirm-msg="Se eliminará este pago y el balance del grupo volverá a reflejar esa deuda. Esta acción no se puede deshacer."
                                            data-confirm-btn="Eliminar pago"
                                            data-confirm-form="delete-pago-<?= $pago['id'] ?>">Eliminar</button>
                                    </form>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold mb-1"><?= $pago['descripcion'] ? esc($pago['descripcion']) : 'Pago #' . $pago['id'] ?></h5>
                <p class="text-muted small mb-0">
                    <a href="<?= base_url('grupos/' . $pago['grupo_id']) ?>"><?= esc($pago['grupo_nombre']) ?></a>
                    &middot; <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                </p>
                <!-- Desktop actions -->
                <?php if ($permisos['puede_editar_pago'] || $permisos['puede_eliminar_pago']): ?>
                <div class="d-none d-md-flex gap-2 mt-3">
                    <?php if ($permisos['puede_editar_pago']): ?>
                        <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a>
                    <?php endif; ?>
                    <?php if ($permisos['puede_eliminar_pago']): ?>
                        <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" id="delete-pago-d-<?= $pago['id'] ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="button" class="btn btn-danger btn-sm"
                                data-bs-toggle="modal" data-bs-target="#confirmModal"
                                data-confirm-title="Eliminar pago"
                                data-confirm-msg="Se eliminará este pago y el balance del grupo volverá a reflejar esa deuda. Esta acción no se puede deshacer."
                                data-confirm-btn="Eliminar pago"
                                data-confirm-form="delete-pago-d-<?= $pago['id'] ?>">Eliminar</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detalle -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Detalle del pago</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <p class="text-muted small mb-1">Pag&oacute;</p>
                        <h5 class="text-success mb-0"><?= esc($pago['pagador_nombre']) ?></h5>
                    </div>
                    <div class="col-4">
                        <p class="text-muted small mb-1">Monto</p>
                        <h4 class="text-primary fw-bold mb-0">$<?= number_format($pago['monto'], 2) ?></h4>
                    </div>
                    <div class="col-4">
                        <p class="text-muted small mb-1">Recibi&oacute;</p>
                        <h5 class="text-danger mb-0"><?= esc($pago['receptor_nombre']) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
