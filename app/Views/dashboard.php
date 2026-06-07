<?= view('partials/_head', ['title' => 'SplitWise - Dashboard']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h4 class="card-title">Dashboard</h4>
                <p class="card-text mb-1">Bienvenido, <?= session()->get('userName') ?>!</p>
                <p class="card-text text-muted small"><?= session()->get('userEmail') ?></p>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">Mis Grupos</h5>
            <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">+ Nuevo Grupo</a>
        </div>

        <?php if (empty($grupos)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-3">No tenés grupos aún. Creá uno nuevo.</p>
                    <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">Crear Grupo</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($grupos as $grupo): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($grupo['nombre']) ?></h5>
                                <p class="card-text text-muted small"><?= esc($grupo['descripcion'] ?? 'Sin descripción') ?></p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0">
                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-primary w-100">Abrir Grupo</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
