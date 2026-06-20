<?= view('partials/_head', ['title' => 'Cambiar contraseña - SplitWise']) ?>
    <?= view('partials/_navbar', ['pageTitle' => 'Cambiar contraseña']) ?>
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
                        <div class="password-field">
                            <input type="password" class="form-control" id="current_password" name="current_password" required autofocus>
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" aria-label="Mostrar contraseña">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva contrase&ntilde;a</label>
                        <div class="password-field">
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" aria-label="Mostrar contraseña">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirm" class="form-label">Repetir nueva contrase&ntilde;a</label>
                        <div class="password-field">
                            <input type="password" class="form-control" id="new_password_confirm" name="new_password_confirm" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" aria-label="Mostrar contraseña">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                            </button>
                        </div>
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
