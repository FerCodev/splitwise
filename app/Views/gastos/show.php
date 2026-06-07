<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - <?= esc($gasto['descripcion']) ?></title>
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
        <a href="<?= base_url('gastos') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver a Gastos</a>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="card-title mb-1"><?= esc($gasto['descripcion']) ?></h3>
                        <p class="text-muted mb-0">
                            Grupo: <a href="<?= base_url('grupos/' . $gasto['grupo_id']) ?>"><?= esc($gasto['grupo_nombre']) ?></a>
                            &middot; Fecha: <?= date('d/m/Y', strtotime($gasto['fecha'])) ?>
                        </p>
                    </div>
                    <div class="text-end">
                        <h4 class="text-primary mb-0">$<?= number_format($gasto['monto'], 2) ?></h4>
                        <small class="text-muted">Pagado por <?= esc($gasto['pagador_nombre']) ?></small>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                    <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar gasto?')">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Participantes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th class="text-end">Monto asignado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($participantes as $p): ?>
                                <tr>
                                    <td><?= esc($p['name']) ?>
                                        <?php if ($p['user_id'] == $gasto['pagador_id']): ?>
                                            <span class="badge bg-info ms-1">Pagó</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($p['email']) ?></td>
                                    <td class="text-end">$<?= number_format($p['monto_asignado'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2">Total</th>
                                <th class="text-end">$<?= number_format(array_sum(array_column($participantes, 'monto_asignado')), 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Resumen de saldos</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    <strong><?= esc($gasto['pagador_nombre']) ?></strong> pagó <strong>$<?= number_format($gasto['monto'], 2) ?></strong>
                    y debe recibir de los demás participantes.
                </p>
                <table class="table table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Debe</th>
                            <th>A</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participantes as $p): ?>
                            <?php if ($p['user_id'] != $gasto['pagador_id']): ?>
                                <tr>
                                    <td><?= esc($p['name']) ?></td>
                                    <td><?= esc($gasto['pagador_nombre']) ?></td>
                                    <td class="text-end">$<?= number_format($p['monto_asignado'], 2) ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
