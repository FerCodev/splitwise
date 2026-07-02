<?= view('partials/_head', ['title' => 'Foto de perfil - Gastito']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Foto de perfil']) ?>
<div class="container py-4 avatar-profile-page">
    <?= view('partials/_feedback') ?>
    <div class="card avatar-profile-card">
        <div class="card-body text-center">
            <?= view('components/avatar', [
                'userId' => $user['id'],
                'name' => $user['name'],
                'avatarFilename' => $user['avatar_filename'] ?? null,
                'avatarUpdatedAt' => $user['avatar_updated_at'] ?? null,
                'size' => 144,
                'alt' => 'Tu foto de perfil',
            ]) ?>
            <form action="<?= base_url('perfil/avatar') ?>" method="post" enctype="multipart/form-data" class="mt-4" id="avatar-upload-form">
                <?= csrf_field() ?>
                <input type="file" name="avatar" id="avatar-file" class="visually-hidden" accept="image/jpeg,image/png,image/webp" required>
                <label for="avatar-file" class="btn btn-primary w-100" id="avatar-file-label">
                    <?= !empty($user['avatar_filename']) ? 'Cambiar foto' : 'Subir foto' ?>
                </label>
                <div id="avatar-preview-wrap" class="mt-3 d-none">
                    <img id="avatar-preview" class="avatar-preview" alt="Vista previa de la foto seleccionada">
                </div>
                <button type="submit" class="btn btn-success w-100 mt-3 d-none" id="avatar-submit">Guardar foto</button>
                <div class="text-muted small mt-2">JPG, PNG o WebP. M&aacute;ximo 30 MB.</div>
            </form>
            <?php if (!empty($user['avatar_filename'])): ?>
                <form action="<?= base_url('perfil/avatar/eliminar') ?>" method="post" class="mt-3" onsubmit="return confirm('Eliminar tu foto de perfil?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger w-100">Eliminar foto</button>
                </form>
            <?php endif; ?>
            <a href="<?= base_url('perfil') ?>" class="btn btn-link mt-2">Volver al perfil</a>
        </div>
    </div>
</div>
<script>
(function () {
    const input = document.getElementById('avatar-file');
    const preview = document.getElementById('avatar-preview');
    const wrap = document.getElementById('avatar-preview-wrap');
    const submit = document.getElementById('avatar-submit');
    const form = document.getElementById('avatar-upload-form');
    if (!input) return;
    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) return;
        if (file.size > 30 * 1024 * 1024) {
            alert('La imagen no puede superar 30 MB.');
            input.value = '';
            return;
        }
        preview.src = URL.createObjectURL(file);
        wrap.classList.remove('d-none');
        submit.classList.remove('d-none');
    });
    form.addEventListener('submit', function () {
        submit.disabled = true;
        submit.textContent = 'Guardando...';
    });
})();
</script>
<?= view('partials/_footer') ?>
