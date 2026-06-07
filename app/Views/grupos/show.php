<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - <?= esc($grupo['nombre']) ?></title>
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
        <a href="<?= base_url('grupos') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver a Grupos</a>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="card-title mb-1"><?= esc($grupo['nombre']) ?></h3>
                        <p class="text-muted mb-0">
                            Creado el <?= date('d/m/Y H:i', strtotime($grupo['created_at'])) ?>
                        </p>
                        <span class="badge bg-<?= $rol === 'admin' ? 'warning' : 'secondary' ?> mt-1">
                            <?= $rol === 'admin' ? 'Administrador' : 'Miembro' ?>
                        </span>
                    </div>
                    <?php if ($rol === 'admin'): ?>
                        <div>
                            <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="<?= base_url('grupos/' . $grupo['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar grupo?')">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($grupo['descripcion']): ?>
                    <p class="mt-3 mb-0"><?= esc($grupo['descripcion']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Miembros (<?= count($miembros) ?>)</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Desde</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($miembros as $miembro): ?>
                                <tr>
                                    <td><?= esc($miembro['name']) ?></td>
                                    <td><?= esc($miembro['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $miembro['rol'] === 'admin' ? 'warning' : 'secondary' ?>">
                                            <?= $miembro['rol'] === 'admin' ? 'Admin' : 'Miembro' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($miembro['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gastos (<?= count($gastos) ?>)</h5>
                <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary btn-sm">+ Nuevo Gasto</a>
            </div>
            <?php if (empty($gastos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay gastos en este grupo.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Monto</th>
                                    <th>Pagó</th>
                                    <th>Participantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastos as $gasto): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></td>
                                        <td><?= esc($gasto['descripcion']) ?></td>
                                        <td class="text-end">$<?= number_format($gasto['monto'], 2) ?></td>
                                        <td><?= esc($gasto['pagador_nombre']) ?></td>
                                        <td><?= $gasto['total_participantes'] ?></td>
                                        <td>
                                            <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total gastado</th>
                                    <th class="text-end">$<?= number_format($totalGastado, 2) ?></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pagos (<?= count($pagos) ?>)</h5>
                <a href="<?= base_url('pagos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-success btn-sm">+ Nuevo Pago</a>
            </div>
            <?php if (empty($pagos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay pagos en este grupo.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Monto</th>
                                    <th>Pagó</th>
                                    <th>Recibió</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pago): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                        <td><?= esc($pago['descripcion'] ?: '-') ?></td>
                                        <td class="text-end">$<?= number_format($pago['monto'], 2) ?></td>
                                        <td><?= esc($pago['pagador_nombre']) ?></td>
                                        <td><?= esc($pago['receptor_nombre']) ?></td>
                                        <td>
                                            <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total pagado</th>
                                    <th class="text-end">$<?= number_format($totalPagado, 2) ?></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Saldos</h5>
            </div>
            <?php if (empty($saldos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay saldos para mostrar.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-end">Pagó</th>
                                    <th class="text-end">Debe</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($saldos as $s): ?>
                                    <tr>
                                        <td><?= esc($s['name']) ?></td>
                                        <td class="text-end">$<?= number_format($s['pago'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($s['debe'], 2) ?></td>
                                        <td class="text-end <?= $s['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            $<?= number_format($s['saldo'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (!empty($deudas)): ?>
                    <div class="card-footer">
                        <h6 class="mb-2">Deudas</h6>
                        <?php foreach ($deudas as $d): ?>
                            <p class="mb-1">
                                <strong><?= esc($d['deudor']) ?></strong> debe
                                <strong>$<?= number_format($d['monto'], 2) ?></strong> a
                                <strong><?= esc($d['acreedor']) ?></strong>
                            </p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
