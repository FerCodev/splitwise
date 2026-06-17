<?= view('partials/_head', ['title' => 'SplitWise - ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar') ?>

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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h4 class="fw-bold mb-0"><?= esc($grupo['nombre']) ?></h4>
                    <span class="badge <?= $claseEstado ?>"><?= ucfirst($grupo['estado']) ?></span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="fw-bold fs-5 <?= $miSaldo >= 0 ? 'text-success' : 'text-danger' ?>">
                        $<?= number_format(abs($miSaldo), 2) ?>
                        <small class="fw-normal text-muted fs-6"><?= $miSaldo >= 0 ? 'a favor' : 'debe' ?></small>
                    </span>
                    <span class="badge bg-<?= $rol === 'admin' ? 'warning' : 'secondary' ?>"><?= $rol === 'admin' ? 'Admin' : 'Miembro' ?></span>
                </div>
                <?php if ($permisos['puede_editar_grupo'] || $permisos['puede_agregar_miembro'] || $permisos['puede_cambiar_estado']): ?>
                    <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-primary w-100 py-2">Configurar grupo</a>
                <?php endif; ?>
                <?php if ($grupo['descripcion']): ?>
                    <p class="mt-2 mb-0 text-muted small"><?= esc($grupo['descripcion']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small">Transferencias sugeridas</span>
                        <div class="fw-bold fs-5"><?= count($deudas) ?></div>
                    </div>
                    <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-info btn-sm">Ver balance</a>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="fw-bold mb-0">Movimientos</h5>
            <div class="d-flex gap-1">
                <a href="<?= base_url('gastos?grupo_id=' . $grupo['id']) ?>" class="btn btn-outline-primary btn-sm">Gastos</a>
                <a href="<?= base_url('pagos?grupo_id=' . $grupo['id']) ?>" class="btn btn-outline-success btn-sm">Pagos</a>
            </div>
        </div>

        <?php if (empty($gastos) && empty($pagos)): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">
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
                        'persona' => $p['pagador_nombre'] . ' pag&oacute; a ' . $p['receptor_nombre'],
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
                    <div class="card border-0 shadow-sm mb-2">
                        <div class="card-body py-2 px-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-medium small"><?= esc($m['descripcion']) ?></span>
                                    <?php if ($m['categoria_nombre']): ?>
                                        <span class="badge bg-secondary ms-1"><?= esc($m['categoria_nombre']) ?></span>
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
