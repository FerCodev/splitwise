<?= view('partials/_head', ['title' => 'SplitWise - Mi perfil']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Perfil']) ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="mb-3 d-none d-md-block">Mi perfil</h2>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Account info -->
        <div class="balance-strip mb-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar avatar-lg" style="background:var(--primary);"><?= esc(strtoupper(substr((string) $user['name'], 0, 1))) ?></div>
                <div>
                    <div class="financial-list-item-title"><?= esc($user['name']) ?></div>
                    <div class="financial-list-item-subtitle"><?= esc($user['email']) ?></div>
                    <div class="financial-list-item-subtitle">
                        <span class="badge bg-<?= $user['role'] === 'admin' ? 'warning' : 'secondary' ?>"><?= $user['role'] === 'admin' ? 'Administrador' : 'Usuario' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit name -->
        <div class="section-header">
            <div class="section-header-title">Editar nombre</div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form action="<?= base_url('perfil/editar-nombre') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= old('name', esc($user['name'])) ?>" required minlength="2" maxlength="255">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar nombre</button>
                </form>
            </div>
        </div>

        <!-- Edit email -->
        <div class="section-header">
            <div class="section-header-title">Editar email</div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form action="<?= base_url('perfil/editar-email') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email', esc($user['email'])) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar email</button>
                </form>
            </div>
        </div>

        <!-- Change password -->
        <div class="section-header">
            <div class="section-header-title">Cambiar contrase&ntilde;a</div>
        </div>
        <div class="card mb-3">
            <div class="card-body">
                <form action="<?= base_url('perfil/cambiar-password') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contrase&ntilde;a actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva contrase&ntilde;a</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirm" class="form-label">Repetir nueva contrase&ntilde;a</label>
                        <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary">Cambiar contrase&ntilde;a</button>
                </form>
            </div>
        </div>

        <!-- Quick links -->
        <div class="section-header">
            <div class="section-header-title">Accesos r&aacute;pidos</div>
        </div>
        <div class="card mb-3">
            <a href="<?= base_url('mis-medios-de-cobro') ?>" class="financial-list-item financial-list-item-clickable text-decoration-none" style="color:inherit;">
                <div class="financial-list-item-info">
                    <div class="financial-list-item-title" style="font-size:14px;">Mis medios de cobro</div>
                    <div class="financial-list-item-subtitle">Gestion&aacute; tus datos de cobro</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="var(--muted)" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>
            </a>
        </div>
    </div>

<?= view('partials/_footer') ?>
