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
            $otrasDeudas = array_values(array_filter($deudas, fn($d) => (int) $d['deudor_id'] !== $miUserId));
        ?>

        <div class="card border-0 shadow-sm mb-4 balance-overview-card">
            <div class="card-body">
                <div class="balance-overview-heading">
                    <div>
                        <span class="balance-overview-eyebrow">Balance del grupo</span>
                        <h3 class="fw-bold mb-1"><?= esc($grupo['nombre']) ?></h3>
                    </div>
                <?php
                    $badgeEstado = [
                        'activo' => 'bg-success',
                        'cerrado' => 'bg-warning text-dark',
                        'liquidado' => 'bg-secondary',
                    ];
                    $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                ?>
                    <span class="badge <?= $claseEstado ?>"><?= ucfirst($grupo['estado']) ?></span>
                </div>

                <?php
                    $miSaldoClase = $miSaldo > 0 ? 'is-positive' : ($miSaldo < 0 ? 'is-negative' : 'is-settled');
                    $miSaldoTexto = $miSaldo > 0 ? 'a favor' : ($miSaldo < 0 ? 'debés' : 'saldado');
                ?>
                <div class="balance-overview-personal <?= $miSaldoClase ?>">
                    <span>Tu balance</span>
                    <strong><?= moneda(abs($miSaldo)) ?></strong>
                    <small><?= $miSaldoTexto ?></small>
                </div>

                <div class="balance-overview-stats">
                    <div><span>Integrantes</span><strong><?= count($miembros) ?></strong></div>
                    <div><span>Total gastado</span><strong><?= moneda($totalGastado) ?></strong></div>
                    <div><span>Pagos</span><strong><?= moneda($totalPagado) ?></strong></div>
                </div>
            </div>
        </div>

        <?php if ($debo): ?>
        <div class="mb-4">
            <h5 class="fw-bold mb-3">Tus deudas pendientes</h5>
            <?php foreach ($misDeudas as $idx => $d): ?>
                <?php $acreedorId = (int) $d['acreedor_id']; ?>
                <?= view('components/cards/deuda_pendiente', [
                    'monto'          => $d['monto'],
                    'acreedorNombre' => $d['acreedor'],
                    'acreedorId'     => $acreedorId,
                    'grupoId'        => (int) $grupo['id'],
                    'grupoEstado'    => $grupo['estado'],
                    'mediosCobro'    => $mediosPorAcreedor[$acreedorId] ?? [],
                    'formId'         => 'deuda-' . $idx . '-' . $acreedorId,
                    'fechaDefault'   => date('Y-m-d'),
                ]) ?>
            <?php endforeach; ?>
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
                                        <td class="fw-medium">
                                            <div class="d-flex align-items-center gap-2">
                                                <?= view('components/avatar', [
                                                    'userId' => $b['user_id'], 'name' => $b['name'],
                                                    'avatarFilename' => $b['avatar_filename'] ?? null,
                                                    'avatarUpdatedAt' => $b['avatar_updated_at'] ?? null, 'size' => 36,
                                                ]) ?>
                                                <span><?= esc($b['name']) ?></span>
                                            </div>
                                        </td>
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
                <!-- Mobile: tarjetas de saldo con detalle progresivo -->
                <div class="d-md-none balance-member-list">
                    <?php foreach ($balance as $b): ?>
                        <?php
                            $saldoClase = $b['saldo'] > 0 ? 'is-positive' : ($b['saldo'] < 0 ? 'is-negative' : 'is-settled');
                            $saldoTexto = $b['saldo'] > 0 ? 'A favor' : ($b['saldo'] < 0 ? 'Debe' : 'Saldado');
                            $collapseId = 'detalle-' . $b['user_id'];
                        ?>
                        <article class="balance-member-card <?= $saldoClase ?>">
                            <div class="balance-member-heading">
                                <div class="balance-member-person">
                                <?= view('components/avatar', [
                                    'userId' => $b['user_id'],
                                    'name' => $b['name'],
                                    'avatarFilename' => $b['avatar_filename'] ?? null,
                                    'avatarUpdatedAt' => $b['avatar_updated_at'] ?? null,
                                    'size' => 44,
                                ]) ?>
                                    <div>
                                        <strong><?= esc($b['name']) ?></strong>
                                        <?php if ((int) $b['user_id'] === $miUserId): ?><span>Vos</span><?php endif; ?>
                                    </div>
                                </div>
                                <div class="balance-member-result">
                                    <span><?= $saldoTexto ?></span>
                                    <strong><?= moneda(abs($b['saldo'])) ?></strong>
                                </div>
                            </div>

                            <div class="balance-member-metrics">
                                <div><span>Pagó en gastos</span><strong><?= moneda($b['total_pagado_gastos']) ?></strong></div>
                                <div><span>Consumió</span><strong><?= moneda($b['total_consumido']) ?></strong></div>
                            </div>

                            <button class="balance-member-toggle" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                <span>Ver desglose</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
                            </button>
                            <div class="collapse" id="<?= $collapseId ?>">
                                <div class="balance-member-breakdown">
                                    <div>
                                        <span class="text-muted">Pag&oacute; en gastos:</span>
                                        <span><?= moneda($b['total_pagado_gastos']) ?></span>
                                    </div>
                                    <div>
                                        <span class="text-muted">Consumi&oacute;:</span>
                                        <span><?= moneda($b['total_consumido']) ?></span>
                                    </div>
                                    <div>
                                        <span class="text-muted">Pagos enviados:</span>
                                        <span><?= moneda($b['pagos_enviados']) ?></span>
                                    </div>
                                    <div>
                                        <span class="text-muted">Pagos recibidos:</span>
                                        <span><?= moneda($b['pagos_recibidos']) ?></span>
                                    </div>
                                    <div class="balance-member-net">
                                        <span>Saldo neto:</span>
                                        <strong><?= moneda(abs($b['saldo'])) ?> · <?= mb_strtolower($saldoTexto) ?></strong>
                                    </div>
                                </div>
                            </div>
                        </article>
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

        <?php if (!empty($otrasDeudas)): ?>
        <div class="card border-0 shadow-sm mb-4" id="transferencias-sugeridas">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Estado general</h5>
            </div>
            <div class="card-body p-0">
                <?php foreach ($otrasDeudas as $d): ?>
                    <?php $acreedorId = (int) $d['acreedor_id']; ?>
                    <div class="mobile-card-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($d['deudor']) ?></strong>
                                <span class="text-muted"> le debe a </span>
                                <strong><?= esc($d['acreedor']) ?></strong>
                            </div>
                            <div class="fw-bold text-danger"><?= moneda($d['monto']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

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
        document.querySelectorAll('.deuda-pendiente-cta').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var targetId = this.getAttribute('data-target');
                var target = document.getElementById(targetId);
                if (target) {
                    var isOpen = !target.classList.contains('d-none');
                    target.classList.toggle('d-none');
                    this.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
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
