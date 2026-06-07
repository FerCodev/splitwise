<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitWise - Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --touch-target: 44px; }
        .btn { min-height: var(--touch-target); display: inline-flex; align-items: center; justify-content: center; }
        .form-control, .form-select { min-height: var(--touch-target); font-size: 16px; }
        .mobile-card-item { padding: 12px 16px; border-bottom: 1px solid #eee; }
        .mobile-card-item:last-child { border-bottom: 0; }
        @media (max-width: 575.98px) {
            .card { border-radius: 12px; }
            .container { padding-left: 12px; padding-right: 12px; }
            h2 { font-size: 1.5rem; }
            .navbar-brand { font-size: 1.1rem; }
            .table-mobile-hidden { display: none !important; }
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Gastos</h2>
            <a href="<?= base_url('gastos/nuevo') ?>" class="btn btn-primary">+ Nuevo</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-medium">Grupo</label>
                        <select name="grupo_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= ($filters['grupo_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-medium">Descripción</label>
                        <input type="text" name="descripcion" class="form-control" placeholder="Buscar..." value="<?= esc($filters['descripcion'] ?? '') ?>">
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
                        <a href="<?= base_url('gastos') ?>" class="btn btn-outline-secondary flex-fill">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($gastos)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-0">No hay gastos registrados.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="d-none d-md-block">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><a href="<?= base_url('gastos?sort=fecha&order=') ?><?= ($filters['sort'] ?? 'fecha') === 'fecha' && ($filters['order'] ?? 'DESC') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Fecha <?= ($filters['sort'] ?? 'fecha') === 'fecha' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Descripción</th>
                                        <th><a href="<?= base_url('gastos?sort=monto&order=') ?><?= ($filters['sort'] ?? '') === 'monto' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Monto <?= ($filters['sort'] ?? '') === 'monto' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Pagó</th>
                                        <th><a href="<?= base_url('gastos?sort=grupo_nombre&order=') ?><?= ($filters['sort'] ?? '') === 'grupo_nombre' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Grupo <?= ($filters['sort'] ?? '') === 'grupo_nombre' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Participantes</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($gastos as $gasto): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></td>
                                            <td><?= esc($gasto['descripcion']) ?></td>
                                            <td class="fw-medium">$<?= number_format($gasto['monto'], 2) ?></td>
                                            <td><?= esc($gasto['pagador_nombre']) ?></td>
                                            <td><?= esc($gasto['grupo_nombre']) ?></td>
                                            <td><?= $gasto['total_participantes'] ?></td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                                    <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                                    <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar gasto?')">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-md-none">
                <?php foreach ($gastos as $gasto): ?>
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-medium"><?= esc($gasto['descripcion']) ?></div>
                                <div class="fw-bold fs-5 text-primary">$<?= number_format($gasto['monto'], 2) ?></div>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= date('d/m/Y', strtotime($gasto['fecha'])) ?> &middot;
                                <?= esc($gasto['pagador_nombre']) ?>
                            </div>
                            <div class="text-muted small">Grupo: <?= esc($gasto['grupo_nombre']) ?> &middot; <?= $gasto['total_participantes'] ?> part.</div>
                            <div class="d-flex gap-2 mt-2">
                                <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-outline-info btn-sm flex-fill">Ver</a>
                                <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm flex-fill">Editar</a>
                                <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" class="flex-fill" onsubmit="return confirm('¿Eliminar gasto?')">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
