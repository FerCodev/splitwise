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
                    <div class="avatar-crop-stage mx-auto">
                        <canvas id="avatar-crop-canvas" width="280" height="280" aria-label="Editor de recorte de foto"></canvas>
                    </div>
                    <label for="avatar-zoom" class="form-label mt-3 mb-1">Zoom</label>
                    <input type="range" class="form-range" id="avatar-zoom" min="1" max="3" step="0.01" value="1">
                    <div class="text-muted small">Arrastr&aacute; la imagen para elegir el encuadre.</div>
                </div>
                <input type="hidden" name="crop_x" id="avatar-crop-x">
                <input type="hidden" name="crop_y" id="avatar-crop-y">
                <input type="hidden" name="crop_size" id="avatar-crop-size">
                <button type="submit" class="btn btn-success w-100 mt-3 d-none" id="avatar-submit">Guardar foto</button>
                <div class="text-muted small mt-2">JPG, PNG o WebP. M&aacute;ximo 30 MB.</div>
            </form>
            <?php if (!empty($user['avatar_filename'])): ?>
                <div class="mt-3">
                    <?= view('components/forms/delete_form', [
                        'action' => base_url('perfil/avatar/eliminar'),
                        'formId' => 'eliminar-foto-perfil',
                        'label' => 'Eliminar foto',
                        'buttonClass' => 'btn btn-outline-danger w-100',
                        'confirmTitle' => 'Eliminar foto de perfil',
                        'confirmMsg' => 'Se eliminará tu foto actual. Podés subir una nueva cuando quieras.',
                        'confirmBtn' => 'Eliminar foto',
                        'methodOverride' => false,
                    ]) ?>
                </div>
            <?php endif; ?>
            <a href="<?= base_url('perfil') ?>" class="btn btn-link mt-2">Volver al perfil</a>
        </div>
    </div>
</div>
<script>
(function () {
    const SIZE = 280;
    const input = document.getElementById('avatar-file');
    const canvas = document.getElementById('avatar-crop-canvas');
    const context = canvas ? canvas.getContext('2d') : null;
    const wrap = document.getElementById('avatar-preview-wrap');
    const zoomInput = document.getElementById('avatar-zoom');
    const submit = document.getElementById('avatar-submit');
    const form = document.getElementById('avatar-upload-form');
    const cropX = document.getElementById('avatar-crop-x');
    const cropY = document.getElementById('avatar-crop-y');
    const cropSize = document.getElementById('avatar-crop-size');
    let image = null;
    let baseScale = 1;
    let zoom = 1;
    let offsetX = 0;
    let offsetY = 0;
    let dragging = false;
    let lastX = 0;
    let lastY = 0;

    if (!input || !canvas || !context) return;

    function clampOffsets() {
        const scale = baseScale * zoom;
        const maxX = Math.max(0, (image.naturalWidth * scale - SIZE) / 2);
        const maxY = Math.max(0, (image.naturalHeight * scale - SIZE) / 2);
        offsetX = Math.max(-maxX, Math.min(maxX, offsetX));
        offsetY = Math.max(-maxY, Math.min(maxY, offsetY));
    }

    function draw() {
        if (!image) return;
        clampOffsets();
        const scale = baseScale * zoom;
        const width = image.naturalWidth * scale;
        const height = image.naturalHeight * scale;
        context.clearRect(0, 0, SIZE, SIZE);
        context.drawImage(image, (SIZE - width) / 2 + offsetX, (SIZE - height) / 2 + offsetY, width, height);
    }

    input.addEventListener('change', function () {
        const file = input.files && input.files[0];
        if (!file) return;
        if (file.size > 30 * 1024 * 1024) {
            GastitoFeedback.show('error', 'La imagen no puede superar 30 MB.');
            input.value = '';
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        const selected = new Image();
        selected.onload = function () {
            URL.revokeObjectURL(objectUrl);
            image = selected;
            baseScale = Math.max(SIZE / image.naturalWidth, SIZE / image.naturalHeight);
            zoom = 1;
            offsetX = 0;
            offsetY = 0;
            zoomInput.value = '1';
            draw();
            wrap.classList.remove('d-none');
            submit.classList.remove('d-none');
        };
        selected.onerror = function () {
            URL.revokeObjectURL(objectUrl);
            input.value = '';
            GastitoFeedback.show('error', 'No se pudo mostrar la imagen seleccionada.');
        };
        selected.src = objectUrl;
    });

    zoomInput.addEventListener('input', function () {
        const previous = zoom;
        zoom = Number(zoomInput.value);
        if (previous > 0) {
            offsetX *= zoom / previous;
            offsetY *= zoom / previous;
        }
        draw();
    });

    canvas.addEventListener('pointerdown', function (event) {
        if (!image) return;
        dragging = true;
        lastX = event.clientX;
        lastY = event.clientY;
        canvas.setPointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointermove', function (event) {
        if (!dragging) return;
        offsetX += event.clientX - lastX;
        offsetY += event.clientY - lastY;
        lastX = event.clientX;
        lastY = event.clientY;
        draw();
    });
    canvas.addEventListener('pointerup', function (event) {
        dragging = false;
        canvas.releasePointerCapture(event.pointerId);
    });
    canvas.addEventListener('pointercancel', function () {
        dragging = false;
    });

    form.addEventListener('submit', function (event) {
        if (!image) {
            event.preventDefault();
            return;
        }
        const scale = baseScale * zoom;
        const side = SIZE / scale;
        cropX.value = Math.max(0, (image.naturalWidth - side) / 2 - offsetX / scale).toFixed(3);
        cropY.value = Math.max(0, (image.naturalHeight - side) / 2 - offsetY / scale).toFixed(3);
        cropSize.value = side.toFixed(3);
        submit.disabled = true;
        submit.textContent = 'Guardando...';
    });
})();
</script>
<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
