<?= view('partials/_head', ['title' => 'SplitWise - Dashboard']) ?>
<?= view('partials/_navbar') ?>

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
            ?>

            <!-- Resumen colapsable -->
            <div class="mb-4">
                <div class="collapse d-md-block" id="resumenCollapse">
                    <div class="row g-2 mt-2">
                        <div class="col-3 col-md-3 col-lg-2">
                            <div class="card border-0 shadow-sm text-center py-2 h-100">
                                <div class="small text-muted lh-1">Activos</div>
                                <div class="fw-bold fs-5 text-success"><?= $cantidadActivos ?></div>
                            </div>
                        </div>
                        <div class="col-3 col-md-3 col-lg-2">
                            <div class="card border-0 shadow-sm text-center py-2 h-100">
                                <div class="small text-muted lh-1">Saldo</div>
                                <div class="fw-bold fs-5 <?= $globalSaldo >= 0 ? 'text-success' : 'text-danger' ?>">$<?= number_format(abs($globalSaldo), 0) ?></div>
                            </div>
                        </div>
                        <div class="col-3 col-md-3 col-lg-2">
                            <div class="card border-0 shadow-sm text-center py-2 h-100">
                                <div class="small text-muted lh-1">A favor</div>
                                <div class="fw-bold fs-5 text-success"><?= $gruposAFavor ?></div>
                            </div>
                        </div>
                        <div class="col-3 col-md-3 col-lg-2">
                            <div class="card border-0 shadow-sm text-center py-2 h-100">
                                <div class="small text-muted lh-1">Deb&eacute;s</div>
                                <div class="fw-bold fs-5 text-danger"><?= $gruposDebe ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold small text-muted">Grupos</span>
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
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="fw-bold mb-0"><?= esc($grupo['nombre']) ?></h6>
                                                <span class="badge bg-success">Activo</span>
                                            </div>
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span class="text-muted">Saldo:</span>
                                                <span class="fw-medium <?= $saldoClase ?>">$<?= number_format($grupo['mi_saldo'], 2) ?></span>
                                            </div>
                                            <?php if ($mv): ?>
                                                <div class="small mt-1">
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
                                            <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-primary btn-sm w-100">Entrar</a>
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
                                        $badgeEstado = [
                                            'cerrado' => 'bg-warning text-dark',
                                            'liquidado' => 'bg-secondary',
                                        ];
                                        $claseBadge = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                                        $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : '');
                                    ?>
                                    <div class="col-12 col-md-6 col-lg-4" data-estado="<?= $grupo['estado'] ?>">
                                        <div class="card border-0 shadow-sm h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-bold mb-0"><?= esc($grupo['nombre']) ?></h6>
                                                    <span class="badge <?= $claseBadge ?>"><?= ucfirst($grupo['estado']) ?></span>
                                                </div>
                                                <div class="d-flex justify-content-between small mb-2">
                                                    <span class="text-muted">Saldo:</span>
                                                    <span class="fw-medium <?= $saldoClase ?>">$<?= number_format($grupo['mi_saldo'], 2) ?></span>
                                                </div>
                                                <div class="text-muted small">
                                                    <?= $grupo['deudas_count'] ?> transferencia(s) pendiente(s)
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent border-0 pt-0">
                                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-secondary btn-sm w-100">Ver</a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </details>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actividad reciente -->
            <div class="filter-section" data-section="actividad">
                <?php if (!empty($movimientos)): ?>
                    <h5 class="fw-bold mb-3 mt-2">Actividad reciente</h5>
                    <?php foreach ($movimientos as $m): ?>
                        <?php if ($m['tipo'] === 'gasto'): ?>
                            <div class="card border-0 shadow-sm mb-2">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-medium small"><?= esc($m['descripcion']) ?></span>
                                            <span class="text-muted small ms-2"><?= esc($m['grupo_nombre']) ?></span>
                                        </div>
                                        <span class="fw-bold text-primary small">$<?= number_format($m['monto'], 2) ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot;
                                        <?= esc($m['pagador_nombre']) ?> &middot;
                                        <span class="badge bg-light text-dark"><?= esc($m['categoria_nombre'] ?? 'Otros') ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="card border-0 shadow-sm mb-2">
                                <div class="card-body py-2 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-medium small">Pago</span>
                                            <span class="text-muted small ms-2"><?= esc($m['grupo_nombre']) ?></span>
                                        </div>
                                        <span class="fw-bold text-success small">$<?= number_format($m['monto'], 2) ?></span>
                                    </div>
                                    <div class="text-muted small">
                                        <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot;
                                        <?= esc($m['pagador_nombre']) ?> pag&oacute; a <?= esc($m['receptor_nombre']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <script>
            function filtrarGrupos(filtro, btn) {
                document.querySelectorAll('.filter-tab').forEach(function(t) { t.classList.remove('active'); });
                btn.classList.add('active');
                var sections = document.querySelectorAll('.filter-section');
                sections.forEach(function(s) {
                    if (filtro === 'todos') {
                        s.style.display = '';
                    } else if (filtro === 'activos') {
                        s.style.display = (s.dataset.section === 'activos' || s.dataset.section === 'actividad') ? '' : 'none';
                    } else if (filtro === 'cerrados') {
                        s.style.display = (s.dataset.section === 'activos' || s.dataset.section === 'actividad') ? 'none' : '';
                        if (s.dataset.section === 'inactivos') {
                            var det = s.querySelector('details');
                            if (det) det.open = true;
                        }
                    }
                });
            }
            </script>

        <?php endif; ?>
    </div>

    <!-- FAB extendido - mobile only -->
    <a href="<?= base_url('grupos/nuevo') ?>" class="d-md-none fab fab-extended" aria-label="Nuevo grupo">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
        <span>Nuevo grupo</span>
    </a>

<?= view('partials/_footer') ?>
