<?= view('partials/_head', ['title' => 'Gastito - ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar', ['pageTitle' => $grupo['nombre']]) ?>

    <div class="container mt-3 mt-md-4">

        <?= view('partials/_feedback') ?>

        <?php
            $miBalance = current(array_filter($balance, fn($b) => $b['user_id'] == session()->get('userId')));
            $miSaldo = $miBalance['saldo'] ?? 0;
            $misDeudas = array_values(array_filter($deudas, fn($d) => (int) $d['deudor_id'] === (int) session()->get('userId')));
            $deudaPrincipal = $misDeudas[0] ?? null;
            $miTotalPagado = (float) ($miBalance['total_pagado_gastos'] ?? 0);
            $totalGrupoGauge = max((float) $totalGastado, 0);
            $miPorcentajePagado = $totalGrupoGauge > 0 ? min(100, max(0, ($miTotalPagado / $totalGrupoGauge) * 100)) : 0;
            $balanceCardVariant = \App\Services\UiComponentResolver::variant('grupo_show', 'group_balance_card');
            $gaugeVariant = \App\Services\UiComponentResolver::variant('grupo_show', 'group_gauge');
            $movementCardVariant = \App\Services\UiComponentResolver::variant('grupo_show', 'group_movement_card');
            $esAdmin = ($rol ?? '') === 'admin';
            $estadoGrupo = $grupo['estado'] ?? 'activo';

            $badgeEstado = ['activo' => 'bg-success', 'cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
            $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
        ?>

        <div class="card border-0 shadow-sm mb-4 position-relative" style="background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);">
            <div class="card-body">
                <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="grupo-gear-btn grupo-gear-btn-floating" aria-label="Configurar grupo" title="Configurar grupo">
                    <span aria-hidden="true">&#9881;&#65039;</span>
                </a>
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:56px;height:56px;background:#dbeafe;font-weight:700;color:#2563eb;font-size:1.5rem;">
                        <?= esc(mb_substr($grupo['nombre'], 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <h4 class="fw-bold mb-1"><?= esc($grupo['nombre']) ?></h4>
                        </div>
                        <span class="badge <?= $claseEstado ?>"><?= ucfirst($grupo['estado']) ?></span>
                    </div>
                </div>

                <?= view('components/cards/grupo_balance', [
                    'variant' => $balanceCardVariant,
                    'saldo' => $miSaldo,
                    'href' => base_url('grupos/' . $grupo['id'] . '/balance'),
                ]) ?>

                <?php if ($estadoGrupo === 'cerrado' && $miSaldo < 0 && $deudaPrincipal !== null): ?>
                <div class="d-grid mt-3">
                    <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-warning">
                        Saldar deuda &mdash; deb&eacute;s <?= moneda(abs($miSaldo)) ?> a <?= esc($deudaPrincipal['acreedor']) ?>
                    </a>
                </div>
                <?php elseif ($estadoGrupo === 'cerrado'): ?>
                <div class="d-grid mt-3">
                    <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-secondary">Ver balance</a>
                </div>
                <?php elseif ($estadoGrupo === 'liquidado'): ?>
                <p class="text-muted small mt-2 mb-0">Este grupo est&aacute; finalizado. Solo lectura.</p>
                <?php endif; ?>

                <?php if ($estadoGrupo === 'cerrado' && empty($deudas) && $esAdmin): ?>
                <div class="d-grid mt-2">
                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post" id="liquidar-desde-show-<?= $grupo['id'] ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="estado" value="liquidado">
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">Liquidar grupo</button>
                    </form>
                </div>
                <?php endif; ?>
                <?= view('components/widgets/velocimetro_aporte', [
                    'variant' => $gaugeVariant,
                    'porcentaje' => $miPorcentajePagado,
                    'pagado' => $miTotalPagado,
                    'total' => $totalGrupoGauge,
                ]) ?>

                <?php if ($grupo['descripcion']): ?>
                    <p class="mb-0 text-muted small"><?= esc($grupo['descripcion']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php
            $hasMovimientoFilters = !empty($movimientoFilters['fecha_desde'])
                || !empty($movimientoFilters['fecha_hasta'])
                || !empty($movimientoFilters['categoria_id'])
                || !empty($movimientoFilters['persona_id'])
                || !empty($movimientoFilters['q']);
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Movimientos<?= $hasMovimientoFilters ? ' filtrados' : '' ?></h5>
                <button type="button" class="btn btn-primary feed-filter-btn" data-bs-toggle="modal" data-bs-target="#movimientoFilterModal" aria-label="Filtros">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5z"/></svg>
                </button>
            </div>
            <?php if (empty($movimientos)): ?>
                <div class="card-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:56px;height:56px;background:#e2e8f0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#64748b" viewBox="0 0 16 16"><path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5h-2v12h2V2zm-4-2v2h2V2H9zm-4 4v2h2V7H5zm-4 4v2h2v-2H1z"/></svg>
                    </div>
                    <p class="text-muted mb-0"><?= $hasMovimientoFilters ? 'No hay movimientos para estos filtros.' : 'No hay movimientos en este grupo todav&iacute;a.' ?></p>
                    <?php if ($hasMovimientoFilters): ?>
                        <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-primary btn-sm mt-3">Limpiar filtros</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="card-body report-movement-list">
                    <?php foreach ($movimientos as $m): ?>
                        <?php
                            $movColorKey = $m['tipo'] === 'gasto'
                                ? ($colorMap['movimientos'][(int) $m['id']] ?? \App\Services\UserColor::DEFAULT_KEY)
                                : null;
                        ?>
                        <?php if ($m['tipo'] === 'gasto'): ?>
                            <?= view('components/cards/movimiento', [
                                'variant' => $movementCardVariant,
                                'tipo' => $m['tipo'],
                                'descripcion' => $m['descripcion'],
                                'monto' => $m['monto'],
                                'fecha' => $m['fecha'],
                                'persona' => $m['persona'],
                                'userId' => $m['persona_id'],
                                'avatarFilename' => $m['avatar_filename'] ?? null,
                                'avatarUpdatedAt' => $m['avatar_updated_at'] ?? null,
                                'categoria' => $m['categoria_nombre'],
                                'grupo' => null,
                                'participantes' => null,
                                'colorKey' => $movColorKey,
                                'url' => base_url('gastos/' . $m['id']),
                            ]) ?>
                        <?php else: ?>
                            <?= view('components/cards/movimiento', [
                                'variant' => $movementCardVariant,
                                'tipo' => $m['tipo'],
                                'descripcion' => $m['descripcion'],
                                'monto' => $m['monto'],
                                'fecha' => $m['fecha'],
                                'persona' => $m['persona'],
                                'userId' => $m['persona_id'],
                                'avatarFilename' => $m['avatar_filename'] ?? null,
                                'avatarUpdatedAt' => $m['avatar_updated_at'] ?? null,
                                'categoria' => null,
                                'grupo' => null,
                                'participantes' => null,
                                'colorKey' => $movColorKey,
                                'url' => base_url('pagos/' . $m['id']),
                            ]) ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="modal fade" id="movimientoFilterModal" tabindex="-1" aria-labelledby="movimientoFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">
                <form method="get" action="<?= base_url('grupos/' . $grupo['id']) ?>">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="movimientoFilterModalLabel">Filtrar movimientos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-medium">Desde</label>
                                <input type="date" name="fecha_desde" class="form-control" value="<?= esc($movimientoFilters['fecha_desde'] ?? '') ?>">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-medium">Hasta</label>
                                <input type="date" name="fecha_hasta" class="form-control" value="<?= esc($movimientoFilters['fecha_hasta'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-medium">Categor&iacute;a</label>
                            <select name="categoria_id" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= $categoria['id'] ?>" <?= ($movimientoFilters['categoria_id'] ?? '') == $categoria['id'] ? 'selected' : '' ?>><?= esc($categoria['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-medium">Integrante</label>
                            <select name="persona_id" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($miembros as $miembro): ?>
                                    <option value="<?= $miembro['user_id'] ?>" <?= ($movimientoFilters['persona_id'] ?? '') == $miembro['user_id'] ? 'selected' : '' ?>><?= esc($miembro['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mt-3">
                            <label class="form-label small fw-medium">Palabra clave</label>
                            <input type="search" name="q" class="form-control" value="<?= esc($movimientoFilters['q'] ?? '') ?>" placeholder="Buscar por descripci&oacute;n o persona">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-secondary flex-fill">Limpiar</a>
                        <button type="submit" class="btn btn-primary flex-fill">Aplicar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if ($permisos['puede_crear_gasto']): ?>
    <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="d-md-none fab fab-extended" aria-label="Agregar gastito">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
        <span>Agregar gastito</span>
    </a>
    <?php endif; ?>

<script>
(function () {
    var storageKey = 'gastito:group-colors-changed:<?= (int) $grupo['id'] ?>';

    window.addEventListener('pageshow', function () {
        if (sessionStorage.getItem(storageKey) !== '1') {
            return;
        }

        sessionStorage.removeItem(storageKey);
        window.location.reload();
    });
})();
</script>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
