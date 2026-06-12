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
            <div class="card-body p-0 d-none d-md-block">
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
                                                <span class="badge bg-secondary ms-1" title="Categor&iacute;a protegida: no se puede editar, desactivar ni eliminar">Protegida</span>
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
                                                <?php if ($c['activa']): ?>
                                                    <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="d-inline" id="toggle-cat-<?= $c['id'] ?>">
                                                        <?= csrf_field() ?>
                                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                            data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                            data-confirm-title="Desactivar categor&iacute;a"
                                                            data-confirm-msg="La categor&iacute;a dejar&aacute; de estar disponible para nuevos gastos, pero los gastos existentes la conservar&aacute;n."
                                                            data-confirm-btn="Desactivar"
                                                            data-confirm-form="toggle-cat-<?= $c['id'] ?>">Desactivar</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-sm btn-outline-success">Activar</button>
                                                    </form>
                                                <?php endif; ?>
                                                    <?php if (($usadas[(int) $c['id']] ?? 0) === 0): ?>
                                                        <form action="<?= base_url('categorias/' . $c['id']) ?>" method="post" class="d-inline" id="delete-cat-<?= $c['id'] ?>">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                                data-confirm-title="Eliminar categor&iacute;a"
                                                                data-confirm-msg="Se eliminar&aacute; la categor&iacute;a del sistema. Solo se puede eliminar si no est&aacute; usada por gastos."
                                                                data-confirm-btn="Eliminar"
                                                                data-confirm-form="delete-cat-<?= $c['id'] ?>">Eliminar</button>
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
            <div class="d-md-none">
                <?php if (empty($categorias)): ?>
                    <div class="card-body text-center py-4">
                        <p class="text-muted mb-0">No hay categor&iacute;as registradas.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($categorias as $c): ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="fw-medium"><?= esc($c['nombre']) ?></span>
                                    <?php if ($c['nombre'] === $protegida): ?>
                                        <span class="badge bg-secondary ms-1">Protegida</span>
                                        <div class="text-muted small mt-1">No se puede editar, desactivar ni eliminar.</div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($c['activa']): ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactiva</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-muted small mt-1"><?= $usadas[(int) $c['id']] ?? 0 ?> gasto(s)</div>
                            <div class="mt-2 d-flex gap-1 flex-wrap">
                                <?php if ($c['nombre'] !== $protegida): ?>
                                    <a href="<?= base_url('categorias/' . $c['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm flex-fill">Editar</a>
                                    <?php if ($c['activa']): ?>
                                        <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="flex-fill" id="toggle-cat-m-<?= $c['id'] ?>">
                                            <?= csrf_field() ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning w-100"
                                                data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                data-confirm-title="Desactivar categor&iacute;a"
                                                data-confirm-msg="La categor&iacute;a dejar&aacute; de estar disponible para nuevos gastos, pero los gastos existentes la conservar&aacute;n."
                                                data-confirm-btn="Desactivar"
                                                data-confirm-form="toggle-cat-m-<?= $c['id'] ?>">Desactivar</button>
                                        </form>
                                    <?php else: ?>
                                        <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="flex-fill">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-success w-100">Activar</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (($usadas[(int) $c['id']] ?? 0) === 0): ?>
                                        <form action="<?= base_url('categorias/' . $c['id']) ?>" method="post" class="flex-fill" id="delete-cat-m-<?= $c['id'] ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="button" class="btn btn-outline-danger btn-sm w-100"
                                                data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                data-confirm-title="Eliminar categor&iacute;a"
                                                data-confirm-msg="Se eliminar&aacute; la categor&iacute;a del sistema. Solo se puede eliminar si no est&aacute; usada por gastos."
                                                data-confirm-btn="Eliminar"
                                                data-confirm-form="delete-cat-m-<?= $c['id'] ?>">Eliminar</button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($pager)): ?>
            <div class="mt-4">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
