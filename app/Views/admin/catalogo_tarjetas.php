<?= view('partials/_head', ['title' => 'SplitWise - Catálogo de tarjetas']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Catálogo Admin']) ?>

<div class="container mt-3 mt-md-4 pb-4">

    <h2 class="fw-bold mb-4 d-none d-md-block">Catálogo de tarjetas</h2>

    <!-- Grupo Activo -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0">Tarjeta de grupo activo</h5>
                <span class="badge bg-success">Activo</span>
            </div>
            <small class="text-muted">Usado en: Home, Grupos</small>
        </div>
        <p class="small text-muted">Estilo actual con avatar circular, badge de estado y saldo. Fondo con gradiente suave.</p>
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:56px;height:56px;background:#dbeafe;font-weight:700;color:#2563eb;font-size:1.5rem;">M</div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h4 class="fw-bold mb-1">Mayo</h4>
                            <a href="#" class="grupo-gear-btn flex-shrink-0 ms-2" aria-label="Configurar grupo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8.932.727c-.243-.97-1.62-.97-1.864 0l-.071.286a.96.96 0 0 1-1.622.434l-.205-.211c-.695-.719-1.888-.03-1.613.931l.08.284a.96.96 0 0 1-1.186 1.187l-.284-.081c-.96-.275-1.65.918-.931 1.613l.211.205a.96.96 0 0 1-.434 1.622l-.286.071c-.97.243-.97 1.62 0 1.864l.286.071a.96.96 0 0 1 .434 1.622l-.211.205c-.719.695-.03 1.888.931 1.613l.284-.08a.96.96 0 0 1 1.187 1.187l-.081.283c-.275.96.918 1.65 1.613.931l.205-.211a.96.96 0 0 1 1.622.434l.071.286c.243.97 1.62.97 1.864 0l.071-.286a.96.96 0 0 1 1.622-.434l.205.211c.695.719 1.888.03 1.613-.931l-.08-.284a.96.96 0 0 1 1.187-1.187l.283.081c.96.275 1.65-.918.931-1.613l-.211-.205a.96.96 0 0 1 .434-1.622l.286-.071c.97-.243.97-1.62 0-1.864l-.286-.071a.96.96 0 0 1-.434-1.622l.211-.205c.719-.695.03-1.888-.931-1.613l-.284.08a.96.96 0 0 1-1.187-1.186l.081-.284c.275-.96-.918-1.65-1.613-.931l-.205.211a.96.96 0 0 1-1.622-.434L8.932.727zM8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/></svg>
                            </a>
                        </div>
                        <span class="badge bg-success">Activo</span>
                    </div>
                </div>
                <div class="mt-3 card border-0" style="background: #f0fdf4;">
                    <div class="card-body py-3 text-center">
                        <div class="small text-muted mb-1">Tu balance</div>
                        <div class="fw-bold fs-4 text-success">$12,500.00 <small class="fw-normal fs-6">a favor</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grupo Cerrado -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">Tarjeta de grupo cerrado</h5>
            <span class="badge bg-warning text-dark">Cerrado</span>
            <small class="text-muted ms-auto">Usado en: Home, Grupos</small>
        </div>
        <p class="small text-muted">Mismo layout que activo pero con opacidad reducida y badge de estado diferente.</p>
        <div class="card border-0 shadow-sm" style="opacity:0.8; background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:56px;height:56px;background:#e2e8f0;font-weight:700;color:#64748b;font-size:1.5rem;">A</div>
                    <div class="flex-grow-1 min-width-0">
                        <h4 class="fw-bold mb-1 text-muted">Abril</h4>
                        <span class="badge bg-warning text-dark">Cerrado</span>
                        <div class="small text-muted mt-1">2 transferencias pendientes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Gasto -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">Tarjeta de gasto</h5>
            <span class="badge bg-primary">Gasto</span>
            <small class="text-muted ms-auto">Usado en: Home, Gastos, Grupo</small>
        </div>
        <p class="small text-muted">Borde izquierdo azul, monto a la derecha. Badge de categoria y avatar de pagador.</p>
        <a href="#" class="card border-0 shadow-sm mb-2 mobile-transaction-card mobile-transaction-link d-block" style="border-left: 3px solid #2563eb; text-decoration:none; color:inherit;">
            <div class="card-body mobile-transaction-body">
                <div class="mobile-transaction-main">
                    <div class="mobile-transaction-info">
                        <div class="fw-medium mobile-transaction-title">Supermercado mensual</div>
                        <div class="text-muted small mt-1">15/04/2026 &middot; Fernando</div>
                        <div class="text-muted small mobile-transaction-meta">
                            <span class="badge bg-light text-dark">Supermercado</span>
                            Grupo: Mayo &middot; 2 part.
                        </div>
                    </div>
                    <div class="mobile-transaction-side">
                        <div class="fw-bold fs-5 text-primary mobile-transaction-amount">$45,200.00</div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta de Pago -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">Tarjeta de pago</h5>
            <span class="badge bg-success">Pago</span>
            <small class="text-muted ms-auto">Usado en: Home, Pagos, Grupo</small>
        </div>
        <p class="small text-muted">Borde izquierdo verde, flecha indicando dirección del pago.</p>
        <a href="#" class="card border-0 shadow-sm mb-2 mobile-transaction-card mobile-transaction-link d-block" style="border-left: 3px solid #16a34a; text-decoration:none; color:inherit;">
            <div class="card-body mobile-transaction-body">
                <div class="mobile-transaction-main">
                    <div class="mobile-transaction-info">
                        <div class="fw-medium mobile-transaction-title">Pago</div>
                        <div class="text-muted small mt-1">18/04/2026 &middot; Fernando → Antonella</div>
                        <div class="text-muted small mobile-transaction-meta">Grupo: Mayo</div>
                    </div>
                    <div class="mobile-transaction-side">
                        <div class="fw-bold fs-5 text-success mobile-transaction-amount">$30,000.00</div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta de Deuda Pendiente -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">Tarjeta de deuda pendiente</h5>
            <span class="badge bg-danger">Deuda</span>
            <small class="text-muted ms-auto">Usado en: Home, Dashboard</small>
        </div>
        <p class="small text-muted">Borde rojo, avatar con flecha indicando si debe o le deben. Click lleva al balance del grupo.</p>
        <a href="#" class="card border-0 shadow-sm mb-2 feed-card d-block text-decoration-none" style="border-left: 3px solid #dc2626;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm" style="background:var(--danger-light);color:var(--danger);">↑</div>
                        <div>
                            <span class="fw-medium small">Debés a Antonella</span>
                            <span class="text-muted small ms-1">Mayo</span>
                        </div>
                    </div>
                    <span class="fw-bold small text-danger">$8,500.00</span>
                </div>
            </div>
        </a>
        <a href="#" class="card border-0 shadow-sm mb-2 feed-card d-block text-decoration-none" style="border-left: 3px solid #16a34a;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-sm" style="background:var(--accent-light);color:var(--accent);">↓</div>
                        <div>
                            <span class="fw-medium small">Te debe Fernando</span>
                            <span class="text-muted small ms-1">Mayo</span>
                        </div>
                    </div>
                    <span class="fw-bold small text-success">$12,300.00</span>
                </div>
            </div>
        </a>
    </div>

    <!-- Tarjeta de Movimiento -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">Tarjeta de movimiento</h5>
            <span class="badge bg-info text-dark">Movimiento</span>
            <small class="text-muted ms-auto">Usado en: Grupo (show), Reportes</small>
        </div>
        <p class="small text-muted">Lista plana con borde de color por tipo, badge y metadata en una línea.</p>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <a href="#" class="dash-list-item dash-list-item-bordered text-decoration-none" style="border-left:3px solid var(--primary);">
                    <div class="dash-list-item-info">
                        <div class="dash-list-item-title">Supermercado mensual</div>
                        <div class="dash-list-item-subtitle">15/04/2026 &middot; Fernando &middot; Mayo</div>
                    </div>
                    <div class="dash-list-item-amount text-primary">$45,200.00</div>
                </a>
                <a href="#" class="dash-list-item dash-list-item-bordered text-decoration-none" style="border-left:3px solid var(--accent);">
                    <div class="dash-list-item-info">
                        <div class="dash-list-item-title">Pago</div>
                        <div class="dash-list-item-subtitle">18/04/2026 &middot; Fernando → Antonella</div>
                    </div>
                    <div class="dash-list-item-amount text-success">$30,000.00</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Total / Resumen -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">Tarjeta de total / resumen</h5>
            <span class="badge bg-secondary">Resumen</span>
            <small class="text-muted ms-auto">Usado en: Home, Reportes</small>
        </div>
        <p class="small text-muted">Balance strip con monto grande, dots de estado y detalle inline de debes/te deben.</p>
        <div class="dash-card mb-3">
            <div class="dash-card-body">
                <div class="balance-strip-label">Tu saldo total</div>
                <div class="balance-strip-amount text-success">$5,200.00 <span class="text-muted" style="font-size:14px;">a favor</span></div>
                <div class="balance-strip-detail">
                    <div class="balance-strip-detail-item">
                        <span class="status-dot status-dot-danger"></span>
                        <span class="text-muted">Debés:</span>
                        <span class="financial-amount text-danger">$8,500.00</span>
                    </div>
                    <div class="balance-strip-detail-item">
                        <span class="status-dot status-dot-active"></span>
                        <span class="text-muted">Te deben:</span>
                        <span class="financial-amount text-success">$13,700.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Medio de Cobro -->
    <div class="mb-5">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0">Tarjeta de medio de cobro</h5>
            <span class="badge bg-info text-dark">Pago</span>
            <small class="text-muted ms-auto">Usado en: Medios de cobro</small>
        </div>
        <p class="small text-muted">Icono circular, estrella favorito top-right, estado como badge, copiar con icono.</p>
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
                    <button class="btn btn-warning btn-sm">Desactivar</button>
                    <button class="btn btn-danger btn-sm">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<?= view('partials/_footer') ?>
