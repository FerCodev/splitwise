<?= view('partials/_head', ['title' => 'SplitWise - Categor&iacute;as']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Categor&iacute;as</h2>
            <a href="<?= base_url('categorias/nuevo') ?>" class="btn btn-primary">+ Nueva</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th class="text-end">Gastos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categorias)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No hay categor&iacute;as registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categorias as $c): ?>
                                    <tr>
                                        <td class="fw-medium">
                                            <?= esc($c['nombre']) ?>
                                            <?php if ($c['nombre'] === $protegida): ?>
                                                <span class="badge bg-secondary ms-1">Protegida</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($c['activa']): ?>
                                                <span class="badge bg-success">Activa</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactiva</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= $usadas[(int) $c['id']] ?? 0 ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <?php if ($c['nombre'] !== $protegida): ?>
                                                    <a href="<?= base_url('categorias/' . $c['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                                <?php endif; ?>
                                                <?php if ($c['nombre'] !== $protegida): ?>
                                                    <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-sm <?= $c['activa'] ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                                            <?= $c['activa'] ? 'Desactivar' : 'Activar' ?>
                                                        </button>
                                                    </form>
                                                    <?php if (($usadas[(int) $c['id']] ?? 0) === 0): ?>
                                                        <form action="<?= base_url('categorias/' . $c['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('&iquest;Eliminar categor&iacute;a?')">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?= view('partials/_footer') ?>
