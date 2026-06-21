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
            $misDeudas = array_values(array_filter($deudas, fn($d) => (int) $d['deudor_id'] === (int) session()->get('userId')));
            $deudaPrincipal = $misDeudas[0] ?? null;
            $puedePagarBalance = $miSaldo < 0 && $permisos['puede_crear_pago'] && $deudaPrincipal !== null;

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

                <?php if ($puedePagarBalance): ?>
                <button type="button"
                   class="grupo-balance-card grupo-balance-button d-block card border-0 mb-3 w-100"
                   style="background: #fef2f2; transition: filter 0.15s;"
                   data-bs-toggle="modal"
                   data-bs-target="#pagarBalanceModal">
                    <div class="card-body py-3 text-center">
                        <div class="small text-muted mb-1">Tu balance</div>
                        <div class="fw-bold fs-4 text-danger">
                            <?= moneda(abs($miSaldo)) ?>
                            <small class="fw-normal fs-6">debe</small>
                        </div>
                    </div>
                </button>
                <?php else: ?>
                <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>"
                   class="grupo-balance-card text-decoration-none d-block card border-0 mb-3"
                   style="background: <?= $miSaldo >= 0 ? '#f0fdf4' : '#fef2f2' ?>; transition: filter 0.15s;">
                    <div class="card-body py-3 text-center">
                        <div class="small text-muted mb-1">Tu balance</div>
                        <div class="fw-bold fs-4 <?= $miSaldo >= 0 ? 'text-success' : 'text-danger' ?>">
                            <?= moneda(abs($miSaldo)) ?>
                            <small class="fw-normal fs-6"><?= $miSaldo >= 0 ? 'a favor' : 'debe' ?></small>
                        </div>
                    </div>
                </a>
                <?php endif; ?>

                <?php if ($grupo['descripcion']): ?>
                    <p class="mb-0 text-muted small"><?= esc($grupo['descripcion']) ?></p>
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
                        'created_at' => $g['created_at'] ?? $g['fecha'],
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
                        'created_at' => $p['created_at'] ?? $p['fecha'],
                    ];
                }
                usort($movimientos, fn($a, $b) => strcmp($b['fecha'] . ' ' . $b['created_at'], $a['fecha'] . ' ' . $a['created_at']));
                $movimientos = array_slice($movimientos, 0, 10);
            ?>
            <?php foreach ($movimientos as $m): ?>
                <?php if ($m['tipo'] === 'gasto'): ?>
                <a href="<?= base_url('gastos/' . $m['id']) ?>" class="report-movement-link">
                <?php else: ?>
                <a href="<?= base_url('pagos/' . $m['id']) ?>" class="report-movement-link">
                <?php endif; ?>
                    <div class="report-movement-card <?= $m['tipo'] === 'gasto' ? 'report-movement-expense' : 'report-movement-payment' ?> mb-2" style="border-left: 3px solid <?= $m['tipo'] === 'gasto' ? '#2563eb' : '#16a34a' ?>;">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="min-width-0">
                                <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?> me-1"><?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                <span class="fw-medium small"><?= esc($m['descripcion']) ?></span>
                                <?php if ($m['categoria_nombre']): ?>
                                    <span class="badge bg-light text-dark ms-1"><?= esc($m['categoria_nombre']) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="fw-bold small <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?> text-nowrap"><?= moneda($m['monto']) ?></span>
                        </div>
                        <div class="text-muted small mt-1">
                            <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot; <?= esc($m['persona']) ?>
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

    <?php if ($puedePagarBalance): ?>
    <div class="modal fade" id="pagarBalanceModal" tabindex="-1" aria-labelledby="pagarBalanceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content pagar-balance-modal">
                <form action="<?= base_url('pagos') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="grupo_id" value="<?= (int) $grupo['id'] ?>">
                    <input type="hidden" name="receptor_id" id="balancePagoReceptor" value="<?= (int) $deudaPrincipal['acreedor_id'] ?>">
                    <input type="hidden" name="descripcion" value="Pago de deuda - <?= esc($grupo['nombre']) ?>">
                    <input type="hidden" name="origen" value="grupo_balance">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="pagarBalanceModalLabel">Pagar deuda</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted mb-3">Registr&aacute; un pago para reducir tu deuda en <?= esc($grupo['nombre']) ?>.</p>

                        <?php if (count($misDeudas) > 1): ?>
                            <div class="mb-3">
                                <label class="form-label fw-medium">Eleg&iacute; a qui&eacute;n pagar</label>
                                <div class="d-grid gap-2">
                                    <?php foreach ($misDeudas as $idx => $deuda): ?>
                                        <label class="deuda-option <?= $idx === 0 ? 'is-selected' : '' ?>">
                                            <input type="radio"
                                                name="deuda_option"
                                                value="<?= (int) $deuda['acreedor_id'] ?>"
                                                data-monto="<?= esc(number_format((float) $deuda['monto'], 2, '.', '')) ?>"
                                                data-monto-visual="<?= esc(moneda($deuda['monto'], false)) ?>"
                                                <?= $idx === 0 ? 'checked' : '' ?>>
                                            <span>
                                                <strong><?= esc($deuda['acreedor']) ?></strong>
                                                <small>Le deb&eacute;s</small>
                                            </span>
                                            <strong><?= moneda($deuda['monto']) ?></strong>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="pagar-balance-summary mb-3">
                                <span>Le pag&aacute;s a</span>
                                <strong><?= esc($deudaPrincipal['acreedor']) ?></strong>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="balancePagoMontoVisual" class="form-label fw-medium">Monto a pagar</label>
                            <input type="text"
                                inputmode="decimal"
                                class="form-control form-control-lg"
                                id="balancePagoMontoVisual"
                                name="monto_visual"
                                value="<?= esc(moneda($deudaPrincipal['monto'], false)) ?>"
                                required>
                            <input type="hidden"
                                id="balancePagoMonto"
                                name="monto"
                                value="<?= esc(number_format((float) $deudaPrincipal['monto'], 2, '.', '')) ?>">
                            <div class="form-text">Pod&eacute;s registrar un pago parcial si no vas a saldar todo ahora.</div>
                        </div>

                        <div class="mb-0">
                            <label for="balancePagoFecha" class="form-label fw-medium">Fecha</label>
                            <input type="date" class="form-control" id="balancePagoFecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Registrar pago</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        const balancePagoVisible = document.getElementById('balancePagoMontoVisual');
        const balancePagoHidden = document.getElementById('balancePagoMonto');

        function normalizarMontoArgentino(valor) {
            return valor.trim().replace(/\./g, '').replace(',', '.');
        }

        function agregarMiles(valor) {
            return valor.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function formatearMontoArgentino(valor) {
            const limpio = valor.replace(/[^\d,]/g, '');
            const partes = limpio.split(',');
            const entero = agregarMiles((partes[0] || '').replace(/^0+(?=\d)/, '') || '0');
            const tieneComa = limpio.includes(',');

            if (!tieneComa) {
                return entero;
            }

            const decimales = (partes[1] || '').replace(/\D/g, '').slice(0, 2);
            return entero + ',' + decimales;
        }

        function formatearMontoArgentinoCompleto(valor) {
            const normalizado = normalizarMontoArgentino(valor);
            const numero = Number.parseFloat(normalizado);

            if (!Number.isFinite(numero)) {
                return '';
            }

            const fijo = numero.toFixed(2);
            const partes = fijo.split('.');
            return agregarMiles(partes[0]) + ',' + partes[1];
        }

        if (balancePagoVisible && balancePagoHidden) {
            balancePagoVisible.addEventListener('input', function() {
                this.value = formatearMontoArgentino(this.value);
                balancePagoHidden.value = normalizarMontoArgentino(this.value);
            });

            balancePagoVisible.addEventListener('blur', function() {
                this.value = formatearMontoArgentinoCompleto(this.value);
                balancePagoHidden.value = normalizarMontoArgentino(this.value);
            });
        }

        document.querySelectorAll('#pagarBalanceModal input[name="deuda_option"]').forEach(function(input) {
            input.addEventListener('change', function() {
                document.querySelectorAll('#pagarBalanceModal .deuda-option').forEach(function(label) {
                    label.classList.remove('is-selected');
                });
                this.closest('.deuda-option').classList.add('is-selected');
                document.getElementById('balancePagoReceptor').value = this.value;
                balancePagoHidden.value = this.dataset.monto;
                balancePagoVisible.value = this.dataset.montoVisual;
            });
        });
    </script>
    <?php endif; ?>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
