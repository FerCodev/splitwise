<?= view('partials/_head', ['title' => 'Gastito - Prueba de almacenamiento externo']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Storage Test']) ?>

<div class="container mt-3 mt-md-4">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Prueba de almacenamiento externo</h4>

            <?php
            $statusClass = match ($status) {
                'ok'           => 'text-success',
                'not_found'    => 'text-warning',
                'not_readable' => 'text-danger',
                'read_error'   => 'text-danger',
                default        => 'text-muted',
            };
            ?>

            <p class="mb-1"><strong>Estado:</strong> <span class="<?= $statusClass ?>"><?= esc($statusText) ?></span></p>

            <?php if ($size !== null): ?>
            <p class="mb-1"><strong>Tamaño:</strong> <?= esc((string) $size) ?> bytes</p>
            <?php endif; ?>

            <?php if ($empty !== null): ?>
            <p class="mb-1"><strong>Vacío:</strong> <?= $empty ? 'Sí' : 'No' ?></p>
            <?php endif; ?>

            <?php if ($hash !== null): ?>
            <p class="mb-1"><strong>SHA-256:</strong> <code class="small"><?= esc($hash) ?></code></p>
            <?php endif; ?>

            <?php if ($preview !== null): ?>
            <div class="mt-3">
                <strong>Preview:</strong>
                <pre class="bg-light p-2 rounded small mt-1" style="max-height:150px;overflow:auto"><?= esc($preview) ?></pre>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">&larr; Volver</a>
</div>

<?= view('partials/_footer') ?>
