<?= view('partials/_head', ['title' => 'SplitWise - Catálogo de tarjetas']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Catálogo Admin']) ?>

<div class="container mt-3 mt-md-4 pb-4">

    <h2 class="fw-bold mb-4 d-none d-md-block">Catálogo de tarjetas</h2>

    <?php $fechaDemo = '2026-04-15'; ?>

    <!-- 1. Grupo Activo -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">1. Grupo activo</h5>
            <span class="badge bg-success">Grupo</span>
        </div>
        <p class="small text-muted mb-2">Usado en Home (dashboard) y Grupos. Avatar circular, badge de estado y saldo. Fondo con gradiente suave.</p>
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:56px;height:56px;background:#dbeafe;font-weight:700;color:#2563eb;font-size:1.5rem;">M</div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h4 class="fw-bold mb-1">Mayo</h4>
                        </div>
                        <span class="badge bg-success">Activo</span>
                    </div>
                </div>
                <div class="mt-3 card border-0" style="background: #f0fdf4;">
                    <div class="card-body py-3 text-center">
                        <div class="small text-muted mb-1">Tu balance</div>
                        <div class="fw-bold fs-4 text-success"><?= moneda(12500) ?> <small class="fw-normal fs-6">a favor</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Grupo Cerrado -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">2. Grupo cerrado</h5>
            <span class="badge bg-warning text-dark">Grupo</span>
        </div>
        <p class="small text-muted mb-2">Mismo layout que activo pero con opacidad reducida y badge de estado diferente. Usado en Home y Grupos.</p>
        <div class="card border-0 shadow-sm" style="opacity:0.8; background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:56px;height:56px;background:#e2e8f0;font-weight:700;color:#64748b;font-size:1.5rem;">A</div>
                    <div class="flex-grow-1 min-width-0">
                        <h4 class="fw-bold mb-1">Abril</h4>
                        <span class="badge bg-warning text-dark">Cerrado</span>
                        <div class="small text-muted mt-2">Creado el 01/04/2026</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Gasto -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">3. Gasto</h5>
            <span class="badge bg-primary">Gasto</span>
        </div>
        <p class="small text-muted mb-2">Borde izquierdo azul con fondo celeste suave. Monto alineado a la derecha. Usado en Home, Gastos, Grupo (show) y Reportes.</p>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body report-movement-list">
                <a href="#" class="report-movement-link">
                    <div class="report-movement-card report-movement-expense" style="border-left: 3px solid #2563eb;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-width-0">
                                <span class="badge bg-primary me-1">Gasto</span>
                                <span class="fw-medium small">Supermercado mensual</span>
                            </div>
                            <span class="fw-bold small text-primary text-nowrap"><?= moneda(45200) ?></span>
                        </div>
                        <div class="text-muted small mt-1">
                            <?= date('d/m/Y', strtotime($fechaDemo)) ?> &middot; Fernando
                        </div>
                        <div class="text-muted small">
                            <span class="badge bg-light text-dark">Supermercado</span>
                            Grupo: Mayo &middot; 2 part.
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 4. Pago -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">4. Pago</h5>
            <span class="badge bg-success">Pago</span>
        </div>
        <p class="small text-muted mb-2">Borde izquierdo verde con fondo verde suave. Flecha indicando dirección del pago. Usado en Home, Pagos, Grupo (show) y Reportes.</p>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body report-movement-list">
                <a href="#" class="report-movement-link">
                    <div class="report-movement-card report-movement-payment" style="border-left: 3px solid #16a34a;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-width-0">
                                <span class="badge bg-success me-1">Pago</span>
                                <span class="fw-medium small">Pago</span>
                            </div>
                            <span class="fw-bold small text-success text-nowrap"><?= moneda(30000) ?></span>
                        </div>
                        <div class="text-muted small mt-1">
                            18/04/2026 &middot; Fernando pag&oacute; a Antonella
                        </div>
                        <div class="text-muted small">Grupo: Mayo</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 5. Deuda Pendiente -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">5. Deuda pendiente</h5>
            <span class="badge bg-danger">Deuda</span>
        </div>
        <p class="small text-muted mb-2">Borde de color según si debe (rojo) o le deben (verde). Avatar con flecha de dirección. Usado en Home (dashboard).</p>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body report-movement-list">
                <a href="#" class="report-movement-link">
                    <div class="report-movement-card report-movement-expense" style="border-left: 3px solid #dc2626;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-width-0">
                                <span class="badge bg-danger me-1">Deb&eacute;s</span>
                                <span class="fw-medium small">Deb&eacute;s a Antonella</span>
                            </div>
                            <span class="fw-bold small text-danger text-nowrap"><?= moneda(8500) ?></span>
                        </div>
                        <div class="text-muted small mt-1">Grupo: Mayo &middot; saldo pendiente</div>
                    </div>
                </a>
                <a href="#" class="report-movement-link">
                    <div class="report-movement-card report-movement-payment" style="border-left: 3px solid #16a34a;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-width-0">
                                <span class="badge bg-success me-1">Te deben</span>
                                <span class="fw-medium small">Te debe Fernando</span>
                            </div>
                            <span class="fw-bold small text-success text-nowrap"><?= moneda(12300) ?></span>
                        </div>
                        <div class="text-muted small mt-1">Grupo: Mayo &middot; saldo pendiente</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 6. Movimiento de grupo -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">6. Movimiento de grupo</h5>
            <span class="badge bg-info text-dark">Movimiento</span>
        </div>
        <p class="small text-muted mb-2">Card-header con título + filtro circular. Lista de report-movement-card. Usado en Grupo (show).</p>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Movimientos</h5>
                <button type="button" class="btn btn-primary feed-filter-btn" aria-label="Filtros">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/></svg>
                </button>
            </div>
            <div class="card-body report-movement-list">
                <a href="#" class="report-movement-link">
                    <div class="report-movement-card report-movement-expense" style="border-left: 3px solid #2563eb;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-width-0">
                                <span class="badge bg-primary me-1">Gasto</span>
                                <span class="fw-medium small">Alquiler</span>
                                <span class="badge bg-light text-dark ms-1">Servicios</span>
                            </div>
                            <span class="fw-bold small text-primary text-nowrap"><?= moneda(85000) ?></span>
                        </div>
                        <div class="text-muted small mt-1">01/04/2026 &middot; Fernando</div>
                    </div>
                </a>
                <a href="#" class="report-movement-link">
                    <div class="report-movement-card report-movement-payment" style="border-left: 3px solid #16a34a;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-width-0">
                                <span class="badge bg-success me-1">Pago</span>
                                <span class="fw-medium small">Pago</span>
                            </div>
                            <span class="fw-bold small text-success text-nowrap"><?= moneda(42500) ?></span>
                        </div>
                        <div class="text-muted small mt-1">05/04/2026 &middot; Antonella pag&oacute; a Fernando</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- 7. Total / Resumen -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">7. Total / Resumen</h5>
            <span class="badge bg-secondary">Resumen</span>
        </div>
        <p class="small text-muted mb-2">Balance strip con monto grande, dots de estado, detalle inline de debes/te deben. Usado en Home (desktop) y Dashboard.</p>
        <div class="dash-card mb-3">
            <div class="dash-card-body">
                <div class="balance-strip-label">Tu saldo total</div>
                <div class="balance-strip-amount text-success"><?= moneda(5200) ?> <span class="text-muted" style="font-size:14px;">a favor</span></div>
                <div class="balance-strip-detail">
                    <div class="balance-strip-detail-item">
                        <span class="status-dot status-dot-danger"></span>
                        <span class="text-muted">Deb&eacute;s:</span>
                        <span class="financial-amount text-danger"><?= moneda(8500) ?></span>
                    </div>
                    <div class="balance-strip-detail-item">
                        <span class="status-dot status-dot-active"></span>
                        <span class="text-muted">Te deben:</span>
                        <span class="financial-amount text-success"><?= moneda(13700) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 8. Medio de cobro -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">8. Medio de cobro</h5>
            <span class="badge bg-info text-dark">Pago</span>
        </div>
        <p class="small text-muted mb-2">Icono circular con color según estado. Estrella favorito en top-right. Copiar con icono de dos hojas. Usado en Medios de cobro.</p>
        <div class="card border-0 shadow-sm" style="border-left: 3px solid #16a34a;">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-2">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:42px;height:42px;background:#dcfce7;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#16a34a" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Zm-9 2a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1H6Z"/></svg>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="d-flex align-items-center gap-2 min-width-0">
                                <span class="fw-semibold text-truncate">CBU Santander</span>
                                <span class="badge bg-success flex-shrink-0">Activo</span>
                            </div>
                            <button type="button" class="medio-fav-btn medio-fav-active flex-shrink-0 ms-2" aria-label="Quitar favorito">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
                            </button>
                        </div>
                        <div class="small text-muted mb-2">
                            <div><span class="fw-medium">Titular:</span> Fernando</div>
                            <div class="d-flex align-items-center gap-1">
                                <span class="fw-medium">Alias:</span> <span class="text-truncate">fer.santander</span>
                                <button type="button" class="copiar-icon-btn" aria-label="Copiar alias">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-1 flex-wrap">
                    <a href="#" class="btn btn-primary btn-sm">Editar</a>
                    <button type="button" class="btn btn-warning btn-sm">Desactivar</button>
                    <button type="button" class="btn btn-danger btn-sm">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<?= view('partials/_footer') ?>
