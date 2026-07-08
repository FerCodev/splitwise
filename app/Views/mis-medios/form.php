<?= view('partials/_head', ['title' => 'Gastito - ' . (isset($medio) ? 'Editar' : 'Nuevo') . ' medio de cobro']) ?>
<?= view('partials/_navbar', ['pageTitle' => isset($medio) ? 'Editar medio' : 'Nuevo medio']) ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4 d-none d-md-block"><?= isset($medio) ? 'Editar medio de cobro' : 'Nuevo medio de cobro' ?></h2>

        <?= view('partials/_validation_errors') ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="<?= isset($medio) ? base_url('mis-medios-de-cobro/' . $medio['id']) : base_url('mis-medios-de-cobro') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if (isset($medio)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-medium">Nombre <small class="text-muted">(ej: Mercado Pago, Santander)</small></label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?= esc(old('nombre', $medio['nombre'] ?? '')) ?>"
                               maxlength="255" required placeholder="Ej: Mercado Pago">
                    </div>

                    <div class="mb-3">
                        <label for="titular" class="form-label fw-medium">Titular</label>
                        <input type="text" class="form-control" id="titular" name="titular"
                               value="<?= esc(old('titular', $medio['titular'] ?? '')) ?>"
                               maxlength="255" required placeholder="Nombre del titular">
                    </div>

                    <div class="mb-3">
                        <label for="cbu_cvu" class="form-label fw-medium">CBU/CVU <small class="text-muted">(requerido si no hay alias)</small></label>
                        <input type="text" class="form-control" id="cbu_cvu" name="cbu_cvu"
                               value="<?= esc(old('cbu_cvu', $medio['cbu_cvu'] ?? '')) ?>"
                               maxlength="255" placeholder="0000000000000000000000">
                    </div>

                    <div class="mb-3">
                        <label for="alias" class="form-label fw-medium">Alias <small class="text-muted">(requerido si no hay CBU/CVU)</small></label>
                        <input type="text" class="form-control" id="alias" name="alias"
                               value="<?= esc(old('alias', $medio['alias'] ?? '')) ?>"
                               maxlength="255" placeholder="alias.mercadopago">
                    </div>

                    <div class="mb-3">
                        <label for="banco" class="form-label fw-medium">Banco <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" id="banco" name="banco"
                               value="<?= esc(old('banco', $medio['banco'] ?? '')) ?>"
                               maxlength="255" placeholder="Ej: Santander, Galicia">
                    </div>

                    <div class="mb-3">
                        <label for="payment_link" class="form-label fw-medium">Link de pago <small class="text-muted">(opcional)</small></label>
                        <input type="url" class="form-control" id="payment_link" name="payment_link"
                               value="<?= esc(old('payment_link', $medio['payment_link'] ?? '')) ?>"
                               maxlength="500" placeholder="https://mpago.li/...">
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('mis-medios-de-cobro') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($medio) ? 'Guardar Cambios' : 'Crear Medio' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= view('partials/_footer') ?>
