<?= view('partials/_head', ['title' => 'SplitWise - Pago']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('pagos') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver</a>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="card-title mb-1">
                            <?= $pago['descripcion'] ? esc($pago['descripcion']) : 'Pago #' . $pago['id'] ?>
                        </h3>
                        <p class="text-muted small mb-0">
                            Grupo: <a href="<?= base_url('grupos/' . $pago['grupo_id']) ?>"><?= esc($pago['grupo_nombre']) ?></a>
                            &middot; <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <h4 class="text-primary mb-0 fw-bold">$<?= number_format($pago['monto'], 2) ?></h4>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-outline-primary flex-fill">Editar</a>
                    <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" class="flex-fill" onsubmit="return confirm('¿Eliminar pago?')">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-outline-danger w-100">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Detalle del pago</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <p class="text-muted small mb-1">Pagó</p>
                        <h5 class="text-success mb-0"><?= esc($pago['pagador_nombre']) ?></h5>
                    </div>
                    <div class="col-4">
                        <p class="text-muted small mb-1">Monto</p>
                        <h4 class="text-primary fw-bold mb-0">$<?= number_format($pago['monto'], 2) ?></h4>
                    </div>
                    <div class="col-4">
                        <p class="text-muted small mb-1">Recibió</p>
                        <h5 class="text-danger mb-0"><?= esc($pago['receptor_nombre']) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?= view('partials/_footer') ?>
