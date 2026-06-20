<?= view('partials/_head', ['title' => 'Mi perfil - SplitWise']) ?>
    <?= view('partials/_navbar', ['pageTitle' => 'Perfil']) ?>
    <div class="container py-4">
        <h2 class="mb-4 d-none d-md-block">Mi perfil</h2>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Informaci&oacute;n de la cuenta</h5>
                <div class="d-grid gap-3 mb-4">
                    <div>
                        <div class="fw-semibold">Nombre</div>
                        <div class="text-muted text-break"><?= esc($user['name']) ?></div>
                    </div>
                    <div>
                        <div class="fw-semibold">Email</div>
                        <div class="text-muted text-break"><?= esc($user['email']) ?></div>
                    </div>
                    <div>
                        <div class="fw-semibold">Rol</div>
                        <div class="text-muted"><?= $user['role'] === 'admin' ? 'Administrador' : 'Usuario' ?></div>
                    </div>
                </div>
                <a href="<?= base_url('perfil/editar') ?>" class="btn btn-primary w-100">Editar perfil</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Seguridad</h5>
                <a href="<?= base_url('perfil/cambiar-password') ?>" class="btn btn-primary">Cambiar contrase&ntilde;a</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Accesos r&aacute;pidos</h5>
                <a href="<?= base_url('mis-medios-de-cobro') ?>" class="btn btn-primary">Mis medios de cobro</a>
            </div>
        </div>
    </div>
    <?= view('partials/_footer') ?>
