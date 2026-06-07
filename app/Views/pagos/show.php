<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - Pago</title>
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

    <div class="container mt-4">
        <a href="<?= base_url('pagos') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver a Pagos</a>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="card-title mb-1">
                            <?= $pago['descripcion'] ? esc($pago['descripcion']) : 'Pago #' . $pago['id'] ?>
                        </h3>
                        <p class="text-muted mb-0">
                            Grupo: <a href="<?= base_url('grupos/' . $pago['grupo_id']) ?>"><?= esc($pago['grupo_nombre']) ?></a>
                            &middot; Fecha: <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <h4 class="text-primary mb-0">$<?= number_format($pago['monto'], 2) ?></h4>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                    <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar pago?')">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detalle del pago</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center border-end">
                        <p class="text-muted mb-1">Pagó</p>
                        <h5 class="text-success"><?= esc($pago['pagador_nombre']) ?></h5>
                    </div>
                    <div class="col-md-4 text-center">
                        <p class="text-muted mb-1">Monto</p>
                        <h4 class="text-primary">$<?= number_format($pago['monto'], 2) ?></h4>
                    </div>
                    <div class="col-md-4 text-center border-start">
                        <p class="text-muted mb-1">Recibió</p>
                        <h5 class="text-danger"><?= esc($pago['receptor_nombre']) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
