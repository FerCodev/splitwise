<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($user) ? 'Editar' : 'Nuevo') . ' Usuario']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4"><?= isset($user) ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form action="<?= isset($user) ? base_url('usuarios/' . $user['id']) : base_url('usuarios') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if (isset($user)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-medium">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= esc(old('name', $user['name'] ?? '')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-medium">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= esc(old('email', $user['email'] ?? '')) ?>" required>
                    </div>

                    <?php if (!isset($user)): ?>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-medium">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('usuarios') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($user) ? 'Guardar Cambios' : 'Crear Usuario' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($user)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Cambiar contraseña</h5>
                </div>
                <div class="card-body">
                    <form action="<?= base_url('usuarios/' . $user['id'] . '/password') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-medium">Nueva contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-outline-warning">Actualizar contraseña</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
