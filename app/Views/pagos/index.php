<?= view('partials/_head', ['title' => 'SplitWise - Pagos']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Pagos']) ?>
<?php
    $pdfUrl = base_url('pagos/exportar-pdf?' . http_build_query($filters));
    $excelUrl = base_url('pagos/exportar-excel?' . http_build_query($filters));
    $totalFiltrado = $totalFiltrado ?? 0;
    $summaryVariant = \App\Services\UiComponentResolver::variant('pagos_index', 'filtered_total_card');
?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0 d-none d-md-block">Pagos</h2>
            <a href="<?= base_url('pagos/nuevo') ?>" class="btn btn-primary d-none d-md-inline-flex">+ Nuevo</a>
        </div>

        <?= view('partials/_feedback') ?>
        <div class="mb-3">
            <?= view('components/cards/resumen', [
                'variant' => $summaryVariant,
                'titulo' => 'Total filtrado',
                'monto' => $totalFiltrado,
                'detalle' => 'Suma de pagos filtrados',
                'color' => 'text-success',
            ]) ?>
        </div>

        <?php if (empty($pagos)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:64px;height:64px;background:#dcfce7;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#16a34a" viewBox="0 0 16 16"><path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V3zm1 0v10h12V3H2zm10.5 5.5a.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0 0 1h7a.5.5 0 0 0 .5-.5zm0 3a.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0 0 1h7a.5.5 0 0 0 .5-.5z"/></svg>
                    </div>
                    <p class="text-muted mb-0">No hay pagos registrados.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="d-none d-md-block">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex gap-1">
                            <a href="<?= $pdfUrl ?>" class="btn btn-danger btn-sm">PDF</a>
                            <a href="<?= $excelUrl ?>" class="btn btn-success btn-sm">Excel</a>
                        </div>
                        <button type="button" class="btn btn-primary feed-filter-btn flex-shrink-0" data-bs-toggle="modal" data-bs-target="#pagoFilterModal" aria-label="Filtros">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/></svg>
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th><a href="<?= base_url('pagos?sort=fecha&order=') ?><?= ($filters['sort'] ?? 'fecha') === 'fecha' && ($filters['order'] ?? 'DESC') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Fecha <?= ($filters['sort'] ?? 'fecha') === 'fecha' ? ($filters['order'] ?? 'DESC') === 'DESC' ? 'â†“' : 'â†‘' : '' ?></a></th>
                                        <th>DescripciÃ³n</th>
                                        <th><a href="<?= base_url('pagos?sort=monto&order=') ?><?= ($filters['sort'] ?? '') === 'monto' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Monto <?= ($filters['sort'] ?? '') === 'monto' ? ($filters['order'] ?? 'DESC') === 'DESC' ? 'â†“' : 'â†‘' : '' ?></a></th>
                                        <th>PagÃ³</th>
                                        <th>RecibiÃ³</th>
                                        <th><a href="<?= base_url('pagos?sort=grupo_nombre&order=') ?><?= ($filters['sort'] ?? '') === 'grupo_nombre' && ($filters['order'] ?? '') === 'DESC' ? 'ASC' : 'DESC' ?>&amp;<?= http_build_query(array_diff_key($filters, ['sort' => '', 'order' => ''])) ?>" class="text-decoration-none text-dark">Grupo <?= ($filters['sort'] ?? '') === 'grupo_nombre' ? ($filters['order'] ?? 'DESC') === 'DESC' ? 'â†“' : 'â†‘' : '' ?></a></th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagos as $pago): ?>
                                        <tr>
                                            <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                            <td><?= esc($pago['descripcion'] ?: '-') ?></td>
                                            <td class="fw-medium"><?= moneda($pago['monto']) ?></td>
                                            <td><?= esc($pago['pagador_nombre']) ?></td>
                                            <td><?= esc($pago['receptor_nombre']) ?></td>
                                            <td><?= esc($pago['grupo_nombre']) ?></td>
                                            <td>
                                                <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-sm btn-info">Ver detalle</a>
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
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex gap-1">
                            <a href="<?= $pdfUrl ?>" class="btn btn-danger btn-sm">PDF</a>
                            <a href="<?= $excelUrl ?>" class="btn btn-success btn-sm">Excel</a>
                        </div>
                        <button type="button" class="btn btn-primary feed-filter-btn flex-shrink-0" data-bs-toggle="modal" data-bs-target="#pagoFilterModal" aria-label="Filtros">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/></svg>
                        </button>
                    </div>
                    <div class="card-body p-2" id="scroll-container">
                        <?php foreach ($pagos as $pago): ?>
                            <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="card border-0 shadow-sm mb-2 mobile-transaction-card mobile-transaction-payment mobile-transaction-link" style="border-left: 3px solid #16a34a;">
                                <div class="card-body mobile-transaction-body">
                                    <div class="mobile-transaction-main">
                                        <div class="mobile-transaction-info">
                                            <div class="fw-medium mobile-transaction-title"><?= esc($pago['descripcion'] ?: 'Pago') ?></div>
                                            <div class="text-muted small mt-1">
                                                <?= date('d/m/Y', strtotime($pago['fecha'])) ?> &middot;
                                                <?= esc($pago['pagador_nombre']) ?> &rarr; <?= esc($pago['receptor_nombre']) ?>
                                            </div>
                                            <div class="text-muted small mobile-transaction-meta">Grupo: <?= esc($pago['grupo_nombre']) ?></div>
                                        </div>
                                        <div class="mobile-transaction-side">
                                            <div class="fw-bold fs-5 text-success mobile-transaction-amount"><?= moneda($pago['monto']) ?></div>
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
            <div class="pagination-wrap infinite-scroll-pagination mt-4">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('pagos/nuevo') ?>" class="fab fab-extended d-lg-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
            <span>Nuevo pago</span>
        </a>
    </div>

    <div class="modal fade" id="pagoFilterModal" tabindex="-1" aria-labelledby="pagoFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="get">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="pagoFilterModalLabel">Filtrar pagos</h5>
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

<?= view('partials/_scroll_infinito') ?>
<?= view('partials/_footer') ?>
