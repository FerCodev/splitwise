<!DOCTYPE html>
<html lang="es">
<head>
    <?= view('partials/_head') ?>
    <title>Mi perfil - SplitWise</title>
</head>
<body>
    <?= view('partials/_navbar') ?>
    <div class="container py-4">
        <h2 class="mb-4">Mi perfil</h2>

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

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Información de la cuenta</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nombre</dt>
                    <dd class="col-sm-9"><?= esc($user['name']) ?></dd>
                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9"><?= esc($user['email']) ?></dd>
                    <dt class="col-sm-3">Rol</dt>
                    <dd class="col-sm-9"><?= $user['role'] === 'admin' ? 'Administrador' : 'Usuario' ?></dd>
                </dl>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Editar nombre</h5>
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

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Editar email</h5>
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

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Cambiar contraseña</h5>
                <form action="<?= base_url('perfil/cambiar-password') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirm" class="form-label">Repetir nueva contraseña</label>
                        <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" required minlength="8">
                    </div>
                    <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Accesos rápidos</h5>
                <a href="<?= base_url('mis-medios-de-cobro') ?>" class="btn btn-primary">Mis medios de cobro</a>
            </div>
        </div>
    </div>
    <?= view('partials/_footer') ?>
</body>
</html>
