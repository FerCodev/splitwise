<?= view('partials/_head', ['title' => 'Cambiar contrase&ntilde;a - SplitWise']) ?>
    <?= view('partials/_navbar', ['pageTitle' => 'Cambiar contrase&ntilde;a']) ?>
    <div class="container py-4">
        <h2 class="mb-4 d-none d-md-block">Cambiar contrase&ntilde;a</h2>

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
                <form action="<?= base_url('perfil/cambiar-password') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contrase&ntilde;a actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva contrase&ntilde;a</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirm" class="form-label">Repetir nueva contrase&ntilde;a</label>
                        <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" required minlength="8">
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= base_url('perfil') ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar contrase&ntilde;a</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?= view('partials/_footer') ?>
