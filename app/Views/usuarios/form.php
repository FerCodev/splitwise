<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($user) ? 'Editar' : 'Nuevo') . ' Usuario']) ?>
<?= view('partials/_navbar', ['pageTitle' => isset($user) ? 'Editar usuario' : 'Nuevo usuario']) ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="mb-3 d-none d-md-block"><?= isset($user) ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-body">
                <form action="<?= isset($user) ? base_url('usuarios/' . $user['id']) : base_url('usuarios') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if (isset($user)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= esc(old('name', $user['name'] ?? '')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= esc(old('email', $user['email'] ?? '')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Rol global</label>
                        <select class="form-select" id="role" name="role">
                            <option value="user" <?= old('role', $user['role'] ?? '') === 'user' ? 'selected' : '' ?>>Usuario</option>
                            <option value="admin" <?= old('role', $user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>

                    <?php if (!isset($user)): ?>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contrase&ntilde;a</label>
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
            <div class="section-header">
                <div class="section-header-title">Cambiar contrase&ntilde;a</div>
            </div>
            <div class="card">
                <div class="card-body">
                    <form action="<?= base_url('usuarios/' . $user['id'] . '/password') ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contrase&ntilde;a</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-warning">Actualizar contrase&ntilde;a</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
