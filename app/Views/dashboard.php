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
                <a href="<?= base_url('gastos') ?>" class="btn btn-outline-light btn-sm me-2">Gastos</a>
                <span class="navbar-text me-3"><?= session()->get('userName') ?></span>
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Dashboard</h4>
                        <p class="card-text">Bienvenido, <?= session()->get('userName') ?>!</p>
                        <p class="card-text">Email: <?= session()->get('userEmail') ?></p>
                        <a href="<?= base_url('grupos') ?>" class="btn btn-primary mt-3">Ir a Grupos</a>
                        <a href="<?= base_url('gastos') ?>" class="btn btn-success mt-3 ms-2">Ir a Gastos</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
