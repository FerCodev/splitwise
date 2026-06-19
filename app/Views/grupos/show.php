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

        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:56px;height:56px;background:#dbeafe;font-weight:700;color:#2563eb;font-size:1.5rem;">
                        <?= esc(mb_substr($grupo['nombre'], 0, 1)) ?>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="fw-bold mb-0"><?= esc($grupo['nombre']) ?></h4>
                            <span class="badge <?= $claseEstado ?>"><?= ucfirst($grupo['estado']) ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-<?= $rol === 'admin' ? 'warning' : 'secondary' ?>"><?= $rol === 'admin' ? 'Admin' : 'Miembro' ?></span>
                        </div>
                    </div>
                </div>
                <div class="card border-0 mb-3" style="background: <?= $miSaldo >= 0 ? '#f0fdf4' : '#fef2f2' ?>;">
                    <div class="card-body py-3 text-center">
                        <div class="small text-muted mb-1">Tu balance</div>
                        <div class="fw-bold fs-4 <?= $miSaldo >= 0 ? 'text-success' : 'text-danger' ?>">
                            $<?= number_format(abs($miSaldo), 2) ?>
                            <small class="fw-normal fs-6"><?= $miSaldo >= 0 ? 'a favor' : 'debe' ?></small>
                        </div>
                    </div>
                </div>
                <?php if ($permisos['puede_editar_grupo'] || $permisos['puede_agregar_miembro'] || $permisos['puede_cambiar_estado']): ?>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-info w-100 py-2">Ver balance</a>
                        </div>
                        <div class="col-6">
                            <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-primary w-100 py-2">
                                <span class="d-none d-sm-inline">Configurar grupo</span>
                                <span class="d-inline d-sm-none">Configurar</span>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($grupo['descripcion']): ?>
                    <p class="mt-2 mb-0 text-muted small"><?= esc($grupo['descripcion']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:28px;height:28px;background:#dbeafe;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="#2563eb" viewBox="0 0 16 16"><path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5h-2v12h2V2zm-4-2v2h2V2H9zm-4 4v2h2V7H5zm-4 4v2h2v-2H1z"/></svg>
            </div>
            <h5 class="fw-bold mb-0">Movimientos</h5>
        </div>

        <?php if (empty($gastos) && empty($pagos)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:56px;height:56px;background:#e2e8f0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#64748b" viewBox="0 0 16 16"><path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5h-2v12h2V2zm-4-2v2h2V2H9zm-4 4v2h2V7H5zm-4 4v2h2v-2H1z"/></svg>
                    </div>
                    <p class="text-muted mb-0">No hay movimientos en este grupo todav&iacute;a.</p>
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
                        'persona' => $p['pagador_nombre'] . ' pagó a ' . $p['receptor_nombre'],
                        'id' => $p['id'],
                        'categoria_nombre' => null,
                    ];
                }
                usort($movimientos, fn($a, $b) => strcmp($b['fecha'] . 'z', $a['fecha'] . 'z'));
                $movimientos = array_slice($movimientos, 0, 10);
            ?>
            <?php foreach ($movimientos as $m): ?>
                <?php if ($m['tipo'] === 'gasto'): ?>
                <a href="<?= base_url('gastos/' . $m['id']) ?>" class="text-decoration-none">
                <?php else: ?>
                <a href="<?= base_url('pagos/' . $m['id']) ?>" class="text-decoration-none">
                <?php endif; ?>
                    <div class="card border-0 shadow-sm mb-2 grupo-movimiento-card" style="border-left: 3px solid <?= $m['tipo'] === 'gasto' ? '#2563eb' : '#16a34a' ?>;">
                        <div class="card-body py-3 px-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?> me-1"><?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                    <span class="fw-medium small"><?= esc($m['descripcion']) ?></span>
                                    <?php if ($m['categoria_nombre']): ?>
                                        <span class="badge bg-light text-dark ms-1"><?= esc($m['categoria_nombre']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="fw-bold small <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?>">$<?= number_format($m['monto'], 2) ?></span>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot; <?= esc($m['persona']) ?>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (count($gastos) + count($pagos) > 10): ?>
                <div class="text-center mb-4">
                    <a href="<?= base_url('gastos?grupo_id=' . $grupo['id']) ?>" class="text-decoration-none small">Ver todos los movimientos &rarr;</a>
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
