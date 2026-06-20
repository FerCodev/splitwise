<?= view('partials/_head', ['title' => 'SplitWise - Gastos']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Gastos']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0 d-none d-md-block">Gastos</h2>
            <a href="<?= base_url('gastos/nuevo') ?>" class="btn btn-primary d-none d-md-inline-flex">+ Nuevo</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (empty($gastos)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:64px;height:64px;background:#dbeafe;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#2563eb" viewBox="0 0 16 16"><path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5h-2v12h2V2zm-4-2v2h2V2H9zm-4 4v2h2V7H5zm-4 4v2h2v-2H1z"/></svg>
                    </div>
                    <p class="text-muted mb-0">No hay gastos registrados.</p>
                </div>
            </div>
        <?php else: ?>
            <!-- Desktop table -->
            <div class="d-none d-md-block">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Gastos</h5>
                        <button type="button" class="btn btn-primary feed-filter-btn" data-bs-toggle="modal" data-bs-target="#gastoFilterModal" aria-label="Filtros">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/></svg>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><a href="<?= base_url('gastos?sort=fecha&order=') ?><?= ($filters['sort'] ?? 'fecha') === 'fecha' && ($filters['order'] ?? 'DESC') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Fecha <?= ($filters['sort'] ?? 'fecha') === 'fecha' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Descripción</th>
                                        <th><a href="<?= base_url('gastos?sort=monto&order=') ?><?= ($filters['sort'] ?? '') === 'monto' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Monto <?= ($filters['sort'] ?? '') === 'monto' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Pagó</th>
                                        <th><a href="<?= base_url('gastos?sort=grupo_nombre&order=') ?><?= ($filters['sort'] ?? '') === 'grupo_nombre' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Grupo <?= ($filters['sort'] ?? '') === 'grupo_nombre' ? ($filters['order'] ?? 'DESC') === 'DESC' ? '↓' : '↑' : '' ?></a></th>
                                        <th>Categoría</th>
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
                                            <td><span class="badge bg-light text-dark"><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></span></td>
                                            <td><?= $gasto['total_participantes'] ?></td>
                                            <td>
                                                <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-sm btn-info">Ver detalle</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile cards -->
            <div class="d-md-none">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Gastos</h5>
                        <button type="button" class="btn btn-primary feed-filter-btn" data-bs-toggle="modal" data-bs-target="#gastoFilterModal" aria-label="Filtros">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/></svg>
                        </button>
                    </div>
                    <div class="card-body p-2" id="scroll-container">
                        <?php foreach ($gastos as $gasto): ?>
                            <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="card border-0 shadow-sm mb-2 mobile-transaction-card mobile-transaction-link" style="border-left: 3px solid #2563eb;">
                                <div class="card-body mobile-transaction-body">
                                    <div class="mobile-transaction-main">
                                        <div class="mobile-transaction-info">
                                            <div class="fw-medium mobile-transaction-title"><?= esc($gasto['descripcion']) ?></div>
                                            <div class="text-muted small mt-1">
                                                <?= date('d/m/Y', strtotime($gasto['fecha'])) ?> &middot;
                                                <?= esc($gasto['pagador_nombre']) ?>
                                            </div>
                                            <div class="text-muted small mobile-transaction-meta">
                                                <span class="badge bg-light text-dark"><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></span>
                                                Grupo: <?= esc($gasto['grupo_nombre']) ?> &middot; <?= $gasto['total_participantes'] ?> part.
                                            </div>
                                        </div>
                                        <div class="mobile-transaction-side">
                                            <div class="fw-bold fs-5 text-primary mobile-transaction-amount">$<?= number_format($gasto['monto'], 2) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="pagination-wrap mt-4">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('gastos/nuevo') ?>" class="fab fab-extended d-md-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
            <span>Nuevo gasto</span>
        </a>
    </div>

    <!-- Filtros -->
    <div class="modal fade" id="gastoFilterModal" tabindex="-1" aria-labelledby="gastoFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="get">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="gastoFilterModalLabel">Filtrar gastos</h5>
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
                            <label class="form-label">Categor&iacute;a</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= ($filters['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
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
                        <a href="<?= base_url('gastos') ?>" class="btn btn-secondary flex-fill">Limpiar</a>
                        <button type="submit" class="btn btn-primary flex-fill">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?= view('partials/_scroll_infinito') ?>
<?= view('partials/_footer') ?>
