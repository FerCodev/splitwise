<!DOCTYPE html>
<html lang="es">
<head>
    <?= view('partials/_head') ?>
    <title>Editar perfil - SplitWise</title>
</head>
<body>
    <?= view('partials/_navbar', ['pageTitle' => 'Editar perfil']) ?>
    <div class="container py-4">
        <h2 class="mb-4 d-none d-md-block">Editar perfil</h2>

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

        <div class="card">
            <div class="card-body">
                <form action="<?= base_url('perfil/editar') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= old('name', esc($user['name'])) ?>" required minlength="2" maxlength="255" autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= old('email', esc($user['email'])) ?>" required>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('perfil') ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?= view('partials/_footer') ?>
</body>
</html>