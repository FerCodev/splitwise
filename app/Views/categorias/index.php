<?= view('partials/_head', ['title' => 'SplitWise - Categor&iacute;as']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Categor&iacute;as']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0 d-none d-md-block">Categor&iacute;as</h2>
            <a href="<?= base_url('categorias/nuevo') ?>" class="btn btn-primary btn-sm d-none d-md-inline-flex">+ Nueva</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (empty($categorias)): ?>
            <div class="empty-state">
                <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.393-.13-.803-.264-1.186-.406-1.078-.401-1.868-.465-2.604-.138v-6.972c0-.507-.133-.954-.4-1.358a1.2 1.2 0 0 0-.479-.458c-.295-.166-.651-.19-1.078-.093-.47.106-.896.336-1.356.628V2.828z"/></svg>
                <div class="empty-state-title">No hay categor&iacute;as</div>
                <div class="empty-state-text">Cre&aacute; categor&iacute;as para organizar tus gastos.</div>
                <a href="<?= base_url('categorias/nuevo') ?>" class="btn btn-primary">Nueva categor&iacute;a</a>
            </div>
        <?php else: ?>
            <!-- Desktop table -->
            <div class="card d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th class="text-end">Gastos</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $c): ?>
                                <tr>
                                    <td class="fw-semibold">
                                        <?= esc($c['nombre']) ?>
                                        <?php if ($c['nombre'] === $protegida): ?>
                                            <span class="badge bg-secondary" style="font-size:10px;padding:2px 6px;margin-left:4px;">Protegida</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $c['activa'] ? 'bg-success' : 'bg-danger' ?>"><?= $c['activa'] ? 'Activa' : 'Inactiva' ?></span>
                                    </td>
                                    <td class="text-end"><?= $usadas[(int) $c['id']] ?? 0 ?></td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <?php if ($c['nombre'] !== $protegida): ?>
                                                <a href="<?= base_url('categorias/' . $c['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a>
                                                <?php if ($c['activa']): ?>
                                                    <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="d-inline" id="toggle-cat-<?= $c['id'] ?>">
                                                        <?= csrf_field() ?>
                                                        <button type="button" class="btn btn-warning btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                            data-confirm-title="Desactivar categor&iacute;a"
                                                            data-confirm-msg="La categor&iacute;a dejar&aacute; de estar disponible para nuevos gastos, pero los gastos existentes la conservar&aacute;n."
                                                            data-confirm-btn="Desactivar"
                                                            data-confirm-form="toggle-cat-<?= $c['id'] ?>">Desactivar</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-success btn-sm">Activar</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if (($usadas[(int) $c['id']] ?? 0) === 0): ?>
                                                    <form action="<?= base_url('categorias/' . $c['id']) ?>" method="post" class="d-inline" id="delete-cat-<?= $c['id'] ?>">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="button" class="btn btn-danger btn-sm"
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
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile list -->
            <div class="card d-md-none">
                <?php foreach ($categorias as $c): ?>
                    <div class="financial-list-item" style="flex-wrap:wrap;">
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title" style="font-size:14px;">
                                <?= esc($c['nombre']) ?>
                                <?php if ($c['nombre'] === $protegida): ?>
                                    <span class="badge bg-secondary" style="font-size:10px;padding:2px 6px;">Protegida</span>
                                <?php endif; ?>
                                <span class="badge <?= $c['activa'] ? 'bg-success' : 'bg-danger' ?>" style="font-size:10px;padding:2px 6px;"><?= $c['activa'] ? 'Activa' : 'Inactiva' ?></span>
                            </div>
                            <div class="financial-list-item-subtitle"><?= $usadas[(int) $c['id']] ?? 0 ?> gasto(s)</div>
                        </div>
                        <div class="d-flex gap-1 w-100 mt-2">
                            <?php if ($c['nombre'] !== $protegida): ?>
                                <a href="<?= base_url('categorias/' . $c['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a>
                                <?php if ($c['activa']): ?>
                                    <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="d-inline" id="toggle-cat-m-<?= $c['id'] ?>">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn btn-warning btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#confirmModal"
                                            data-confirm-title="Desactivar categor&iacute;a"
                                            data-confirm-msg="La categor&iacute;a dejar&aacute; de estar disponible para nuevos gastos, pero los gastos existentes la conservar&aacute;n."
                                            data-confirm-btn="Desactivar"
                                            data-confirm-form="toggle-cat-m-<?= $c['id'] ?>">Desactivar</button>
                                    </form>
                                <?php else: ?>
                                    <form action="<?= base_url('categorias/' . $c['id'] . '/toggle') ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-success btn-sm">Activar</button>
                                    </form>
                                <?php endif; ?>
                                <?php if (($usadas[(int) $c['id']] ?? 0) === 0): ?>
                                    <form action="<?= base_url('categorias/' . $c['id']) ?>" method="post" class="d-inline" id="delete-cat-m-<?= $c['id'] ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="button" class="btn btn-danger btn-sm"
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
            </div>
        <?php endif; ?>

        <a href="<?= base_url('categorias/nuevo') ?>" class="d-md-none fab" aria-label="Nueva categor&iacute;a">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
        </a>

        <?php if (isset($pager)): ?>
            <div class="pagination-wrap mt-3">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
