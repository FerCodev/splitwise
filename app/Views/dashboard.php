<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">SplitWise</a>
            <div class="d-flex align-items-center">
                <a href="<?= base_url('grupos') ?>" class="btn btn-outline-light btn-sm me-2">Grupos</a>
                <span class="navbar-text me-3"><?= session()->get('userName') ?></span>
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h4 class="card-title">Dashboard</h4>
                        <p class="card-text mb-1">Bienvenido, <?= session()->get('userName') ?>!</p>
                        <p class="card-text text-muted"><?= session()->get('userEmail') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Mis Grupos</h5>
            <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary btn-sm">+ Nuevo Grupo</a>
        </div>

        <?php if (empty($grupos)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-0">No tenés grupos aún. Creá uno nuevo.</p>
                    <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary mt-3">Crear Grupo</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($grupos as $grupo): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= esc($grupo['nombre']) ?></h5>
                                <p class="card-text text-muted"><?= esc($grupo['descripcion'] ?? 'Sin descripción') ?></p>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-primary btn-sm w-100">Abrir Grupo</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
