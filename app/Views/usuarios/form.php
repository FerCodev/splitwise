<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($user) ? 'Editar' : 'Nuevo') . ' Usuario']) ?>
<?= view('partials/_navbar', ['pageTitle' => isset($user) ? 'Editar usuario' : 'Nuevo usuario']) ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4 d-none d-md-block"><?= isset($user) ? 'Editar Usuario' : 'Nuevo Usuario' ?></h2>

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

                    <div class="mb-3">
                        <label for="role" class="form-label fw-medium">Rol global</label>
                        <select class="form-select" id="role" name="role">
                            <option value="user" <?= old('role', $user['role'] ?? '') === 'user' ? 'selected' : '' ?>>Usuario</option>
                            <option value="admin" <?= old('role', $user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>

                    <?php if (!isset($user)): ?>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-medium">Contraseña</label>
                            <div class="password-field">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword(this)" aria-label="Mostrar contraseña">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                                </button>
                            </div>
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
                            <div class="password-field">
                                <input type="password" class="form-control" id="password" name="password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword(this)" aria-label="Mostrar contraseña">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning">Actualizar contraseña</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
