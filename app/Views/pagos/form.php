<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($pago) ? 'Editar' : 'Nuevo') . ' Pago']) ?>
<?= view('partials/_navbar', ['pageTitle' => isset($pago) ? 'Editar pago' : 'Nuevo pago']) ?>

    <?php $prefill = $prefill ?? []; ?>
    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4 d-none d-md-block"><?= isset($pago) ? 'Editar Pago' : 'Nuevo Pago' ?></h2>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="<?= isset($pago) ? base_url('pagos/' . $pago['id']) : base_url('pagos') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if (isset($pago)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-medium">Descripción <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion"
                               value="<?= esc(old('descripcion', $pago['descripcion'] ?? $prefill['descripcion'] ?? '')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="monto_visual" class="form-label fw-medium">Monto</label>
                        <input type="text" class="form-control" id="monto_visual" name="monto_visual"
                               value="<?= esc(old('monto_visual', isset($pago) ? numero_arg($pago['monto']) : ($prefill['monto'] !== '' && $prefill['monto'] !== null ? numero_arg((float) $prefill['monto']) : ''))) ?>" required
                               inputmode="numeric"
                               oninput="formatearMonto(this);">
                        <input type="hidden" name="monto" id="monto_real" value="<?= esc(old('monto', $pago['monto'] ?? $prefill['monto'] ?? '')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="fecha" class="form-label fw-medium">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha"
                               value="<?= esc(old('fecha', $pago['fecha'] ?? $prefill['fecha'] ?? date('Y-m-d'))) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="grupo_id" class="form-label fw-medium">Grupo</label>
                        <?php if (!empty($grupoId) && !isset($pago)): ?>
                        <?php
                            $grupoActual = array_filter($grupos, fn($g) => $g['id'] == $grupoId);
                            $grupoActual = reset($grupoActual);
                        ?>
                        <input type="hidden" name="grupo_id" value="<?= $grupoId ?>">
                        <input type="text" class="form-control" value="<?= esc($grupoActual['nombre'] ?? '') ?>" disabled>
                        <?php else: ?>
                        <select class="form-select" id="grupo_id" name="grupo_id" required>
                            <option value="">Seleccionar grupo</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= ($grupoId ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Pagador (quien entrega el dinero)</label>
                        <?php if (isset($pago) && $rol !== 'admin'): ?>
                            <?php
                                $pagadorActual = current(array_filter($miembros ?? [], fn($m) => (int) $m['user_id'] === (int) $pago['pagador_id']));
                            ?>
                            <input type="hidden" name="pagador_id" value="<?= $pago['pagador_id'] ?>">
                            <input type="text" class="form-control" value="<?= esc($pagadorActual['name'] ?? 'Vos') ?>" disabled>
                        <?php elseif (isset($pago) && $rol === 'admin'): ?>
                            <select class="form-select" id="pagador_id" name="pagador_id" required>
                                <option value="">Seleccionar pagador</option>
                                <?php if (isset($miembros)): ?>
                                    <?php foreach ($miembros as $m): ?>
                                        <option value="<?= $m['user_id'] ?>" <?= old('pagador_id', $pago['pagador_id'] ?? '') == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control" value="Vos" disabled>
                            <small class="text-muted">El pago se registra a tu nombre como pagador.</small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label for="receptor_id" class="form-label fw-medium">Receptor (quien recibe el dinero)</label>
                        <select class="form-select" id="receptor_id" name="receptor_id" required>
                            <option value="">Seleccionar receptor</option>
                            <?php if (isset($miembros)): ?>
                                <?php foreach ($miembros as $m): ?>
                                    <option value="<?= $m['user_id'] ?>" <?= old('receptor_id', $pago['receptor_id'] ?? $prefill['receptor_id'] ?? '') == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= !empty($grupoId) ? base_url('grupos/' . $grupoId) : base_url('pagos') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($pago) ? 'Guardar Cambios' : 'Registrar Pago' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function formatearMonto(input) {
            var limpio = input.value.replace(/[^0-9,]/g, '');
            var partes = limpio.split(',');
            var entero = partes[0].replace(/\D/g, '');
            var decimal = partes.length > 1 ? partes.slice(1).join('').replace(/\D/g, '').slice(0, 2) : '';
            var formateado = entero.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            if (partes.length > 1) {
                formateado += ',' + decimal;
            }
            if (input.value !== formateado) {
                input.value = formateado;
            }
            var numStr = entero + '.' + (decimal || '0');
            var num = parseFloat(numStr);
            if (!isNaN(num)) {
                document.getElementById('monto_real').value = num.toFixed(2);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var vis = document.getElementById('monto_visual');
            var real = document.getElementById('monto_real');
            if (vis && real && vis.value) { formatearMonto(vis); }
        });
    </script>

    <?php if (!isset($pago) && empty($grupoId)): ?>
    <script>
        document.getElementById('grupo_id').addEventListener('change', function() {
            if (this.value) {
                var desc = encodeURIComponent(document.getElementById('descripcion').value);
                var montoReal = document.getElementById('monto_real');
                var monto = encodeURIComponent(montoReal ? montoReal.value : '');
                var fecha = encodeURIComponent(document.getElementById('fecha').value);
                var receptor = encodeURIComponent(document.getElementById('receptor_id').value);
                window.location.href = '<?= base_url('pagos/nuevo?grupo_id=') ?>' + this.value
                    + '&descripcion=' + desc
                    + '&monto=' + monto
                    + '&fecha=' + fecha
                    + '&receptor_id=' + receptor;
            }
        });
    </script>
    <?php endif; ?>
<?= view('partials/_footer') ?>
