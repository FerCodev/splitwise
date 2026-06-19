<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($categoria) ? 'Editar' : 'Nueva') . ' Categor&iacute;a']) ?>
<?= view('partials/_navbar', ['pageTitle' => isset($categoria) ? 'Editar categoría' : 'Nueva categoría']) ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4 d-none d-md-block"><?= isset($categoria) ? 'Editar Categor&iacute;a' : 'Nueva Categor&iacute;a' ?></h2>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="<?= isset($categoria) ? base_url('categorias/' . $categoria['id']) : base_url('categorias') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if (isset($categoria)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-medium">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?= esc(old('nombre', $categoria['nombre'] ?? '')) ?>"
                               maxlength="50" required>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('categorias') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($categoria) ? 'Guardar Cambios' : 'Crear Categor&iacute;a' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= view('partials/_footer') ?>
