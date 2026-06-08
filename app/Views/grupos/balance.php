<?= view('partials/_head', ['title' => 'SplitWise - Balance: ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver al grupo</a>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="fw-bold mb-1"><?= esc($grupo['nombre']) ?></h3>
                <?php
                    $badgeEstado = [
                        'activo' => 'bg-success',
                        'cerrado' => 'bg-warning text-dark',
                        'liquidado' => 'bg-secondary',
                    ];
                    $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                ?>
                <span class="badge <?= $claseEstado ?> mb-2"><?= ucfirst($grupo['estado']) ?></span>
                <p class="text-muted small mb-0">
                    <?= count($miembros) ?> miembro(s) &middot;
                    Total gastado: <strong>$<?= number_format($totalGastado, 2) ?></strong> &middot;
                    Total pagos: <strong>$<?= number_format($totalPagado, 2) ?></strong>
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Balance por usuario</h5>
            </div>
            <?php if (empty($balance)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay datos de balance para mostrar.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-end">Pagó en gastos</th>
                                    <th class="text-end">Consumió</th>
                                    <th class="text-end">Pagos enviados</th>
                                    <th class="text-end">Pagos recibidos</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($balance as $b): ?>
                                    <?php
                                        $estado = $b['saldo'] > 0 ? 'a-favor' : ($b['saldo'] < 0 ? 'debe' : 'saldado');
                                        $badgeClass = $b['saldo'] > 0 ? 'text-bg-success' : ($b['saldo'] < 0 ? 'text-bg-danger' : 'text-bg-secondary');
                                        $badgeText = $b['saldo'] > 0 ? 'A favor' : ($b['saldo'] < 0 ? 'Debe' : 'Saldado');
                                    ?>
                                    <tr>
                                        <td class="fw-medium"><?= esc($b['name']) ?></td>
                                        <td class="text-end">$<?= number_format($b['total_pagado_gastos'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($b['total_consumido'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($b['pagos_enviados'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($b['pagos_recibidos'], 2) ?></td>
                                        <td class="text-end fw-bold <?= $b['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            $<?= number_format($b['saldo'], 2) ?>
                                        </td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($balance as $b): ?>
                        <?php
                            $estado = $b['saldo'] > 0 ? 'a-favor' : ($b['saldo'] < 0 ? 'debe' : 'saldado');
                            $badgeClass = $b['saldo'] > 0 ? 'text-bg-success' : ($b['saldo'] < 0 ? 'text-bg-danger' : 'text-bg-secondary');
                            $badgeText = $b['saldo'] > 0 ? 'A favor' : ($b['saldo'] < 0 ? 'Debe' : 'Saldado');
                        ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="fw-medium"><?= esc($b['name']) ?></div>
                                <span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mt-1">
                                <span class="text-muted">Pagó:</span>
                                <span>$<?= number_format($b['total_pagado_gastos'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Consumió:</span>
                                <span>$<?= number_format($b['total_consumido'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Envió:</span>
                                <span>$<?= number_format($b['pagos_enviados'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Recibió:</span>
                                <span>$<?= number_format($b['pagos_recibidos'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small mt-1 fw-bold <?= $b['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                <span>Saldo:</span>
                                <span>$<?= number_format($b['saldo'], 2) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Gastos por categoría</h5>
            </div>
            <?php if (empty($gastosPorCategoria)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay gastos registrados.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Categoría</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastosPorCategoria as $cat): ?>
                                    <tr>
                                        <td><span class="badge bg-light text-dark"><?= esc($cat['categoria_nombre']) ?></span></td>
                                        <td class="text-end"><?= $cat['cantidad'] ?></td>
                                        <td class="text-end fw-medium">$<?= number_format($cat['total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($gastosPorCategoria as $cat): ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-light text-dark"><?= esc($cat['categoria_nombre']) ?></span>
                                <span class="fw-medium">$<?= number_format($cat['total'], 2) ?></span>
                            </div>
                            <div class="text-muted small"><?= $cat['cantidad'] ?> gasto(s)</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Transferencias sugeridas</h5>
            </div>
            <?php if (empty($deudas)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">
                        <span class="text-success fw-bold">&check;</span>
                        El grupo está saldado. No hay deudas pendientes.
                    </p>
                </div>
            <?php else: ?>
                <div class="card-body p-0">
                    <?php foreach ($deudas as $d): ?>
                        <?php $acreedorId = (int) $d['acreedor_id']; ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= esc($d['deudor']) ?></strong>
                                    <span class="text-muted"> le debe a </span>
                                    <strong><?= esc($d['acreedor']) ?></strong>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="fw-bold text-danger fs-5">$<?= number_format($d['monto'], 2) ?></div>
                                    <button type="button" class="btn btn-sm btn-success pagar-btn" data-target="pagar-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>">
                                        Pagar
                                    </button>
                                </div>
                            </div>
                            <div id="pagar-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>" class="mt-2 d-none">
                                <div class="card border">
                                    <div class="card-body py-2 px-3">
                                        <?php if (!empty($mediosPorAcreedor[$acreedorId])): ?>
                                            <p class="mb-1 small text-muted fw-medium">Medios de cobro de <?= esc($d['acreedor']) ?>:</p>
                                            <?php foreach ($mediosPorAcreedor[$acreedorId] as $m): ?>
                                                <div class="mb-1">
                                                    <span class="small fw-medium"><?= esc($m['nombre'] ?? $m['tipo']) ?></span>
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <?php if ($m['alias']): ?>
                                                            <span class="small text-muted me-2">Alias: <?= esc($m['alias']) ?></span>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 copiar-btn" data-copiar="<?= esc($m['alias'], 'attr') ?>">Copiar alias</button>
                                                        <?php endif; ?>
                                                        <?php if ($m['cbu_cvu']): ?>
                                                            <span class="small text-muted me-2">CBU/CVU: <?= esc($m['cbu_cvu']) ?></span>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-1 copiar-btn" data-copiar="<?= esc($m['cbu_cvu'], 'attr') ?>">Copiar CBU/CVU</button>
                                                        <?php endif; ?>
                                                        <?php if ($m['payment_link']): ?>
                                                            <a href="<?= esc($m['payment_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary py-0 px-1">Abrir link de pago</a>
                                                        <?php endif; ?>
                                                        <?php if ($m['banco']): ?>
                                                            <span class="small text-muted">- <?= esc($m['banco']) ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p class="mb-1 small text-muted"><?= esc($d['acreedor']) ?> no tiene medios de cobro registrados.</p>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <a href="<?= base_url('pagos/nuevo?grupo_id=' . $grupo['id'] . '&pagador_id=' . $d['deudor_id'] . '&receptor_id=' . $acreedorId . '&monto=' . $d['monto'] . '&fecha=' . date('Y-m-d')) ?>" class="btn btn-sm btn-primary">
                                                Registrar pago
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($grupo['estado'] === 'cerrado' && empty($deudas) && $rol === 'admin'): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="estado" value="liquidado">
                        <p class="mb-3">El grupo está saldado y cerrado. ¿Marcar como liquidado?</p>
                        <button type="submit" class="btn btn-secondary">Liquidar grupo</button>
                    </form>
                </div>
            </div>
        <?php elseif ($grupo['estado'] === 'cerrado' && !empty($deudas) && $rol === 'admin'): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">
                        Hay deudas pendientes. Registrá los pagos correspondientes antes de liquidar.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.querySelectorAll('.pagar-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var target = document.getElementById(targetId);
                if (target) {
                    target.classList.toggle('d-none');
                }
            });
        });

        document.querySelectorAll('.copiar-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var texto = this.getAttribute('data-copiar');
                var copiar = function(t) {
                    navigator.clipboard.writeText(t).then(function() {
                        var textoOriginal = this.textContent;
                        this.textContent = 'Copiado!';
                        var self = this;
                        setTimeout(function() {
                            self.textContent = textoOriginal;
                        }, 2000);
                    }.bind(this)).catch(function() {
                        fallbackCopiar(t, this);
                    }.bind(this));
                }.bind(this);
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    copiar(texto);
                } else {
                    fallbackCopiar(texto, this);
                }
            });
        });

        function fallbackCopiar(texto, btn) {
            var textarea = document.createElement('textarea');
            textarea.value = texto;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                var textoOriginal = btn.textContent;
                btn.textContent = 'Copiado!';
                setTimeout(function() {
                    btn.textContent = textoOriginal;
                }, 2000);
            } catch (e) {
                alert('No se pudo copiar al portapapeles. Seleccioná y copiá manualmente.');
            }
            document.body.removeChild(textarea);
        }
    </script>
<?= view('partials/_footer') ?>
