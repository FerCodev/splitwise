<?= view('partials/_head', ['title' => 'SplitWise - Pagos']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Pagos']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0 d-none d-md-block">Pagos</h2>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#pagoFilterModal">
                    Filtrar
                </button>
                <a href="<?= base_url('pagos/nuevo') ?>" class="btn btn-primary btn-sm d-none d-md-inline-flex">+ Nuevo</a>
            </div>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (empty($pagos)): ?>
            <div class="empty-state">
                <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V3zm1 0v10h12V3H2z"/></svg>
                <div class="empty-state-title">No hay pagos registrados</div>
                <div class="empty-state-text">Registr&aacute; tu primer pago para saldar deudas.</div>
                <a href="<?= base_url('pagos/nuevo') ?>" class="btn btn-primary">Nuevo pago</a>
            </div>
        <?php else: ?>
            <!-- Desktop table -->
            <div class="card d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><a href="<?= base_url('pagos?sort=fecha&order=') ?><?= ($filters['sort'] ?? 'fecha') === 'fecha' && ($filters['order'] ?? 'DESC') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Fecha <?= ($filters['sort'] ?? 'fecha') === 'fecha' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                <th>Descripci&oacute;n</th>
                                <th><a href="<?= base_url('pagos?sort=monto&order=') ?><?= ($filters['sort'] ?? '') === 'monto' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Monto <?= ($filters['sort'] ?? '') === 'monto' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                <th>Pag&oacute;</th>
                                <th>Recibi&oacute;</th>
                                <th><a href="<?= base_url('pagos?sort=grupo_nombre&order=') ?><?= ($filters['sort'] ?? '') === 'grupo_nombre' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Grupo <?= ($filters['sort'] ?? '') === 'grupo_nombre' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pagos as $pago): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                    <td><?= esc($pago['descripcion'] ?: '-') ?></td>
                                    <td class="financial-amount text-success">$<?= number_format($pago['monto'], 2) ?></td>
                                    <td><?= esc($pago['pagador_nombre']) ?></td>
                                    <td><?= esc($pago['receptor_nombre']) ?></td>
                                    <td><?= esc($pago['grupo_nombre']) ?></td>
                                    <td><a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-info btn-sm">Ver</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile list -->
            <div class="card d-md-none">
                <?php foreach ($pagos as $pago): ?>
                    <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="financial-list-item financial-list-item-clickable text-decoration-none" style="color:inherit;border-left:3px solid var(--accent);">
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title" style="font-size:14px;"><?= esc($pago['descripcion'] ?: 'Pago') ?></div>
                            <div class="financial-list-item-subtitle">
                                <?= date('d/m/Y', strtotime($pago['fecha'])) ?> &middot;
                                <?= esc($pago['pagador_nombre']) ?> &rarr; <?= esc($pago['receptor_nombre']) ?>
                                &middot; <?= esc($pago['grupo_nombre']) ?>
                            </div>
                        </div>
                        <div class="financial-list-item-amount text-success">
                            $<?= number_format($pago['monto'], 2) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($pager)): ?>
            <div class="pagination-wrap mt-3">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('pagos/nuevo') ?>" class="d-md-none fab fab-extended" aria-label="Nuevo pago">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
            <span>Nuevo pago</span>
        </a>
    </div>

    <div class="modal fade" id="pagoFilterModal" tabindex="-1" aria-labelledby="pagoFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="get">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pagoFilterModalLabel">Filtrar pagos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Grupo</label>
                            <select name="grupo_id" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($grupos as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ($filters['grupo_id'] ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripci&oacute;n</label>
                            <input type="text" name="descripcion" class="form-control" placeholder="Buscar..." value="<?= esc($filters['descripcion'] ?? '') ?>">
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" value="<?= esc($filters['fecha_desde'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control" value="<?= esc($filters['fecha_hasta'] ?? '') ?>">
                            </div>
                        </div>
                        <input type="hidden" name="sort" value="<?= esc($filters['sort'] ?? 'fecha') ?>">
                        <input type="hidden" name="order" value="<?= esc($filters['order'] ?? 'DESC') ?>">
                    </div>
                    <div class="modal-footer">
                        <a href="<?= base_url('pagos') ?>" class="btn btn-secondary flex-fill">Limpiar</a>
                        <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= view('partials/_footer') ?>
