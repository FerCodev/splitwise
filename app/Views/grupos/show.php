<?= view('partials/_head', ['title' => 'SplitWise - ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar', ['pageTitle' => $grupo['nombre']]) ?>

    <div class="container mt-3 mt-md-4">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php
            $miBalance = current(array_filter($balance, fn($b) => $b['user_id'] == session()->get('userId')));
            $miSaldo = $miBalance['saldo'] ?? 0;
            $badgeEstado = ['activo' => 'bg-success', 'cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
            $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
        ?>

        <!-- Group header -->
        <div class="balance-strip">
            <div class="d-flex align-items-center gap-2 mb-2">
                <h3 class="mb-0"><?= esc($grupo['nombre']) ?></h3>
                <span class="badge <?= $claseEstado ?>"><?= ucfirst($grupo['estado']) ?></span>
                <span class="badge bg-<?= $rol === 'admin' ? 'warning' : 'secondary' ?>"><?= $rol === 'admin' ? 'Admin' : 'Miembro' ?></span>
            </div>
            <div class="balance-strip-label">Tu balance en este grupo</div>
            <div class="balance-strip-amount <?= $miSaldo >= 0 ? 'text-success' : 'text-danger' ?>">
                $<?= number_format(abs($miSaldo), 2) ?>
                <span style="font-size:14px;font-weight:600;" class="text-muted"><?= $miSaldo >= 0 ? 'a favor' : 'debe' ?></span>
            </div>
            <?php if ($grupo['descripcion']): ?>
                <p class="text-muted small mt-2 mb-0"><?= esc($grupo['descripcion']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <?php if ($permisos['puede_editar_grupo'] || $permisos['puede_agregar_miembro'] || $permisos['puede_cambiar_estado']): ?>
            <div class="d-flex gap-2 py-3">
                <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-info flex-fill">Ver balance</a>
                <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-primary flex-fill">Configurar</a>
            </div>
        <?php endif; ?>

        <!-- Movements section -->
        <div class="section-header">
            <div class="section-header-title">Movimientos</div>
            <?php if ($permisos['puede_crear_gasto']): ?>
                <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary btn-sm">+ Gasto</a>
            <?php endif; ?>
        </div>

        <?php if (empty($gastos) && empty($pagos)): ?>
            <div class="card">
                <div class="empty-state" style="padding:24px;">
                    <div class="empty-state-text">No hay movimientos en este grupo todav&iacute;a.</div>
                </div>
            </div>
        <?php else: ?>
            <?php
                $movimientos = [];
                foreach ($gastos as $g) {
                    $movimientos[] = [
                        'tipo' => 'gasto',
                        'fecha' => $g['fecha'],
                        'descripcion' => $g['descripcion'],
                        'monto' => $g['monto'],
                        'persona' => $g['pagador_nombre'],
                        'id' => $g['id'],
                        'categoria_nombre' => $g['categoria_nombre'] ?? null,
                    ];
                }
                foreach ($pagos as $p) {
                    $movimientos[] = [
                        'tipo' => 'pago',
                        'fecha' => $p['fecha'],
                        'descripcion' => $p['descripcion'] ?: 'Pago',
                        'monto' => $p['monto'],
                        'persona' => $p['pagador_nombre'] . ' pag&oacute; a ' . $p['receptor_nombre'],
                        'id' => $p['id'],
                        'categoria_nombre' => null,
                    ];
                }
                usort($movimientos, fn($a, $b) => strcmp($b['fecha'] . 'z', $a['fecha'] . 'z'));
                $movimientos = array_slice($movimientos, 0, 10);
            ?>
            <div class="card">
                <?php foreach ($movimientos as $m): ?>
                    <?php if ($m['tipo'] === 'gasto'): ?>
                    <a href="<?= base_url('gastos/' . $m['id']) ?>" class="financial-list-item financial-list-item-clickable text-decoration-none" style="color:inherit;border-left:3px solid var(--primary);">
                    <?php else: ?>
                    <a href="<?= base_url('pagos/' . $m['id']) ?>" class="financial-list-item financial-list-item-clickable text-decoration-none" style="color:inherit;border-left:3px solid var(--accent);">
                    <?php endif; ?>
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title" style="font-size:14px;">
                                <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>" style="font-size:10px;padding:2px 6px;margin-right:4px;"><?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                <?= esc($m['descripcion']) ?>
                            </div>
                            <div class="financial-list-item-subtitle">
                                <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot; <?= esc($m['persona']) ?>
                                <?php if ($m['categoria_nombre']): ?>
                                    &middot; <?= esc($m['categoria_nombre']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="financial-list-item-amount <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?>">
                            $<?= number_format($m['monto'], 2) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (count($gastos) + count($pagos) > 10): ?>
                <div class="text-center mt-2">
                    <a href="<?= base_url('gastos?grupo_id=' . $grupo['id']) ?>" class="text-decoration-none" style="font-size:13px;font-weight:600;">Ver todos los movimientos &rarr;</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <?php if ($permisos['puede_crear_gasto']): ?>
    <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="d-md-none fab fab-extended" aria-label="Agregar gasto">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
        <span>Agregar gasto</span>
    </a>
    <?php endif; ?>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
