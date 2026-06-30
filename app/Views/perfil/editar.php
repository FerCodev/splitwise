<?php
    $currentColor = old('color', $user['color'] ?? \App\Services\UserColor::DEFAULT_KEY);
    $colorInfo = \App\Services\UserColor::get($currentColor);
?>
<?= view('partials/_head', ['title' => 'Editar perfil - Gastito']) ?>
    <?= view('partials/_navbar', ['pageTitle' => 'Editar perfil']) ?>
    <div class="container py-4">
        <h2 class="mb-4 d-none d-md-block">Editar perfil</h2>

        <?= view('partials/_feedback') ?>

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
                    <div class="mb-3">
                        <label class="form-label d-block">Color personal</label>
                        <p class="text-muted small mb-2">Eleg&iacute; el color que usaremos por defecto para tus movimientos en los grupos. Los dem&aacute;s pueden ver otro color para vos si lo configuran en su grupo.</p>
                        <div class="user-color-swatches" role="radiogroup" aria-label="Color personal">
                            <label class="user-color-swatch user-color-swatch-auto <?= $currentColor === \App\Services\UserColor::DEFAULT_KEY ? 'is-selected' : '' ?>" title="Sin color personalizado">
                                <input type="radio" name="color" value="<?= \App\Services\UserColor::DEFAULT_KEY ?>" <?= $currentColor === \App\Services\UserColor::DEFAULT_KEY ? 'checked' : '' ?>>
                                <span class="user-color-swatch-circle" style="background: <?= esc(\App\Services\UserColor::RESERVED['system']['bg']) ?>; border-color: <?= esc(\App\Services\UserColor::RESERVED['system']['border']) ?>;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.34-5.66L20 8.68M20 4v4.68h-4.68" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                                <span class="user-color-swatch-label">Autom&aacute;tico</span>
                            </label>
                            <?php foreach ($palette as $key => $info): ?>
                                <label class="user-color-swatch <?= $currentColor === $key ? 'is-selected' : '' ?>" title="<?= esc($info['label']) ?>">
                                    <input type="radio" name="color" value="<?= esc($key) ?>" <?= $currentColor === $key ? 'checked' : '' ?>>
                                    <span class="user-color-swatch-circle" style="background: <?= esc($info['bg']) ?>; border-color: <?= esc($info['border']) ?>;"></span>
                                    <span class="user-color-swatch-label"><?= esc($info['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
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
