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

    <?php if (isset($writeResult)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Prueba de escritura</h5>

            <?php
            $w = $writeResult;
            $wStatusClass = match (true) {
                !$w['dirFound']    => 'text-danger',
                !$w['dirWritable'] => 'text-danger',
                !$w['fileCreated'] => 'text-danger',
                !$w['writeOk']     => 'text-danger',
                !$w['readOk']      => 'text-danger',
                !$w['hashMatch']   => 'text-danger',
                !$w['fileDeleted'] => 'text-danger',
                ($w['residue'] ?? true) => 'text-danger',
                default             => 'text-success',
            };
            ?>

            <p class="mb-1"><strong>Directorio encontrado:</strong> <?= $w['dirFound'] ? 'Sí' : 'No' ?></p>
            <p class="mb-1"><strong>Directorio escribible:</strong> <?= $w['dirWritable'] ? 'Sí' : 'No' ?></p>
            <p class="mb-1"><strong>Archivo temporal creado:</strong> <?= $w['fileCreated'] ? 'Sí' : 'No' ?></p>
            <p class="mb-1"><strong>Escritura completada:</strong> <?= $w['writeOk'] ? 'Sí' : 'No' ?></p>
            <?php if ($w['sizeWritten'] !== null): ?>
            <p class="mb-1"><strong>Tamaño escrito:</strong> <?= esc((string) $w['sizeWritten']) ?> bytes</p>
            <?php endif; ?>
            <p class="mb-1"><strong>Lectura posterior correcta:</strong> <?= $w['readOk'] ? 'Sí' : 'No' ?></p>
            <p class="mb-1"><strong>Hash coincidente:</strong> <?= $w['hashMatch'] ? 'Sí' : 'No' ?></p>
            <p class="mb-1"><strong>Archivo eliminado:</strong> <?= $w['fileDeleted'] ? 'Sí' : 'No' ?></p>
            <p class="mb-1"><strong>Residuos:</strong> <?= ($w['residue'] ?? null) === false ? 'No' : (($w['residue'] ?? null) === null ? '—' : 'Sí') ?></p>

            <p class="mt-3 mb-0"><strong>Resultado:</strong> <span class="<?= $wStatusClass ?>"><?= esc($w['statusText']) ?></span></p>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!isset($writeResult)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">Prueba de escritura</h5>
            <form action="<?= base_url('admin/storage-test/write') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary">Probar escritura y limpieza</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">&larr; Volver</a>
</div>

<?= view('partials/_footer') ?>
