<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - Gastos</title>
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

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gastos</h2>
            <a href="<?= base_url('gastos/nuevo') ?>" class="btn btn-primary">+ Nuevo Gasto</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Grupo</label>
                        <select name="grupo_id" class="form-select">
                            <option value="">Todos los grupos</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= ($filters['grupo_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fecha hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="descripcion" class="form-control" placeholder="Buscar..." value="<?= esc($filters['descripcion'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                        <a href="<?= base_url('gastos') ?>" class="btn btn-outline-secondary w-100">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($gastos)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-0">No hay gastos registrados.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>
                                        <a href="<?= base_url('gastos?sort=fecha&order=') ?><?= ($filters['sort'] ?? 'fecha') === 'fecha' && ($filters['order'] ?? 'DESC') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">
                                            Fecha <?= ($filters['sort'] ?? 'fecha') === 'fecha' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?>
                                        </a>
                                    </th>
                                    <th>Descripción</th>
                                    <th>
                                        <a href="<?= base_url('gastos?sort=monto&order=') ?><?= ($filters['sort'] ?? '') === 'monto' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">
                                            Monto <?= ($filters['sort'] ?? '') === 'monto' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?>
                                        </a>
                                    </th>
                                    <th>Pagó</th>
                                    <th>
                                        <a href="<?= base_url('gastos?sort=grupo_nombre&order=') ?><?= ($filters['sort'] ?? '') === 'grupo_nombre' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">
                                            Grupo <?= ($filters['sort'] ?? '') === 'grupo_nombre' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?>
                                        </a>
                                    </th>
                                    <th>Participantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastos as $gasto): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></td>
                                        <td><?= esc($gasto['descripcion']) ?></td>
                                        <td>$<?= number_format($gasto['monto'], 2) ?></td>
                                        <td><?= esc($gasto['pagador_nombre']) ?></td>
                                        <td><?= esc($gasto['grupo_nombre']) ?></td>
                                        <td><?= $gasto['total_participantes'] ?></td>
                                        <td>
                                            <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                            <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                            <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar gasto?')">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
