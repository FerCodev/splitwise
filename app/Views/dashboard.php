<?= view('partials/_head', ['title' => 'SplitWise - Dashboard']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Home']) ?>

    <div class="container mt-3 mt-md-4">

        <!-- ===== MOBILE (visible < 992px) ===== -->
        <div class="d-lg-none">

            <?php
                $activos = array_filter($grupos, fn($g) => $g['estado'] === 'activo');
                $inactivos = array_filter($grupos, fn($g) => $g['estado'] !== 'activo');
                $userId = (int) session()->get('userId');
                $debtCardVariant = \App\Services\UiComponentResolver::variant('home', 'debt_card');
            ?>

            <!-- Filtros de grupos -->
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <span class="fw-bold small text-muted d-none d-md-inline">Grupos</span>
                <div class="filter-tabs flex-grow-1">
                    <button class="filter-tab" data-filter="todos" onclick="filtrarGrupos('todos', this)">Todos</button>
                    <button class="filter-tab active" data-filter="activos" onclick="filtrarGrupos('activos', this)">Activos</button>
                    <button class="filter-tab" data-filter="cerrados" onclick="filtrarGrupos('cerrados', this)">Cerrados</button>
                </div>
            </div>

            <div id="grupos-list">
                <!-- Grupos activos -->
                <div class="filter-section" data-section="activos">
                    <?php if (empty($activos)): ?>
                        <div class="home-soft-alert mb-3">
                            No ten&eacute;s grupos activos.
                        </div>
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
                                                        <span class="fw-semibold <?= $saldoClase ?>"><?= moneda($grupo['mi_saldo']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if ($mv): ?>
                                                <div class="small mt-2">
                                                    <span class="badge <?= $mv['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>"><?= $mv['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                                    <span class="text-muted ms-1"><?= esc(mb_substr($mv['descripcion'], 0, 40)) ?></span>
                                                    <span class="fw-medium float-end"><?= moneda($mv['monto']) ?></span>
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
                                            <div class="d-flex gap-2">
                                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-primary btn-sm flex-fill">Entrar</a>
                                                <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary btn-sm flex-fill">+ Gasto</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Grupos cerrados / liquidados -->
                <div class="filter-section" data-section="inactivos" style="display:none;">
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
                                                            <span class="fw-semibold <?= $saldoClase ?>"><?= moneda($grupo['mi_saldo']) ?></span>
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
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">Deudas pendientes</h5>
                    </div>
                    <?php if (empty($deudasPendientes)): ?>
                        <div class="card-body text-center py-5 home-debts-empty-card">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:56px;height:56px;background:#dcfce7;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#16a34a" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                            </div>
                            <p class="text-muted mb-0">No ten&eacute;s deudas pendientes.</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body report-movement-list">
                            <?php foreach ($deudasPendientes as $d): ?>
                                <?php $esDeudor = (int) $d['deudor_id'] === $userId; ?>
                                <a href="<?= base_url('grupos/' . $d['grupo_id'] . '/balance') ?>" class="report-movement-link">
                                    <?= view('components/cards/deuda', [
                                        'variant' => $debtCardVariant,
                                        'wrap' => false,
                                        'modo' => $esDeudor ? 'debes' : 'te_deben',
                                        'persona' => $esDeudor ? $d['acreedor'] : $d['deudor'],
                                        'grupo' => $d['grupo_nombre'],
                                        'monto' => $d['monto'],
                                    ]) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($hayMasDeudas): ?>
                            <div class="card-footer bg-white text-center">
                                <a href="<?= base_url('grupos') ?>" class="text-decoration-none small">Ver m&aacute;s</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <script>
            function filtrarGrupos(filtro, btn) {
                document.querySelectorAll('.filter-tab').forEach(function(t) { t.classList.remove('active'); });
                btn.classList.add('active');
                document.querySelectorAll('.filter-section').forEach(function(s) {
                    if (filtro === 'todos') { s.style.display = ''; }
                    else if (filtro === 'activos') { s.style.display = s.dataset.section === 'activos' ? '' : 'none'; }
                    else if (filtro === 'cerrados') {
                        s.style.display = s.dataset.section === 'activos' ? 'none' : '';
                        if (s.dataset.section === 'inactivos') { var d = s.querySelector('details'); if (d) d.open = true; }
                    }
                });
            }
            </script>

        </div><!-- /d-lg-none mobile wrapper -->

        <!-- ===== DESKTOP (visible >= 992px) ===== -->
        <div class="d-none d-lg-block">
            <?php
                $avatarColors = ['avatar-blue', 'avatar-green', 'avatar-purple', 'avatar-orange', 'avatar-teal', 'avatar-pink', 'avatar-red', 'avatar-indigo'];
                $colorIdx = 0;
            ?>

            <!-- Balance Strip -->
            <div class="dash-card mb-3">
                <div class="dash-card-body">
                    <div class="balance-strip-label">Tu saldo total</div>
                    <div class="balance-strip-amount <?= $globalSaldo >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= moneda(abs($globalSaldo)) ?>
                        <span class="text-muted" style="font-size:14px;"><?= $globalSaldo >= 0 ? 'a favor' : 'deb&eacute;s' ?></span>
                    </div>
                    <div class="balance-strip-detail">
                        <div class="balance-strip-detail-item">
                            <span class="status-dot status-dot-danger"></span>
                            <span class="text-muted">Deb&eacute;s:</span>
                            <span class="financial-amount text-danger"><?= moneda($totalDebe) ?></span>
                        </div>
                        <div class="balance-strip-detail-item">
                            <span class="status-dot status-dot-active"></span>
                            <span class="text-muted">Te deben:</span>
                            <span class="financial-amount text-success"><?= moneda($totalLeDeben) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="dash-card mb-3">
                <div class="d-flex">
                    <a href="<?= base_url('grupos') ?>" class="text-decoration-none" style="flex:1;padding:12px 16px;text-align:center;">
                        <div class="financial-amount" style="font-size:20px;color:var(--primary);"><?= $cantidadActivos ?></div>
                        <div style="font-size:12px;color:var(--muted);font-weight:600;">Grupos activos</div>
                    </a>
                    <div style="width:1px;background:var(--border);"></div>
                    <span style="flex:1;padding:12px 16px;text-align:center;">
                        <div class="financial-amount" style="font-size:20px;"><?= count($grupos) ?></div>
                        <div style="font-size:12px;color:var(--muted);font-weight:600;">Total grupos</div>
                    </span>
                </div>
            </div>

            <?php
                $activos = array_filter($grupos, fn($g) => $g['estado'] === 'activo');
                $inactivos = array_filter($grupos, fn($g) => $g['estado'] !== 'activo');
                $userId = (int) session()->get('userId');
            ?>

            <!-- Grupos activos -->
            <?php if (!empty($activos)): ?>
                <div class="section-header">
                    <div class="section-header-title">Grupos activos</div>
                    <a href="<?= base_url('grupos/nuevo') ?>" class="section-header-action btn btn-primary btn-sm">+ Nuevo</a>
                </div>
                <div class="dash-card mb-3">
                    <?php foreach ($activos as $grupo): ?>
                        <?php
                            $avaColor = $avatarColors[$colorIdx % 8];
                            $colorIdx++;
                            $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : 'text-muted');
                            $mv = $grupo['ultimo_movimiento'];
                        ?>
                        <div class="dash-list-item">
                            <div class="avatar <?= $avaColor ?>"><?= esc(mb_strtoupper(mb_substr($grupo['nombre'], 0, 1))) ?></div>
                            <div class="dash-list-item-info">
                                <div class="dash-list-item-title"><?= esc($grupo['nombre']) ?></div>
                                <?php if ($mv): ?>
                                    <div class="dash-list-item-subtitle">
                                        <span class="badge <?= $mv['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>"><?= $mv['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                        <?= esc(mb_substr($mv['descripcion'], 0, 30)) ?> &middot; <?= date('d/m', strtotime($mv['fecha'])) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="dash-list-item-subtitle">Creado el <?= date('d/m/Y', strtotime($grupo['created_at'])) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="dash-list-item-amount <?= $saldoClase ?>">
                                <?= moneda(abs($grupo['mi_saldo'])) ?>
                            </div>
                            <div class="dash-list-item-actions">
                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-primary btn-sm">Abrir</a>
                                <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary btn-sm">+ Gasto</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="dash-card mb-3">
                    <div class="dash-list-item" style="justify-content:flex-start;color:var(--muted);">
                        No ten&eacute;s grupos activos.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Grupos inactivos -->
            <?php if (!empty($inactivos)): ?>
                <details class="mb-3">
                    <summary class="section-header-title" style="cursor:pointer;display:inline-block;padding:4px 0;">
                        Cerrados / Liquidados (<?= count($inactivos) ?>)
                    </summary>
                    <div class="dash-card mt-2">
                        <?php foreach ($inactivos as $grupo): ?>
                            <?php
                                $avaColor = $avatarColors[$colorIdx % 8];
                                $colorIdx++;
                                $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : 'text-muted');
                                $badgeEstado = ['cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
                                $claseBadge = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                            ?>
                            <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="dash-list-item text-decoration-none" style="opacity:0.75;">
                                <div class="avatar <?= $avaColor ?>" style="opacity:0.6;"><?= esc(mb_strtoupper(mb_substr($grupo['nombre'], 0, 1))) ?></div>
                                <div class="dash-list-item-info">
                                    <div class="dash-list-item-title"><?= esc($grupo['nombre']) ?></div>
                                    <div class="dash-list-item-subtitle">
                                        <span class="badge <?= $claseBadge ?>"><?= ucfirst($grupo['estado']) ?></span>
                                        <?= $grupo['deudas_count'] ?> transf. pend.
                                    </div>
                                </div>
                                <div class="dash-list-item-amount <?= $saldoClase ?>">
                                    <?= moneda(abs($grupo['mi_saldo'])) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endif; ?>

            <!-- Deudas pendientes -->
            <div class="mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">Deudas pendientes</h5>
                    </div>
                    <?php if (empty($deudasPendientes)): ?>
                        <div class="card-body text-center py-4 home-debts-empty-card">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:56px;height:56px;background:#dcfce7;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#16a34a" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/></svg>
                            </div>
                            <p class="text-muted mb-0">No ten&eacute;s deudas pendientes.</p>
                        </div>
                    <?php else: ?>
                        <div class="card-body report-movement-list">
                            <?php foreach ($deudasPendientes as $d): ?>
                                <?php $esDeudor = (int)$d['deudor_id'] === $userId; ?>
                                <a href="<?= base_url('grupos/' . $d['grupo_id'] . '/balance') ?>" class="report-movement-link">
                                    <?= view('components/cards/deuda', [
                                        'variant' => $debtCardVariant,
                                        'wrap' => false,
                                        'modo' => $esDeudor ? 'debes' : 'te_deben',
                                        'persona' => $esDeudor ? $d['acreedor'] : $d['deudor'],
                                        'grupo' => $d['grupo_nombre'],
                                        'monto' => $d['monto'],
                                    ]) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($hayMasDeudas): ?>
                            <div class="card-footer bg-white text-end">
                                <a href="<?= base_url('grupos') ?>" class="section-header-action">Ver m&aacute;s</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div><!-- /d-none d-lg-block desktop wrapper -->

    </div><!-- /container -->

<?= view('partials/_footer') ?>
