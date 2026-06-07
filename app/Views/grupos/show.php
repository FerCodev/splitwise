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
            <a class="navbar-brand" href="/dashboard">SplitWise</a>
            <div class="d-flex align-items-center">
                <a href="/grupos" class="btn btn-outline-light btn-sm me-2">Grupos</a>
                <span class="navbar-text me-3"><?= session()->get('userName') ?></span>
                <a href="/logout" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <a href="/grupos" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver a Grupos</a>

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
                            <a href="/grupos/<?= $grupo['id'] ?>/editar" class="btn btn-outline-primary btn-sm">Editar</a>
                            <form action="/grupos/<?= $grupo['id'] ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar grupo?')">
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

        <div class="card">
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
    </div>
</body>
</html>
