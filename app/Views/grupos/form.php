<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitWise - <?= isset($grupo) ? 'Editar' : 'Nuevo' ?> Grupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --touch-target: 44px; }
        .btn { min-height: var(--touch-target); display: inline-flex; align-items: center; justify-content: center; }
        .form-control, .form-select { min-height: var(--touch-target); font-size: 16px; }
        @media (max-width: 575.98px) {
            .card { border-radius: 12px; }
            .container { padding-left: 12px; padding-right: 12px; }
            h2 { font-size: 1.5rem; }
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
        <h2 class="fw-bold mb-4"><?= isset($grupo) ? 'Editar Grupo' : 'Nuevo Grupo' ?></h2>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="<?= isset($grupo) ? base_url('grupos/' . $grupo['id']) : base_url('grupos') ?>" method="post">
                    <?php if (isset($grupo)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-medium">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?= esc(old('nombre', $grupo['nombre'] ?? '')) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label fw-medium">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= esc(old('descripcion', $grupo['descripcion'] ?? '')) ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('grupos') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($grupo) ? 'Guardar Cambios' : 'Crear Grupo' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
