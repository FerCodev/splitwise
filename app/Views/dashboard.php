<?= view('partials/_head', ['title' => 'SplitWise - Dashboard']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Home']) ?>

    <div class="container mt-3 mt-md-4">

        <?php if (empty($grupos)): ?>

            <div class="empty-state">
                <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                <div class="empty-state-title">No ten&eacute;s grupos a&uacute;n</div>
                <div class="empty-state-text">Cre&aacute; tu primer grupo para empezar a compartir gastos.</div>
                <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">Crear Grupo</a>
            </div>

        <?php else: ?>

            <?php
                $activos = array_filter($grupos, fn($g) => $g['estado'] === 'activo');
                $inactivos = array_filter($grupos, fn($g) => $g['estado'] !== 'activo');
                $userId = (int) session()->get('userId');
                $avatarColors = ['avatar-blue', 'avatar-green', 'avatar-purple', 'avatar-orange', 'avatar-teal', 'avatar-pink', 'avatar-red', 'avatar-indigo'];
                $colorIdx = 0;
            ?>

            <!-- Balance Strip - card style -->
            <div class="dash-card mb-3">
                <div class="dash-card-body">
                    <div class="balance-strip-label">Tu saldo total</div>
                    <div class="balance-strip-amount <?= $globalSaldo >= 0 ? 'text-success' : 'text-danger' ?>">
                        $<?= number_format(abs($globalSaldo), 2) ?>
                        <span style="font-size:14px;font-weight:600;" class="text-muted"><?= $globalSaldo >= 0 ? 'a favor' : 'deb&eacute;s' ?></span>
                    </div>
                    <div class="balance-strip-detail">
                        <div class="balance-strip-detail-item">
                            <span class="status-dot status-dot-danger"></span>
                            <span class="text-muted">Deb&eacute;s:</span>
                            <span class="financial-amount text-danger">$<?= number_format($totalDebe, 2) ?></span>
                        </div>
                        <div class="balance-strip-detail-item">
                            <span class="status-dot status-dot-active"></span>
                            <span class="text-muted">Te deben:</span>
                            <span class="financial-amount text-success">$<?= number_format($totalLeDeben, 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick stats row -->
            <div class="dash-card mb-3">
                <div class="d-flex">
                    <a href="<?= base_url('grupos') ?>" class="text-decoration-none" style="flex:1;padding:12px;text-align:center;">
                        <div class="financial-amount" style="font-size:20px;color:var(--primary);"><?= $cantidadActivos ?></div>
                        <div style="font-size:12px;color:var(--muted);font-weight:600;">Grupos activos</div>
                    </a>
                    <div style="width:1px;background:var(--border);"></div>
                    <a href="<?= base_url('gastos') ?>" class="text-decoration-none" style="flex:1;padding:12px;text-align:center;">
                        <div class="financial-amount" style="font-size:20px;color:var(--ink);"><?= count($grupos) ?></div>
                        <div style="font-size:12px;color:var(--muted);font-weight:600;">Total grupos</div>
                    </a>
                </div>
            </div>

            <!-- Filtros del feed -->
            <div class="d-flex align-items-center gap-2 my-3 flex-wrap">
                <div class="filter-tabs flex-grow-1">
                    <button class="filter-tab active" data-filter="todos" onclick="filtrarGrupos('todos', this)">Todos</button>
                    <button class="filter-tab" data-filter="activos" onclick="filtrarGrupos('activos', this)">Activos</button>
                    <button class="filter-tab" data-filter="cerrados" onclick="filtrarGrupos('cerrados', this)">Cerrados</button>
                </div>
                <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary btn-sm d-none d-md-inline-flex">+ Nuevo grupo</a>
            </div>

            <div id="grupos-list">
                <!-- Grupos activos -->
                <div class="filter-section" data-section="activos">
                    <?php if (empty($activos)): ?>
                        <div class="dash-card mb-3">
                            <div class="empty-state" style="padding:24px;">
                                <div class="empty-state-text">No ten&eacute;s grupos activos.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="dash-card mb-3">
                            <?php foreach ($activos as $grupo): ?>
                                <?php
                                    $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : 'text-muted');
                                    $mv = $grupo['ultimo_movimiento'];
                                    $avaColor = $avatarColors[$colorIdx % count($avatarColors)];
                                    $colorIdx++;
                                ?>
                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="dash-list-item text-decoration-none" data-estado="activo">
                                    <div class="avatar <?= $avaColor ?>"><?= esc(mb_strtoupper(mb_substr($grupo['nombre'], 0, 1))) ?></div>
                                    <div class="dash-list-item-info">
                                        <div class="dash-list-item-title"><?= esc($grupo['nombre']) ?></div>
                                        <div class="dash-list-item-subtitle">
                                            <?php if ($mv): ?>
                                                <span class="badge <?= $mv['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>" style="font-size:10px;padding:2px 6px;"><?= $mv['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                                <?= esc(mb_substr($mv['descripcion'], 0, 30)) ?> &middot; <?= date('d/m', strtotime($mv['fecha'])) ?>
                                            <?php else: ?>
                                                Creado <?= date('d/m/Y', strtotime($grupo['created_at'])) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="dash-list-item-amount <?= $saldoClase ?>">
                                        $<?= number_format(abs($grupo['mi_saldo']), 2) ?>
                                        <div style="font-size:10px;font-weight:600;"><?= $grupo['mi_saldo'] > 0 ? 'a favor' : ($grupo['mi_saldo'] < 0 ? 'deb&eacute;s' : ''); ?></div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Grupos cerrados / liquidados -->
                <div class="filter-section" data-section="inactivos">
                    <?php if (!empty($inactivos)): ?>
                        <div class="mb-3">
                            <details>
                                <summary class="section-header-title" style="cursor:pointer;padding:12px 0;">
                                    Cerrados / Liquidados (<?= count($inactivos) ?>)
                                </summary>
                                <div class="dash-card">
                                    <?php foreach ($inactivos as $grupo): ?>
                                        <?php
                                            $badgeEstado = ['cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
                                            $claseBadge = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                                            $saldoClase = $grupo['mi_saldo'] > 0 ? 'text-success' : ($grupo['mi_saldo'] < 0 ? 'text-danger' : 'text-muted');
                                            $avaColor = $avatarColors[$colorIdx % count($avatarColors)];
                                            $colorIdx++;
                                        ?>
                                        <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="dash-list-item text-decoration-none" style="opacity:0.75;" data-estado="<?= $grupo['estado'] ?>">
                                            <div class="avatar" style="background:var(--surface-alt);color:var(--muted);"><?= esc(mb_strtoupper(mb_substr($grupo['nombre'], 0, 1))) ?></div>
                                            <div class="dash-list-item-info">
                                                <div class="dash-list-item-title"><?= esc($grupo['nombre']) ?></div>
                                                <div class="dash-list-item-subtitle">
                                                    <span class="badge <?= $claseBadge ?>" style="font-size:10px;padding:2px 6px;"><?= ucfirst($grupo['estado']) ?></span>
                                                    <?= $grupo['deudas_count'] ?> transferencia(s) pendiente(s)
                                                </div>
                                            </div>
                                            <div class="dash-list-item-amount <?= $saldoClase ?>">
                                                $<?= number_format(abs($grupo['mi_saldo']), 2) ?>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </details>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Deudas pendientes -->
            <div id="deudas-section" class="mb-3">
                <div class="section-header">
                    <div class="section-header-title">Deudas pendientes</div>
                </div>
                <?php if (empty($deudasPendientes)): ?>
                    <div class="dash-card">
                        <div class="dash-list-item" style="justify-content:center;flex-direction:column;text-align:center;padding:24px;">
                            <span class="status-dot status-dot-active" style="width:10px;height:10px;margin-bottom:8px;"></span>
                            <span style="font-size:14px;color:var(--muted);">No ten&eacute;s deudas pendientes</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="dash-card">
                        <?php foreach ($deudasPendientes as $d): ?>
                            <?php $esDeudor = (int) $d['deudor_id'] === $userId; ?>
                            <a href="<?= base_url('grupos/' . $d['grupo_id'] . '/balance') ?>" class="dash-list-item text-decoration-none">
                                <div class="avatar avatar-sm" style="background:<?= $esDeudor ? 'var(--danger-light)' : 'var(--accent-light)' ?>;color:<?= $esDeudor ? 'var(--danger)' : 'var(--accent)' ?>;">
                                    <?= $esDeudor ? '↑' : '↓' ?>
                                </div>
                                <div class="dash-list-item-info">
                                    <div class="dash-list-item-title" style="font-size:14px;">
                                        <?= $esDeudor ? 'Deb&eacute;s a ' . esc($d['acreedor']) : 'Te debe ' . esc($d['deudor']) ?>
                                    </div>
                                    <div class="dash-list-item-subtitle"><?= esc($d['grupo_nombre']) ?></div>
                                </div>
                                <div class="dash-list-item-amount <?= $esDeudor ? 'text-danger' : 'text-success' ?>">
                                    $<?= number_format($d['monto'], 2) ?>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($hayMasDeudas): ?>
                        <div class="text-center mt-2">
                            <a href="<?= base_url('grupos') ?>" class="text-decoration-none" style="font-size:13px;font-weight:600;">Ver m&aacute;s</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Actividad reciente -->
            <div class="filter-section" data-section="actividad">
                <div class="section-header">
                    <div class="section-header-title">Actividad reciente</div>
                    <button type="button" class="btn btn-primary feed-filter-btn btn-sm" data-bs-toggle="modal" data-bs-target="#feedFilterModal" aria-label="Filtros">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5zm1 .5v1.708l4 4.646V14l2-.667V7.854l4-4.646V2z"/></svg>
                    </button>
                </div>

                <?php if (!empty($movimientos)): ?>
                    <div class="dash-card" id="feedContainer">
                        <?php foreach ($movimientos as $m): ?>
                            <?php
                                $fechaTs = strtotime($m['fecha']);
                                $fechaTag = date('Y-m-d', $fechaTs);
                            ?>
                            <?php if ($m['tipo'] === 'gasto'): ?>
                                <a href="<?= base_url('gastos/' . $m['id']) ?>" class="dash-list-item dash-list-item-bordered text-decoration-none feed-item" style="border-left:3px solid var(--primary);" data-grupo-id="<?= $m['grupo_id'] ?? '' ?>" data-fecha="<?= $fechaTag ?>">
                                    <div class="dash-list-item-info">
                                        <div class="dash-list-item-title" style="font-size:14px;"><?= esc($m['descripcion']) ?></div>
                                        <div class="dash-list-item-subtitle">
                                            <?= date('d/m/Y', $fechaTs) ?> &middot; <?= esc($m['pagador_nombre']) ?> &middot; <?= esc($m['grupo_nombre']) ?>
                                        </div>
                                    </div>
                                    <div class="dash-list-item-amount text-primary">
                                        -$<?= number_format($m['monto'], 2) ?>
                                    </div>
                                </a>
                            <?php else: ?>
                                <a href="<?= base_url('pagos/' . $m['id']) ?>" class="dash-list-item dash-list-item-bordered text-decoration-none feed-item" style="border-left:3px solid var(--accent);" data-grupo-id="<?= $m['grupo_id'] ?? '' ?>" data-fecha="<?= $fechaTag ?>">
                                    <div class="dash-list-item-info">
                                        <div class="dash-list-item-title" style="font-size:14px;">Pago</div>
                                        <div class="dash-list-item-subtitle">
                                            <?= date('d/m/Y', $fechaTs) ?> &middot; <?= esc($m['pagador_nombre']) ?> &rarr; <?= esc($m['receptor_nombre']) ?>
                                        </div>
                                    </div>
                                    <div class="dash-list-item-amount text-success">
                                        $<?= number_format($m['monto'], 2) ?>
                                    </div>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div id="feedEmpty" class="dash-card d-none">
                        <div class="dash-list-item" style="justify-content:center;flex-direction:column;text-align:center;padding:24px;">
                            <span style="font-size:14px;color:var(--muted);">No hay movimientos para este filtro.</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="dash-card">
                        <div class="empty-state" style="padding:24px;">
                            <div class="empty-state-text">No hay actividad reciente.</div>
                            <a href="<?= base_url('grupos') ?>" class="btn btn-primary btn-sm">Entrar a un grupo</a>
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
                    <h5 class="modal-title" id="feedFilterModalLabel">Filtrar actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Grupo</label>
                        <select id="feedFiltroGrupo" class="form-select" onchange="filtrarFeed()">
                            <option value="">Todos los grupos</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Per&iacute;odo</label>
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

    <!-- FAB mobile -->
    <a href="<?= base_url('grupos/nuevo') ?>" class="d-md-none fab" aria-label="Nuevo grupo">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
    </a>

<?= view('partials/_footer') ?>
