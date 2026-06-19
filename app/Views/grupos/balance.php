<?= view('partials/_head', ['title' => 'SplitWise - Balance: ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Balance']) ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-secondary btn-sm mb-3">&larr; Volver al grupo</a>

        <?php
            $badgeEstado = [
                'activo' => 'bg-success',
                'cerrado' => 'bg-warning text-dark',
                'liquidado' => 'bg-secondary',
            ];
            $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
        ?>

        <!-- Group summary -->
        <div class="balance-strip mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <h3 class="mb-0"><?= esc($grupo['nombre']) ?></h3>
                <span class="badge <?= $claseEstado ?>"><?= ucfirst($grupo['estado']) ?></span>
            </div>
            <div class="balance-strip-detail">
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Miembros:</span>
                    <span class="financial-amount"><?= count($miembros) ?></span>
                </div>
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Gastado:</span>
                    <span class="financial-amount">$<?= number_format($totalGastado, 2) ?></span>
                </div>
                <div class="balance-strip-detail-item">
                    <span class="text-muted">Pagado:</span>
                    <span class="financial-amount">$<?= number_format($totalPagado, 2) ?></span>
                </div>
            </div>
        </div>

        <!-- Balance por usuario -->
        <div class="section-header">
            <div class="section-header-title">Balance por usuario</div>
        </div>
        <?php if (empty($balance)): ?>
            <div class="card mb-3">
                <div class="empty-state" style="padding:24px;">
                    <div class="empty-state-text">No hay datos de balance para mostrar.</div>
                </div>
            </div>
        <?php else: ?>
            <!-- Desktop table -->
            <div class="card mb-3 d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th class="text-end">Pag&oacute; en gastos</th>
                                <th class="text-end">Consumi&oacute;</th>
                                <th class="text-end">Pagos enviados</th>
                                <th class="text-end">Pagos recibidos</th>
                                <th class="text-end">Saldo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($balance as $b): ?>
                                <?php
                                    $badgeClass = $b['saldo'] > 0 ? 'text-bg-success' : ($b['saldo'] < 0 ? 'text-bg-danger' : 'text-bg-secondary');
                                    $badgeText = $b['saldo'] > 0 ? 'A favor' : ($b['saldo'] < 0 ? 'Debe' : 'Saldado');
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc($b['name']) ?></td>
                                    <td class="text-end financial-amount">$<?= number_format($b['total_pagado_gastos'], 2) ?></td>
                                    <td class="text-end financial-amount">$<?= number_format($b['total_consumido'], 2) ?></td>
                                    <td class="text-end financial-amount">$<?= number_format($b['pagos_enviados'], 2) ?></td>
                                    <td class="text-end financial-amount">$<?= number_format($b['pagos_recibidos'], 2) ?></td>
                                    <td class="text-end financial-amount fw-bold <?= $b['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        $<?= number_format($b['saldo'], 2) ?>
                                    </td>
                                    <td><span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Mobile list -->
            <div class="card mb-3 d-md-none">
                <?php
                    $avatarColors = ['avatar-blue', 'avatar-green', 'avatar-purple', 'avatar-orange', 'avatar-teal', 'avatar-pink', 'avatar-red', 'avatar-indigo'];
                    $colorIdx = 0;
                ?>
                <?php foreach ($balance as $b): ?>
                    <?php
                        $inicial = mb_strtoupper(mb_substr(trim($b['name']), 0, 1));
                        $avaColor = $avatarColors[$colorIdx % count($avatarColors)];
                        $colorIdx++;
                        $saldoClase = $b['saldo'] >= 0 ? 'text-success' : 'text-danger';
                        $collapseId = 'detalle-' . $b['user_id'];
                    ?>
                    <div class="financial-list-item" style="flex-wrap:wrap;">
                        <div class="avatar <?= $avaColor ?>"><?= $inicial ?></div>
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title"><?= esc($b['name']) ?></div>
                            <div class="financial-list-item-subtitle">
                                Pag&oacute;: $<?= number_format($b['total_pagado_gastos'], 2) ?> &middot;
                                Consumi&oacute;: $<?= number_format($b['total_consumido'], 2) ?>
                            </div>
                        </div>
                        <div class="financial-list-item-amount <?= $saldoClase ?>">
                            $<?= number_format(abs($b['saldo']), 2) ?>
                            <div style="font-size:10px;font-weight:600;"><?= $b['saldo'] >= 0 ? 'a favor' : 'debe' ?></div>
                        </div>
                        <button class="btn btn-sm btn-secondary w-100 mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false">
                            Ver detalle
                        </button>
                        <div class="collapse w-100 mt-2" id="<?= $collapseId ?>">
                            <div style="padding:8px;background:var(--surface);border-radius:var(--radius-sm);">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Pag&oacute; en gastos:</span>
                                    <span class="financial-amount">$<?= number_format($b['total_pagado_gastos'], 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Consumi&oacute;:</span>
                                    <span class="financial-amount">$<?= number_format($b['total_consumido'], 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Pagos enviados:</span>
                                    <span class="financial-amount">$<?= number_format($b['pagos_enviados'], 2) ?></span>
                                </div>
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="text-muted">Pagos recibidos:</span>
                                    <span class="financial-amount">$<?= number_format($b['pagos_recibidos'], 2) ?></span>
                                </div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between small fw-bold <?= $saldoClase ?>">
                                    <span>Saldo neto:</span>
                                    <span class="financial-amount">$<?= number_format(abs($b['saldo']), 2) ?> (<?= $b['saldo'] >= 0 ? 'a favor' : 'debe' ?>)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Gastos por categoria -->
        <div class="section-header">
            <div class="section-header-title">Gastos por categor&iacute;a</div>
        </div>
        <?php if (empty($gastosPorCategoria)): ?>
            <div class="card mb-3">
                <div class="empty-state" style="padding:24px;">
                    <div class="empty-state-text">No hay gastos registrados.</div>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-3 d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Categor&iacute;a</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($gastosPorCategoria as $cat): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?= esc($cat['categoria_nombre']) ?></span></td>
                                    <td class="text-end"><?= $cat['cantidad'] ?></td>
                                    <td class="text-end financial-amount">$<?= number_format($cat['total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card mb-3 d-md-none">
                <?php foreach ($gastosPorCategoria as $cat): ?>
                    <div class="financial-list-item">
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title" style="font-size:14px;">
                                <span class="badge bg-secondary"><?= esc($cat['categoria_nombre']) ?></span>
                            </div>
                            <div class="financial-list-item-subtitle"><?= $cat['cantidad'] ?> gasto(s)</div>
                        </div>
                        <div class="financial-list-item-amount">$<?= number_format($cat['total'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Transferencias sugeridas -->
        <div class="section-header">
            <div class="section-header-title">Transferencias sugeridas</div>
        </div>
        <?php if (empty($deudas)): ?>
            <div class="card mb-3">
                <div class="financial-list-item" style="justify-content:center;flex-direction:column;text-align:center;padding:24px;">
                    <span class="status-dot status-dot-active" style="width:10px;height:10px;margin-bottom:8px;"></span>
                    <span style="font-size:14px;color:var(--muted);">El grupo est&aacute; saldado. No hay deudas pendientes.</span>
                </div>
            </div>
        <?php else: ?>
            <div class="card mb-3">
                <?php foreach ($deudas as $d): ?>
                    <?php $acreedorId = (int) $d['acreedor_id']; ?>
                    <div class="financial-list-item" style="flex-wrap:wrap;border-left:3px solid var(--danger);">
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title" style="font-size:14px;">
                                <strong><?= esc($d['deudor']) ?></strong>
                                <span class="text-muted" style="font-weight:400;"> le debe a </span>
                                <strong><?= esc($d['acreedor']) ?></strong>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="financial-list-item-amount text-danger fs-5">$<?= number_format($d['monto'], 2) ?></div>
                            <?php if ((int) $d['deudor_id'] === (int) session()->get('userId')): ?>
                            <button type="button" class="btn btn-success btn-sm pagar-btn" data-target="pagar-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>">
                                Pagar
                            </button>
                            <?php endif; ?>
                        </div>
                        <div id="pagar-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>" class="w-100 mt-2 d-none">
                            <div style="padding:12px;background:var(--surface);border-radius:var(--radius-sm);">
                                <p class="mb-1 small text-muted">Al registrar el pago, se actualizar&aacute; el saldo del grupo.</p>
                                <?php if (!empty($mediosPorAcreedor[$acreedorId])): ?>
                                    <p class="mb-1 small text-muted fw-medium">Medios de cobro de <?= esc($d['acreedor']) ?>:</p>
                                    <?php foreach ($mediosPorAcreedor[$acreedorId] as $m): ?>
                                        <div class="mb-1">
                                            <span class="small fw-medium"><?= esc($m['nombre'] ?? $m['tipo']) ?></span>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                <?php if ($m['alias']): ?>
                                                    <span class="small text-muted me-2">Alias: <?= esc($m['alias']) ?></span>
                                                    <button type="button" class="btn btn-secondary btn-sm copiar-btn" data-copiar="<?= esc($m['alias'], 'attr') ?>">Copiar alias</button>
                                                <?php endif; ?>
                                                <?php if ($m['cbu_cvu']): ?>
                                                    <span class="small text-muted me-2">CBU/CVU: <?= esc($m['cbu_cvu']) ?></span>
                                                    <button type="button" class="btn btn-secondary btn-sm copiar-btn" data-copiar="<?= esc($m['cbu_cvu'], 'attr') ?>">Copiar CBU/CVU</button>
                                                <?php endif; ?>
                                                <?php if ($m['payment_link']): ?>
                                                    <a href="<?= esc($m['payment_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm">Abrir link de pago</a>
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
                                <?php if ((int) $d['deudor_id'] === (int) session()->get('userId')): ?>
                                <div class="mt-2">
                                    <a href="<?= base_url('pagos/nuevo?grupo_id=' . $grupo['id'] . '&pagador_id=' . $d['deudor_id'] . '&receptor_id=' . $acreedorId . '&monto=' . $d['monto'] . '&fecha=' . date('Y-m-d')) ?>" class="btn btn-sm btn-primary">
                                        Registrar pago manual
                                    </a>
                                    <p class="small text-muted mt-1 mb-0">Se registrar&aacute; un pago de <?= esc($d['deudor']) ?> a <?= esc($d['acreedor']) ?> por $<?= number_format($d['monto'], 2) ?>.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($grupo['estado'] === 'cerrado' && empty($deudas) && $rol === 'admin'): ?>
            <div class="card mb-3">
                <div class="card-body text-center" style="padding:24px;">
                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="estado" value="liquidado">
                        <p class="mb-3">El grupo est&aacute; saldado y cerrado. &iquest;Marcar como liquidado?</p>
                        <button type="submit" class="btn btn-secondary">Liquidar grupo</button>
                    </form>
                </div>
            </div>
        <?php elseif ($grupo['estado'] === 'cerrado' && !empty($deudas) && $rol === 'admin'): ?>
            <div class="card mb-3">
                <div class="card-body text-center" style="padding:24px;">
                    <p class="text-muted mb-0">
                        Hay deudas pendientes. Registr&aacute; los pagos correspondientes antes de liquidar.
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
                alert('No se pudo copiar al portapapeles. Seleccion&aacute; y copi&aacute; manualmente.');
            }
            document.body.removeChild(textarea);
        }
    </script>
<?= view('partials/_footer') ?>
