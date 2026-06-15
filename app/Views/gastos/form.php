<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($gasto) ? 'Editar' : 'Nuevo') . ' Gasto']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4"><?= isset($gasto) ? 'Editar Gasto' : 'Nuevo Gasto' ?></h2>

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
                <form action="<?= isset($gasto) ? base_url('gastos/' . $gasto['id']) : base_url('gastos') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if (isset($gasto)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-medium">Descripci&oacute;n</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion"
                               value="<?= esc(old('descripcion', $gasto['descripcion'] ?? '')) ?>" required
                               oninput="inferirCategoria(this.value)">
                        <div id="categoriaSugerida" class="mt-1 small text-muted d-none"></div>
                    </div>

                    <div class="mb-3">
                        <label for="monto" class="form-label fw-medium">Monto total</label>
                        <input type="text" class="form-control" id="monto" name="monto_visual"
                               value="<?= esc(old('monto_visual', isset($gasto) ? number_format($gasto['monto'], 2, ',', '.') : '')) ?>" required
                               inputmode="numeric"
                               oninput="formatearMonto(this)">
                        <input type="hidden" name="monto" id="monto_real" value="<?= esc(old('monto', $gasto['monto'] ?? '')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="fecha" class="form-label fw-medium">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha"
                               value="<?= esc(old('fecha', $gasto['fecha'] ?? date('Y-m-d'))) ?>" required>
                    </div>

                    <!-- M&aacute;s opciones (colapsable en mobile) -->
                    <div class="d-md-none mb-3">
                        <button class="btn btn-outline-secondary btn-sm w-100 text-start" type="button" data-bs-toggle="collapse" data-bs-target="#masOpcionesGasto" aria-expanded="false">
                            <span class="fw-medium">M&aacute;s opciones</span>
                            <span class="float-end">+</span>
                        </button>
                    </div>
                    <div class="collapse d-md-block" id="masOpcionesGasto">

                    <div class="mb-3">
                        <label for="grupo_id" class="form-label fw-medium">Grupo</label>
                        <?php if (!empty($grupoId) && !isset($gasto)): ?>
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
                        <label class="form-label fw-medium">Pagador</label>
                        <?php if (isset($gasto) && $rol !== 'admin'): ?>
                            <?php
                                $pagadorActual = current(array_filter($miembros ?? [], fn($m) => (int) $m['user_id'] === (int) $gasto['pagador_id']));
                            ?>
                            <input type="hidden" name="pagador_id" value="<?= $gasto['pagador_id'] ?>">
                            <input type="text" class="form-control" value="<?= esc($pagadorActual['name'] ?? 'Vos') ?>" disabled>
                        <?php elseif (isset($gasto) && $rol === 'admin'): ?>
                            <select class="form-select" id="pagador_id" name="pagador_id" required>
                                <option value="">Seleccionar pagador</option>
                                <?php if (isset($miembros)): ?>
                                    <?php foreach ($miembros as $m): ?>
                                        <option value="<?= $m['user_id'] ?>" <?= old('pagador_id', $gasto['pagador_id'] ?? '') == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" class="form-control" value="Vos" disabled>
                            <small class="text-muted">El gasto se registra a tu nombre como pagador.</small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="categoria_id" class="form-label fw-medium">Categoría</label>
                        <select class="form-select" id="categoria_id" name="categoria_id">
                            <option value="">Sin categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= old('categoria_id', $gasto['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Participantes (división igualitaria)</label>
                        <?php if (isset($miembros)): ?>
                            <?php foreach ($miembros as $m): ?>
                                <div class="form-check py-1">
                                    <input class="form-check-input participante-checkbox" type="checkbox" name="participantes[]"
                                           value="<?= $m['user_id'] ?>"
                                           id="participante_<?= $m['user_id'] ?>"
                                           <?= isset($participantesIds) && in_array($m['user_id'], $participantesIds) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="participante_<?= $m['user_id'] ?>">
                                        <?= esc($m['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Seleccioná un grupo primero.</p>
                        <?php endif; ?>
                    </div><!-- /.collapse #masOpcionesGasto -->

                    <!-- Preview de división en tiempo real -->
                    <div id="divisionPreview" class="card border-0 shadow-sm mb-4 <?= !isset($miembros) ? 'd-none' : '' ?>">
                        <div class="card-body py-3" id="divisionPreviewContent">
                            <p class="text-muted small mb-0">Ingresá un monto y seleccioná participantes para ver cómo se divide.</p>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= !empty($grupoId) ? base_url('grupos/' . $grupoId) : base_url('gastos') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($gasto) ? 'Guardar Cambios' : 'Crear Gasto' ?>
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
        var diccionarioCategorias = {
            'super': 'Supermercado', 'mercado': 'Supermercado', 'supermercado': 'Supermercado',
            'verduleria': 'Supermercado', 'almacen': 'Supermercado', 'compras': 'Supermercado',
            'nafta': 'Combustible', 'combustible': 'Combustible', 'gasolina': 'Combustible',
            'estacionamiento': 'Combustible',
            'farmacia': 'Farmacia', 'remedio': 'Farmacia', 'medicamento': 'Farmacia',
            'restaurant': 'Comida', 'comida': 'Comida', 'cena': 'Comida', 'almuerzo': 'Comida',
            'delivery': 'Comida', 'uber': 'Transporte', 'taxi': 'Transporte',
            'colectivo': 'Transporte', 'viaje': 'Transporte',
            'luz': 'Servicios', 'agua': 'Servicios', 'gas': 'Servicios', 'internet': 'Servicios',
            'alquiler': 'Vivienda', 'expensas': 'Vivienda',
            'cine': 'Entretenimiento', 'netflix': 'Entretenimiento', 'streaming': 'Entretenimiento',
        };

        function inferirCategoria(texto) {
            var lower = texto.toLowerCase().trim();
            var sugerido = '';
            for (var key in diccionarioCategorias) {
                if (lower.includes(key)) { sugerido = diccionarioCategorias[key]; break; }
            }
            var el = document.getElementById('categoriaSugerida');
            var select = document.getElementById('categoria_id');
            if (sugerido && select) {
                var match = false;
                for (var i = 0; i < select.options.length; i++) {
                    if (select.options[i].text.toLowerCase() === sugerido.toLowerCase()) {
                        select.value = select.options[i].value;
                        match = true;
                        break;
                    }
                }
                if (match) {
                    el.textContent = 'Categor\u00eda sugerida: ' + sugerido;
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            } else if (el) {
                el.classList.add('d-none');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            var montoInput = document.getElementById('monto');
            if (!montoInput) return;

            var checkboxes = document.querySelectorAll('.participante-checkbox');
            var previewContent = document.getElementById('divisionPreviewContent');

            function actualizarPreview() {
                var rawValue = document.getElementById('monto_real').value;
                var monto = parseFloat(rawValue);
                var seleccionados = 0;
                checkboxes.forEach(function(cb) {
                    if (cb.checked) seleccionados++;
                });

                if (!rawValue || isNaN(monto) || monto <= 0) {
                    previewContent.innerHTML = '<p class="text-muted small mb-0">Ingresá un monto v&aacute;lido para ver la divisi&oacute;n.</p>';
                    return;
                }
                if (seleccionados === 0) {
                    previewContent.innerHTML = '<p class="text-muted small mb-0">Seleccion&aacute; al menos un participante para dividir el gasto.</p>';
                    return;
                }

                // Misma logica que el backend: round, diferencias, ultimo participante ajustado
                var porcion = Math.round(monto / seleccionados * 100) / 100;
                var diferencias = Math.round((monto - porcion * seleccionados) * 100) / 100;

                var html =
                    '<div class="d-flex justify-content-between small mb-1">' +
                        '<span class="text-muted">Participantes:</span>' +
                        '<span class="fw-medium">' + seleccionados + '</span>' +
                    '</div>' +
                    '<div class="d-flex justify-content-between small mb-1">' +
                        '<span class="text-muted">Cada uno paga:</span>' +
                        '<span class="fw-medium">$' + porcion.toFixed(2) + '</span>' +
                    '</div>';

                if (diferencias !== 0) {
                    html +=
                    '<div class="d-flex justify-content-between small mb-1 text-warning">' +
                        '<span>Ajuste en el &uacute;ltimo participante:</span>' +
                        '<span class="fw-medium">$' + diferencias.toFixed(2) + '</span>' +
                    '</div>';
                }

                html +=
                    '<div class="d-flex justify-content-between small fw-bold pt-1 border-top">' +
                        '<span>Total:</span>' +
                        '<span>$' + monto.toFixed(2) + '</span>' +
                    '</div>';

                previewContent.innerHTML = html;
            }

            montoInput.addEventListener('input', actualizarPreview);
            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', actualizarPreview);
            });

        // Inicializar monto_real si ya hay valor
        (function() {
            var vis = document.getElementById('monto');
            var real = document.getElementById('monto_real');
            if (vis && real && vis.value) { formatearMonto(vis); }
        })();

        actualizarPreview();
        });
    </script>

    <?php if (!isset($gasto) && empty($grupoId)): ?>
    <script>
        document.getElementById('grupo_id').addEventListener('change', function() {
            if (this.value) {
                window.location.href = '<?= base_url('gastos/nuevo?grupo_id=') ?>' + this.value;
            }
        });
    </script>
    <?php endif; ?>
<?= view('partials/_footer') ?>
