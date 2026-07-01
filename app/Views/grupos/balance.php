<?= view('partials/_head', ['title' => 'Gastito - Balance: ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Balance']) ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-secondary btn-sm mb-3 d-none d-lg-inline-flex">&larr; Volver al grupo</a>

        <?php
            $miUserId = (int) session()->get('userId');
            $miBalance = current(array_filter($balance, fn($b) => $b['user_id'] == $miUserId));
            $miSaldo = $miBalance['saldo'] ?? 0;
            $misDeudas = array_values(array_filter($deudas, fn($d) => (int) $d['deudor_id'] === $miUserId));
            $debo = $miSaldo < 0 && $misDeudas !== [];
            $grupoCerrado = $grupo['estado'] === 'cerrado';
            $grupoActivo = $grupo['estado'] === 'activo';
            $esAdmin = ($rol ?? '') === 'admin';
        ?>

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
                    Total gastado: <strong><?= moneda($totalGastado) ?></strong> &middot;
                    Total pagos: <strong><?= moneda($totalPagado) ?></strong>
                </p>
            </div>
        </div>

        <?php if ($debo && $grupoCerrado): ?>
        <div class="d-grid mb-4">
            <a href="#transferencias-sugeridas" class="btn btn-warning btn-lg">Saldar deuda</a>
        </div>
        <?php elseif ($debo && $grupoActivo): ?>
        <div class="d-grid mb-4">
            <a href="#transferencias-sugeridas" class="btn btn-success btn-lg">Registrar pago</a>
        </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4" id="balance-por-usuario">
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
                                        <td class="text-end"><?= moneda($b['total_pagado_gastos']) ?></td>
                                        <td class="text-end"><?= moneda($b['total_consumido']) ?></td>
                                        <td class="text-end"><?= moneda($b['pagos_enviados']) ?></td>
                                        <td class="text-end"><?= moneda($b['pagos_recibidos']) ?></td>
                                        <td class="text-end fw-bold <?= $b['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= moneda($b['saldo']) ?>
                                        </td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= $badgeText ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Mobile: cards con avatar, progreso y detalle expandible -->
                <div class="d-md-none">
                    <?php
                        $avatarColors = ['#0d6efd','#198754','#dc3545','#fd7e14','#6f42c1','#20c997','#e83e8c','#6610f2'];
                        $colorIdx = 0;
                    ?>
                    <?php foreach ($balance as $b): ?>
                        <?php
                            $inicial = mb_strtoupper(mb_substr(trim($b['name']), 0, 1));
                            $avaColor = $avatarColors[$colorIdx % count($avatarColors)];
                            $colorIdx++;
                            $saldoClase = $b['saldo'] >= 0 ? 'text-success' : 'text-danger';
                            $total = max($b['total_pagado_gastos'], $b['total_consumido'], 1);
                            $pagadoPct = min(round($b['total_pagado_gastos'] / $total * 100), 100);
                            $consumidoPct = min(round($b['total_consumido'] / $total * 100), 100);
                            $collapseId = 'detalle-' . $b['user_id'];
                        ?>
                        <div class="mobile-card-item">
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="avatar-circle" style="background:<?= $avaColor ?>"><?= $inicial ?></div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium"><?= esc($b['name']) ?></div>
                                    <div class="fw-bold fs-5 <?= $saldoClase ?>">
                                        <?= moneda(abs($b['saldo'])) ?>
                                        <small class="fw-normal text-muted fs-6"><?= $b['saldo'] >= 0 ? 'a favor' : 'debe' ?></small>
                                    </div>
                                </div>
                            </div>
                            <div class="small text-muted mb-1 d-flex justify-content-between">
                                <span>Pag&oacute;: <?= moneda($b['total_pagado_gastos']) ?></span>
                                <span>Consumi&oacute;: <?= moneda($b['total_consumido']) ?></span>
                            </div>
                            <div class="progress mb-2" style="height:8px">
                                <div class="progress-bar bg-primary" style="width:<?= $pagadoPct ?>%" title="Pag&oacute;"></div>
                                <div class="progress-bar bg-warning" style="width:<?= $consumidoPct ?>%" title="Consumi&oacute;"></div>
                            </div>
                            <button class="btn btn-sm btn-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false">
                                Ver detalle
                            </button>
                            <div class="collapse mt-2" id="<?= $collapseId ?>">
                                <div class="card card-body py-2 px-3">
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Pag&oacute; en gastos:</span>
                                        <span><?= moneda($b['total_pagado_gastos']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Consumi&oacute;:</span>
                                        <span><?= moneda($b['total_consumido']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Pagos enviados:</span>
                                        <span><?= moneda($b['pagos_enviados']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted">Pagos recibidos:</span>
                                        <span><?= moneda($b['pagos_recibidos']) ?></span>
                                    </div>
                                    <hr class="my-1">
                                    <div class="d-flex justify-content-between small fw-bold <?= $saldoClase ?>">
                                        <span>Saldo neto:</span>
                                        <span><?= moneda(abs($b['saldo'])) ?> (<?= $b['saldo'] >= 0 ? 'a favor' : 'debe' ?>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Gastitos por categoría</h5>
            </div>
            <?php if (empty($gastosPorCategoria)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay gastitos registrados.</p>
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
                                        <td class="text-end fw-medium"><?= moneda($cat['total']) ?></td>
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
                                <span class="fw-medium"><?= moneda($cat['total']) ?></span>
                            </div>
                            <div class="text-muted small"><?= $cat['cantidad'] ?> <?= pluralizar((int) $cat['cantidad'], 'gastito', 'gastitos') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm mb-4" id="transferencias-sugeridas">
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
                                    <div class="fw-bold text-danger fs-5"><?= moneda($d['monto']) ?></div>
                                    <?php if ((int) $d['deudor_id'] === (int) session()->get('userId')): ?>
                                    <button type="button" class="btn btn-success pagar-btn" data-target="pagar-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>" style="min-height:44px">
                                        Pagar
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div id="pagar-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>" class="mt-2 d-none">
                                <div class="card border">
                                    <div class="card-body py-2 px-3">
                                        <p class="mb-1 small text-muted">Al registrar el pago, se actualizar&aacute; el saldo del grupo.</p>
                                        <?php if (!empty($mediosPorAcreedor[$acreedorId])): ?>
                                            <p class="mb-1 small text-muted fw-medium">Medios de cobro de <?= esc($d['acreedor']) ?>:</p>
                                            <?php foreach ($mediosPorAcreedor[$acreedorId] as $m): ?>
                                                <div class="mb-1">
                                                    <span class="small fw-medium"><?= esc($m['nombre'] ?? $m['tipo']) ?></span>
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <?php if ($m['alias']): ?>
                                                            <span class="small text-muted me-2">Alias: <?= esc($m['alias']) ?></span>
                                                            <button type="button" class="btn btn-secondary copiar-btn" data-copiar="<?= esc($m['alias'], 'attr') ?>" style="min-height:44px">Copiar alias</button>
                                                        <?php endif; ?>
                                                        <?php if ($m['cbu_cvu']): ?>
                                                            <span class="small text-muted me-2">CBU/CVU: <?= esc($m['cbu_cvu']) ?></span>
                                                            <button type="button" class="btn btn-secondary copiar-btn" data-copiar="<?= esc($m['cbu_cvu'], 'attr') ?>" style="min-height:44px">Copiar CBU/CVU</button>
                                                        <?php endif; ?>
                                                        <?php if ($m['payment_link']): ?>
                                                            <a href="<?= esc($m['payment_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary" style="min-height:44px">Abrir link de pago</a>
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
                                            <?php if ($grupo['estado'] === 'cerrado'): ?>
                                            <button type="button" class="btn btn-sm btn-warning pago-manual-btn" data-target="pago-manual-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>">
                                                Saldar deuda
                                            </button>
                                            <p class="small text-muted mt-1 mb-0">Deuda pendiente: <strong><?= moneda($d['monto']) ?></strong> a <?= esc($d['acreedor']) ?>.</p>
                                            <form action="<?= base_url('grupos/' . $grupo['id'] . '/saldar-deuda') ?>" method="post" id="pago-manual-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>" class="mt-2 d-none">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="receptor_id" value="<?= $acreedorId ?>">
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Monto (m&aacute;x <?= moneda($d['monto']) ?>)</label>
                                                    <input type="number" step="0.01" min="0.01" max="<?= esc(number_format((float) $d['monto'], 2, '.', '')) ?>" name="monto" class="form-control" value="<?= esc(number_format((float) $d['monto'], 2, '.', '')) ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Fecha</label>
                                                    <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Descripci&oacute;n</label>
                                                    <input type="text" name="descripcion" class="form-control" value="Saldar deuda - <?= esc($grupo['nombre']) ?>" maxlength="255">
                                                </div>
                                                <button type="submit" class="btn btn-warning w-100">Confirmar pago</button>
                                            </form>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-primary pago-manual-btn" data-target="pago-manual-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>">
                                                Registrar pago manual
                                            </button>
                                            <p class="small text-muted mt-1 mb-0">Se registrar&aacute; un pago de <?= esc($d['deudor']) ?> a <?= esc($d['acreedor']) ?> por <?= moneda($d['monto']) ?>.</p>
                                            <form action="<?= base_url('pagos') ?>" method="post" id="pago-manual-<?= $d['deudor_id'] ?>-<?= $acreedorId ?>" class="mt-2 d-none">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="grupo_id" value="<?= (int) $grupo['id'] ?>">
                                                <input type="hidden" name="receptor_id" value="<?= $acreedorId ?>">
                                                <input type="hidden" name="descripcion" value="Pago de deuda - <?= esc($grupo['nombre']) ?>">
                                                <input type="hidden" name="origen" value="grupo_balance_detalle">
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Monto</label>
                                                    <input type="number" step="0.01" min="0.01" name="monto" class="form-control" value="<?= esc(number_format((float) $d['monto'], 2, '.', '')) ?>" required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Fecha</label>
                                                    <input type="date" name="fecha" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                                </div>
                                                <button type="submit" class="btn btn-success w-100">Confirmar pago</button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
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

        document.querySelectorAll('.pago-manual-btn').forEach(function(btn) {
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
