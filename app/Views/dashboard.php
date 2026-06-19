<?= view('partials/_head', ['title' => 'SplitWise - Dashboard']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Home']) ?>

    <div class="container mt-3 mt-md-4">

        <!-- Acciones principales -->
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary d-none d-md-inline-flex">+ Nuevo Grupo</a>
            <a href="<?= base_url('reportes') ?>" class="btn btn-outline-primary d-none d-md-inline-flex">Reportes</a>
            <a href="<?= base_url('mis-medios-de-cobro') ?>" class="btn btn-outline-secondary d-none d-md-inline-flex">Medios de cobro</a>
        </div>

        <?php if (empty($grupos)): ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-3">No ten&eacute;s grupos a&uacute;n. Cre&aacute; uno nuevo.</p>
                    <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">Crear Grupo</a>
                </div>
            </div>

        <?php else: ?>

            <?php
                $activos = array_filter($grupos, fn($g) => $g['estado'] === 'activo');
                $inactivos = array_filter($grupos, fn($g) => $g['estado'] !== 'activo');
                $userId = (int) session()->get('userId');
            ?>

            <!-- Resumen superior -->
            <div class="mb-4">
                <div class="collapse d-md-block" id="resumenCollapse">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <a href="<?= base_url('grupos') ?>" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #eff6ff 0%, #fff 100%);">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:40px;height:40px;background:#dbeafe;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#2563eb" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                                        </div>
                                        <div class="min-width-0">
                                            <div class="small text-muted lh-1">Grupos activos</div>
                                            <div class="fw-bold fs-5 text-primary"><?= $cantidadActivos ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="<?= base_url('reportes') ?>" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, <?= $globalSaldo >= 0 ? '#f0fdf4' : '#fef2f2' ?> 0%, #fff 100%);">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:40px;height:40px;background:<?= $globalSaldo >= 0 ? '#dcfce7' : '#fee2e2' ?>;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="<?= $globalSaldo >= 0 ? '#16a34a' : '#dc2626' ?>" viewBox="0 0 16 16"><path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-6 8c0 1 1 1 1 1h6s1 0 1-1-1-4-6-4-6 3-6 4Zm10-8a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm0 3a1 1 0 1 0 0 2 1 1 0 0 0 0-2ZM3.5 2a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm0 3a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/></svg>
                                        </div>
                                        <div class="min-width-0">
                                            <div class="small text-muted lh-1">Saldo total</div>
                                            <div class="fw-bold fs-5 <?= $globalSaldo >= 0 ? 'text-success' : 'text-danger' ?>">
                                                $<?= number_format(abs($globalSaldo), 0) ?>
                                                <small class="fw-normal text-muted fs-6"><?= $globalSaldo >= 0 ? 'a favor' : 'deb&eacute;s' ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="#deudas-section" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:40px;height:40px;background:#fee2e2;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#dc2626" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Zm-9 2a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1H6Z"/></svg>
                                        </div>
                                        <div class="min-width-0">
                                            <div class="small text-muted lh-1">Deb&#233;s</div>
                                            <div class="fw-bold fs-5 text-danger">$<?= number_format($totalDebe, 0) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="#deudas-section" class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f0fdf4 0%, #fff 100%);">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:40px;height:40px;background:#dcfce7;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#16a34a" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5ZM5.5 9a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5Z"/></svg>
                                        </div>
                                        <div class="min-width-0">
                                            <div class="small text-muted lh-1">Te deben</div>
                                            <div class="fw-bold fs-5 text-success">$<?= number_format($totalLeDeben, 0) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros del feed -->
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <span class="fw-bold small text-muted d-none d-md-inline">Grupos</span>
                <div class="filter-tabs flex-grow-1">
                    <button class="filter-tab active" data-filter="todos" onclick="filtrarGrupos('todos', this)">Todos</button>
                    <button class="filter-tab" data-filter="activos" onclick="filtrarGrupos('activos', this)">Activos</button>
                    <button class="filter-tab" data-filter="cerrados" onclick="filtrarGrupos('cerrados', this)">Cerrados</button>
                </div>
            </div>

            <div id="grupos-list">
                <!-- Grupos activos -->
                <div class="filter-section" data-section="activos">
                    <?php if (empty($activos)): ?>
                        <p class="text-muted small mb-3">No ten&eacute;s grupos activos.</p>
                    <?php else: ?>
                        <div class="row g-3 mb-4">
                            <?php foreach ($activos as $grupo): ?>
                                <?php
                                    $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : 'text-secondary');
                                    $mv = $grupo['ultimo_movimiento'];
                                ?>
                                <div class="col-12 col-md-6 col-lg-4" data-estado="activo">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-start gap-3 mb-2">
                                                <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:42px;height:42px;background:#dbeafe;font-weight:700;color:#2563eb;font-size:1.1rem;">
                                                    <?= esc(mb_substr($grupo['nombre'], 0, 1)) ?>
                                                </div>
                                                <div class="flex-grow-1 min-width-0">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <h6 class="fw-bold mb-0"><?= esc($grupo['nombre']) ?></h6>
                                                        <span class="badge bg-success flex-shrink-0">Activo</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between small mt-1">
                                                        <span class="text-muted">Saldo:</span>
                                                        <span class="fw-semibold <?= $saldoClase ?>">$<?= number_format($grupo['mi_saldo'], 2) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($mv): ?>
                                                <div class="small mt-2">
                                                    <span class="badge <?= $mv['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>"><?= $mv['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                                    <span class="text-muted ms-1"><?= esc(mb_substr($mv['descripcion'], 0, 40)) ?></span>
                                                    <span class="fw-medium float-end">$<?= number_format($mv['monto'], 2) ?></span>
                                                </div>
                                                <div class="text-muted small mt-1">
                                                    <?= date('d/m/Y', strtotime($mv['fecha'])) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-muted small mt-1">
                                                    Creado el <?= date('d/m/Y', strtotime($grupo['created_at'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-footer bg-transparent border-0 pt-0">
                                            <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-primary btn-sm w-100">Entrar</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Grupos cerrados / liquidados -->
                <div class="filter-section" data-section="inactivos">
                    <?php if (!empty($inactivos)): ?>
                        <details class="mb-4">
                            <summary class="fw-bold text-muted" style="cursor:pointer;">
                                Cerrados / Liquidados (<?= count($inactivos) ?>)
                            </summary>
                            <div class="row g-3 mt-2">
                                <?php foreach ($inactivos as $grupo): ?>
                                    <?php
                                        $badgeEstado = ['cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
                                        $claseBadge = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                                        $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : '');
                                    ?>
                                    <div class="col-12 col-md-6 col-lg-4" data-estado="<?= $grupo['estado'] ?>">
                                        <div class="card border-0 shadow-sm h-100" style="opacity:0.8;">
                                            <div class="card-body">
                                                <div class="d-flex align-items-start gap-3 mb-2">
                                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:42px;height:42px;background:#e2e8f0;font-weight:700;color:#64748b;font-size:1.1rem;">
                                                        <?= esc(mb_substr($grupo['nombre'], 0, 1)) ?>
                                                    </div>
                                                    <div class="flex-grow-1 min-width-0">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <h6 class="fw-bold mb-0"><?= esc($grupo['nombre']) ?></h6>
                                                            <span class="badge <?= $claseBadge ?> flex-shrink-0"><?= ucfirst($grupo['estado']) ?></span>
                                                        </div>
                                                        <div class="d-flex justify-content-between small mt-1">
                                                            <span class="text-muted">Saldo:</span>
                                                            <span class="fw-semibold <?= $saldoClase ?>">$<?= number_format($grupo['mi_saldo'], 2) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-muted small">
                                                    <?= $grupo['deudas_count'] ?> transferencia(s) pendiente(s)
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent border-0 pt-0">
                                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-secondary btn-sm w-100">Ver</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deudas pendientes -->
            <div id="deudas-section" class="mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:32px;height:32px;background:#fef2f2;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dc2626" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.5h14V4a1 1 0 0 0-1-1H2Zm13 3.5H1v5.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-5.5Zm-9 2a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1H6Z"/></svg>
                    </div>
                    <h5 class="fw-bold mb-0">Deudas pendientes</h5>
                </div>
                <?php if (empty($deudasPendientes)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:56px;height:56px;background:#dcfce7;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#16a34a" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                            </div>
                            <p class="text-muted mb-0">No ten&eacute;s deudas pendientes.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($deudasPendientes as $d): ?>
                        <?php $esDeudor = (int) $d['deudor_id'] === $userId; ?>
                        <a href="<?= base_url('grupos/' . $d['grupo_id'] . '/balance') ?>" class="text-decoration-none">
                        <div class="card border-0 shadow-sm mb-2 feed-card" style="border-left: 3px solid <?= $esDeudor ? '#dc2626' : '#16a34a' ?>;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-medium small">
                                            <?= $esDeudor ? 'Deb&eacute;s a ' . esc($d['acreedor']) : 'Te debe ' . esc($d['deudor']) ?>
                                        </span>
                                        <span class="text-muted small ms-1"><?= esc($d['grupo_nombre']) ?></span>
                                    </div>
                                    <span class="fw-bold small <?= $esDeudor ? 'text-danger' : 'text-success' ?>">$<?= number_format($d['monto'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                        </a>
                    <?php endforeach; ?>
                    <?php if ($hayMasDeudas): ?>
                        <div class="text-center mt-2">
                            <a href="<?= base_url('grupos') ?>" class="text-decoration-none small">Ver m&aacute;s</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Actividad reciente con filtros -->
            <div class="filter-section" data-section="actividad">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Actividad reciente</h5>
                    <button type="button" class="btn btn-primary feed-filter-btn" data-bs-toggle="modal" data-bs-target="#feedFilterModal" aria-label="Filtros">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.708l4 4.646V14l2-.667V7.854l4-4.646V2z"/></svg>
                    </button>
                </div>

                <?php if (!empty($movimientos)): ?>
                    <div id="feedContainer">
                        <?php foreach ($movimientos as $m): ?>
                            <?php
                                $fechaTs = strtotime($m['fecha']);
                                $fechaTag = date('Y-m-d', $fechaTs);
                            ?>
                            <?php if ($m['tipo'] === 'gasto'): ?>
                                <a href="<?= base_url('gastos/' . $m['id']) ?>" class="text-decoration-none feed-item" data-grupo-id="<?= $m['grupo_id'] ?? '' ?>" data-fecha="<?= $fechaTag ?>">
                                <div class="card border-0 shadow-sm mb-2 feed-card" style="border-left: 3px solid #2563eb;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-medium small"><?= esc($m['descripcion']) ?></span>
                                                <span class="text-muted small ms-2"><?= esc($m['grupo_nombre']) ?></span>
                                            </div>
                                            <span class="fw-bold text-primary small">$<?= number_format($m['monto'], 2) ?></span>
                                        </div>
                                        <div class="text-muted small">
                                            <?= date('d/m/Y', $fechaTs) ?> &middot;
                                            <?= esc($m['pagador_nombre']) ?> &middot;
                                            <span class="badge bg-light text-dark"><?= esc($m['categoria_nombre'] ?? 'Otros') ?></span>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url('pagos/' . $m['id']) ?>" class="text-decoration-none feed-item" data-grupo-id="<?= $m['grupo_id'] ?? '' ?>" data-fecha="<?= $fechaTag ?>">
                                <div class="card border-0 shadow-sm mb-2 feed-card" style="border-left: 3px solid #16a34a;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-medium small">Pago</span>
                                                <span class="text-muted small ms-2"><?= esc($m['grupo_nombre']) ?></span>
                                            </div>
                                            <span class="fw-bold text-success small">$<?= number_format($m['monto'], 2) ?></span>
                                        </div>
                                        <div class="text-muted small">
                                            <?= date('d/m/Y', $fechaTs) ?> &middot;
                                            <?= esc($m['pagador_nombre']) ?> pag&oacute; a <?= esc($m['receptor_nombre']) ?>
                                        </div>
                                    </div>
                                </div>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div id="feedEmpty" class="card border-0 shadow-sm d-none">
                        <div class="card-body text-center py-5">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:56px;height:56px;background:#e2e8f0;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#64748b" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/></svg>
                            </div>
                            <p class="text-muted mb-0">No hay movimientos para este filtro.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-4">
                            <p class="text-muted mb-0">No hay actividad reciente.</p>
                            <a href="<?= base_url('grupos') ?>" class="btn btn-primary btn-sm mt-2">Entrar a un grupo</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <script>
            function filtrarGrupos(filtro, btn) {
                document.querySelectorAll('.filter-tab').forEach(function(t) { t.classList.remove('active'); });
                btn.classList.add('active');
                document.querySelectorAll('.filter-section').forEach(function(s) {
                    if (filtro === 'todos') { s.style.display = ''; }
                    else if (filtro === 'activos') { s.style.display = (s.dataset.section === 'activos' || s.dataset.section === 'actividad') ? '' : 'none'; }
                    else if (filtro === 'cerrados') {
                        s.style.display = (s.dataset.section === 'activos' || s.dataset.section === 'actividad') ? 'none' : '';
                        if (s.dataset.section === 'inactivos') { var d = s.querySelector('details'); if (d) d.open = true; }
                    }
                });
            }

            function filtrarFeed() {
                var grupoFiltro = document.getElementById('feedFiltroGrupo').value;
                var periodoFiltro = document.getElementById('feedFiltroPeriodo').value;
                var items = document.querySelectorAll('.feed-item');
                var visible = 0;

                var ahora = new Date();
                var hoyStr = ahora.toISOString().slice(0, 10);

                var inicioSemana = new Date(ahora);
                inicioSemana.setDate(ahora.getDate() - ahora.getDay());
                var inicioSemanaStr = inicioSemana.toISOString().slice(0, 10);

                var inicioMesStr = ahora.getFullYear() + '-' + String(ahora.getMonth() + 1).padStart(2, '0') + '-01';

                items.forEach(function(item) {
                    var gId = item.dataset.grupoId;
                    var f = item.dataset.fecha;
                    var cumpleGrupo = !grupoFiltro || gId === grupoFiltro;
                    var cumpleFecha = true;
                    if (periodoFiltro === 'hoy') { cumpleFecha = f === hoyStr; }
                    else if (periodoFiltro === 'semana') { cumpleFecha = f >= inicioSemanaStr; }
                    else if (periodoFiltro === 'mes') { cumpleFecha = f >= inicioMesStr; }
                    if (cumpleGrupo && cumpleFecha) { item.style.display = ''; visible++; }
                    else { item.style.display = 'none'; }
                });

                document.getElementById('feedEmpty').classList.toggle('d-none', visible > 0);
            }
            </script>

        <?php endif; ?>
    </div>

    <!-- Filtros de actividad - Modal -->
    <div class="modal fade" id="feedFilterModal" tabindex="-1" aria-labelledby="feedFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="feedFilterModalLabel">Filtrar actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Grupo</label>
                        <select id="feedFiltroGrupo" class="form-select" onchange="filtrarFeed()">
                            <option value="">Todos los grupos</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Per&iacute;odo</label>
                        <select id="feedFiltroPeriodo" class="form-select" onchange="filtrarFeed()">
                            <option value="">Todas</option>
                            <option value="hoy">Hoy</option>
                            <option value="semana">Esta semana</option>
                            <option value="mes">Este mes</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FAB extendido - mobile only -->
    <a href="<?= base_url('grupos/nuevo') ?>" class="d-md-none fab fab-extended" aria-label="Nuevo grupo">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
        <span>Nuevo grupo</span>
    </a>

<?= view('partials/_footer') ?>
