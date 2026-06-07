<?= view('partials/_head', ['title' => 'SplitWise - Pagos']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Pagos</h2>
            <a href="<?= base_url('pagos/nuevo') ?>" class="btn btn-primary">+ Nuevo</a>
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
                        <a href="<?= base_url('pagos') ?>" class="btn btn-outline-secondary flex-fill">Limpiar</a>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($pagos)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-0">No hay pagos registrados.</p>
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
                                        <th><a href="<?= base_url('pagos?sort=fecha&order=') ?><?= ($filters['sort'] ?? 'fecha') === 'fecha' && ($filters['order'] ?? 'DESC') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Fecha <?= ($filters['sort'] ?? 'fecha') === 'fecha' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Descripción</th>
                                        <th><a href="<?= base_url('pagos?sort=monto&order=') ?><?= ($filters['sort'] ?? '') === 'monto' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Monto <?= ($filters['sort'] ?? '') === 'monto' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Pagó</th>
                                        <th>Recibió</th>
                                        <th><a href="<?= base_url('pagos?sort=grupo_nombre&order=') ?><?= ($filters['sort'] ?? '') === 'grupo_nombre' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?><?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Grupo <?= ($filters['sort'] ?? '') === 'grupo_nombre' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagos as $pago): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                            <td><?= esc($pago['descripcion'] ?: '-') ?></td>
                                            <td class="fw-medium">$<?= number_format($pago['monto'], 2) ?></td>
                                            <td><?= esc($pago['pagador_nombre']) ?></td>
                                            <td><?= esc($pago['receptor_nombre']) ?></td>
                                            <td><?= esc($pago['grupo_nombre']) ?></td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                                    <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                                    <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar pago?')">
                                                        <?= csrf_field() ?>
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
                <?php foreach ($pagos as $pago): ?>
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-medium"><?= esc($pago['descripcion'] ?: 'Pago') ?></div>
                                <div class="fw-bold fs-5 text-success">$<?= number_format($pago['monto'], 2) ?></div>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= date('d/m/Y', strtotime($pago['fecha'])) ?> &middot;
                                <?= esc($pago['pagador_nombre']) ?> &rarr; <?= esc($pago['receptor_nombre']) ?>
                            </div>
                            <div class="text-muted small">Grupo: <?= esc($pago['grupo_nombre']) ?></div>
                            <div class="d-flex gap-2 mt-2">
                                <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-outline-info btn-sm flex-fill">Ver</a>
                                <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm flex-fill">Editar</a>
                                <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" class="flex-fill" onsubmit="return confirm('¿Eliminar pago?')">
                                    <?= csrf_field() ?>
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

<?= view('partials/_footer') ?>
