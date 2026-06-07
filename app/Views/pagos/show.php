<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitWise - Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --touch-target: 44px; }
        .btn { min-height: var(--touch-target); display: inline-flex; align-items: center; justify-content: center; }
        .form-control, .form-select { min-height: var(--touch-target); font-size: 16px; }
        @media (max-width: 575.98px) {
            .card { border-radius: 12px; }
            .container { padding-left: 12px; padding-right: 12px; }
            h3 { font-size: 1.25rem; }
            h4 { font-size: 1.1rem; }
            .navbar-brand { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">SplitWise</a>
            <div class="d-flex align-items-center gap-1">
                <a href="<?= base_url('grupos') ?>" class="btn btn-outline-light btn-sm">Grupos</a>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text"><?= session()->get('userName') ?></span></li>
                        <li><span class="dropdown-item-text text-muted small"><?= session()->get('userEmail') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= base_url('grupos') ?>">Grupos</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('gastos') ?>">Gastos</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('pagos') ?>">Pagos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('pagos') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver</a>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="card-title mb-1">
                            <?= $pago['descripcion'] ? esc($pago['descripcion']) : 'Pago #' . $pago['id'] ?>
                        </h3>
                        <p class="text-muted small mb-0">
                            Grupo: <a href="<?= base_url('grupos/' . $pago['grupo_id']) ?>"><?= esc($pago['grupo_nombre']) ?></a>
                            &middot; <?= date('d/m/Y', strtotime($pago['fecha'])) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <h4 class="text-primary mb-0 fw-bold">$<?= number_format($pago['monto'], 2) ?></h4>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-outline-primary flex-fill">Editar</a>
                    <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" class="flex-fill" onsubmit="return confirm('¿Eliminar pago?')">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-outline-danger w-100">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Detalle del pago</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <p class="text-muted small mb-1">Pagó</p>
                        <h5 class="text-success mb-0"><?= esc($pago['pagador_nombre']) ?></h5>
                    </div>
                    <div class="col-4">
                        <p class="text-muted small mb-1">Monto</p>
                        <h4 class="text-primary fw-bold mb-0">$<?= number_format($pago['monto'], 2) ?></h4>
                    </div>
                    <div class="col-4">
                        <p class="text-muted small mb-1">Recibió</p>
                        <h5 class="text-danger mb-0"><?= esc($pago['receptor_nombre']) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
