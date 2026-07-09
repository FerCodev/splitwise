<?= view('partials/_head', ['title' => 'Gastito - ' . (isset($gasto) ? 'Editar' : 'Nuevo') . ' gastito']) ?>
<?= view('partials/_navbar', ['pageTitle' => isset($gasto) ? 'Editar gastito' : 'Nuevo gastito']) ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4 d-none d-md-block"><?= isset($gasto) ? 'Editar gastito' : 'Nuevo gastito' ?></h2>

    <?php $prefill = $prefill ?? []; ?>
        <?= view('partials/_validation_errors') ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="<?= isset($gasto) ? base_url('gastos/' . $gasto['id']) : base_url('gastos') ?>" method="post" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <?php if (isset($gasto)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="monto" class="form-label fw-medium">Monto total</label>
                        <input type="text" class="form-control" id="monto" name="monto_visual"
                               value="<?= esc(old('monto_visual', isset($gasto) ? numero_arg($gasto['monto']) : ($prefill['monto'] !== '' && $prefill['monto'] !== null ? numero_arg((float) $prefill['monto']) : ''))) ?>" required
                               inputmode="numeric"
                               oninput="formatearMonto(this); recalcularDivision();">
                        <input type="hidden" name="monto" id="monto_real" value="<?= esc(old('monto', $gasto['monto'] ?? $prefill['monto'] ?? '')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-medium">Descripci&oacute;n <small class="text-muted">(opcional)</small></label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion"
                               value="<?= esc(old('descripcion', $gasto['descripcion'] ?? $prefill['descripcion'] ?? '')) ?>"
                               oninput="inferirCategoria(this.value)">
                        <div id="categoriaSugerida" class="mt-1 small text-muted d-none"></div>
                    </div>

                    <?php $mostrarMasAcciones = isset($gasto) || old('fecha') || old('nota') || ($prefill['fecha'] ?? '') !== ''; ?>
                    <div class="mb-3">
                        <button class="btn btn-secondary w-100" type="button" data-bs-toggle="collapse" data-bs-target="#masAccionesGasto" aria-expanded="<?= $mostrarMasAcciones ? 'true' : 'false' ?>" aria-controls="masAccionesGasto">
                            M&aacute;s acciones
                        </button>
                    </div>

                    <div class="collapse <?= $mostrarMasAcciones ? 'show' : '' ?>" id="masAccionesGasto">
                        <div class="more-actions-panel mb-3">
                            <div class="mb-3">
                                <label for="fecha" class="form-label fw-medium">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha"
                                       value="<?= esc(old('fecha', $gasto['fecha'] ?? $prefill['fecha'] ?? date('Y-m-d'))) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="nota" class="form-label fw-medium">Nota <small class="text-muted">(opcional)</small></label>
                                <textarea class="form-control" id="nota" name="nota" rows="2"><?= esc(old('nota', $gasto['nota'] ?? '')) ?></textarea>
                            </div>

                            <div>
                                <label for="recibo" class="form-label fw-medium">Comprobante <small class="text-muted">(opcional, JPG/PNG/WebP/PDF, m&aacute;x 5MB)</small></label>
                                <input type="file" class="form-control" id="recibo" name="recibo" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                <?php if (isset($gasto) && !empty($gasto['recibo_nombre'])): ?>
                                    <div class="mt-1 small" id="recibo-actual">
                                        Archivo actual: <?= esc($gasto['recibo_nombre']) ?>
                                        <a href="<?= base_url('gastos/' . $gasto['id'] . '/recibo') ?>" class="text-primary ms-2" target="_blank">Ver</a>
                                        <button type="button" class="btn btn-sm btn-danger ms-2"
                                            data-bs-toggle="modal" data-bs-target="#confirmModal"
                                            data-confirm-title="Eliminar recibo"
                                            data-confirm-msg="Se eliminará el comprobante adjunto a este gastito."
                                            data-confirm-btn="Eliminar recibo"
                                            data-confirm-class="btn-danger"
                                            data-confirm-action="delete-receipt"
                                            data-receipt-url="<?= base_url('gastos/' . $gasto['id'] . '/recibo') ?>"
                                            data-csrf-token="<?= csrf_hash() ?>">Eliminar</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Grupo -->
                    <?php if (!empty($grupoId)): ?>
                        <input type="hidden" name="grupo_id" value="<?= $grupoId ?>">
                    <?php else: ?>
                    <div class="mb-3">
                        <label for="grupo_id" class="form-label fw-medium">Grupo</label>
                        <select class="form-select" id="grupo_id" name="grupo_id" required>
                            <option value="">Seleccionar grupo</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= ($grupoId ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <!-- Categoria (oculta, inferida desde descripcion) -->
                    <input type="hidden" name="categoria_id" id="categoria_id" value="<?= old('categoria_id', $gasto['categoria_id'] ?? $prefill['categoria_id'] ?? '') ?>">
                    <input type="hidden" name="division_tipo" id="divisionTipoHidden" value="<?= esc(old('division_tipo', $gasto['division_tipo'] ?? 'igualitario')) ?>">
                    <div id="participantesHiddenContainer"></div>
                    <div id="divisionValoresContainer"></div>

                    <!-- Pagador -->
                    <?php
                        $usuarioActualId = (int) session()->get('userId');
                        $pagadorDefault = old('pagador_id', $gasto['pagador_id'] ?? $usuarioActualId);
                        $otroMiembro = null;
                        if (isset($miembros)) {
                            foreach ($miembros as $m) {
                                if ((int) $m['user_id'] !== $usuarioActualId) {
                                    $otroMiembro = $m;
                                    break;
                                }
                            }
                        }
                    ?>
                    <?php
                        $pagadorBloqueado = isset($gasto) && $rol !== 'admin';
                        $pagadorNombre = 'Vos';
                        if (isset($miembros)) {
                            foreach ($miembros as $m) {
                                if ((int) $m['user_id'] === (int) $pagadorDefault) {
                                    $pagadorNombre = $m['name'];
                                    break;
                                }
                            }
                        }
                    ?>
                    <div class="mb-3 <?= !isset($miembros) || count($miembros) === 0 ? 'd-none' : '' ?>">
                        <div class="expense-payer-card <?= !$pagadorBloqueado && isset($miembros) && count($miembros) > 0 ? 'expense-payer-card-selectable' : '' ?>">
                            <div class="expense-payer-main">
                                <span class="expense-payer-label">Pagado por</span>
                                <span class="expense-payer-name" id="pagadorLabel"><?= esc($pagadorNombre) ?></span>
                            </div>
                            <?php if (!$pagadorBloqueado && isset($miembros) && count($miembros) > 0): ?>
                                <span class="expense-payer-change" aria-hidden="true">Cambiar</span>
                                <select class="expense-payer-native-select" id="pagadorSelect" name="pagador_id" required aria-label="Pagado por" onchange="pagadorSeleccionado(this)">
                                    <option value="">Seleccionar</option>
                                    <?php foreach ($miembros as $m): ?>
                                        <option value="<?= $m['user_id'] ?>" <?= $pagadorDefault == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                        <?php if ($pagadorBloqueado): ?>
                            <input type="hidden" name="pagador_id" value="<?= $pagadorDefault ?>">
                        <?php endif; ?>
                    </div>

                    <!-- SECCION DIVISION (resumen compacto) -->
                    <div id="divisionSection" class="mb-4 <?= !isset($miembros) || count($miembros) === 0 ? 'd-none' : '' ?>">
                        <label class="form-label fw-medium">Divisi&oacute;n del gastito</label>
                        <button type="button" class="btn btn-secondary w-100 text-start py-3 px-3 division-summary d-flex align-items-center" id="divisionSummaryBtn" data-bs-toggle="modal" data-bs-target="#divisionPresetModal">
                            <span class="division-summary-title flex-grow-1">Por defecto, dividido en partes iguales.</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="flex-shrink-0 ms-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>
                        </button>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= !empty($grupoId) ? base_url('grupos/' . $grupoId) : base_url('gastos') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($gasto) ? 'Guardar Cambios' : 'Crear gastito' ?>
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
            var hidden = document.getElementById('categoria_id');
            if (sugerido) {
                var valueMap = {};
                <?php foreach ($categorias as $cat): ?>
                valueMap['<?= strtolower(esc($cat['nombre'])) ?>'] = '<?= $cat['id'] ?>';
                <?php endforeach; ?>
                if (valueMap[sugerido.toLowerCase()]) {
                    hidden.value = valueMap[sugerido.toLowerCase()];
                    el.textContent = 'Categor\u00eda: ' + sugerido;
                    el.classList.remove('d-none');
                } else {
                    el.classList.add('d-none');
                }
            } else if (el) {
                el.classList.add('d-none');
            }
        }

        var usuarioActualId = '<?= (int) session()->get('userId') ?>';
        var otroMiembroId = '<?= $otroMiembro['user_id'] ?? '' ?>';
        var pagadorBloqueado = <?= (isset($gasto) && $rol !== 'admin') ? 'true' : 'false' ?>;

        function setPagador(uid) {
            var pagador = document.getElementById('pagadorSelect');
            if (!pagador || !uid) return;
            var option = pagador.querySelector('option[value="' + uid + '"]');
            if (option) {
                pagador.value = uid;
            }
            actualizarLabelPagador();
        }

        function actualizarLabelPagador() {
            var pagador = document.getElementById('pagadorSelect');
            var label = document.getElementById('pagadorLabel');
            if (!pagador || !label) return;
            var option = pagador.options[pagador.selectedIndex];
            if (option && option.value) {
                label.textContent = option.textContent;
            }
        }

        function pagadorSeleccionado(select) {
            if (!select.value) return;
            actualizarLabelPagador();
            recalcularDivision();
        }

        function setParticipantesPorPreset(preset) {
            var checkboxes = document.querySelectorAll('.participante-checkbox');
            checkboxes.forEach(function(cb) {
                if (preset === 'me_paid_others') {
                    cb.checked = cb.value !== usuarioActualId;
                } else if (preset === 'other_paid_me') {
                    cb.checked = cb.value === usuarioActualId;
                } else {
                    cb.checked = true;
                }
            });

            var alguno = Array.prototype.some.call(checkboxes, function(cb) { return cb.checked; });
            if (!alguno) {
                checkboxes.forEach(function(cb) { cb.checked = true; });
            }
        }

        function marcarPresetActivo(button) {
            document.querySelectorAll('.quick-split-option').forEach(function(btn) {
                btn.classList.remove('active');
            });
            if (button) {
                button.classList.add('active');
            }
        }

        function actualizarResumenDivision(preset) {
            var titulo = document.querySelector('.division-summary-title');
            if (!titulo) return;
            var pagador = document.getElementById('pagadorSelect');
            var pagadorNombre = 'Vos';
            if (pagador && pagador.value) {
                var opt = pagador.querySelector('option[value="' + pagador.value + '"]');
                if (opt) pagadorNombre = opt.textContent;
            }
            var textos = {
                'equal': pagadorNombre + ' pag\u00f3, dividido en partes iguales',
                'me_paid_others': pagadorNombre + ' pag\u00f3 todo',
                'other_paid_equal': pagadorNombre + ' pag\u00f3, dividido en partes iguales',
                'other_paid_me': 'Le deb\u00e9s a ' + pagadorNombre,
            };
            if (textos[preset]) {
                titulo.textContent = textos[preset];
            }
        }

        function aplicarPresetDivision(preset, button) {
            var modo = document.getElementById('divisionModo');
            if (modo) {
                modo.value = 'igualitario';
                cambiarModoDivision('igualitario');
            }

            if (!pagadorBloqueado) {
                if (preset === 'other_paid_equal' || preset === 'other_paid_me') {
                    setPagador(otroMiembroId);
                } else {
                    setPagador(usuarioActualId);
                }
            }

            setParticipantesPorPreset(preset);
            marcarPresetActivo(button);
            recalcularDivision();
            actualizarResumenDivision(preset);

            var modalEl = document.getElementById('divisionPresetModal');
            if (modalEl) {
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
        }

        function cambiarModoDivision(modo) {
            var rows = document.querySelectorAll('.participante-div-row');
            var inputs = document.querySelectorAll('.division-valor');
            var labels = {
                'igualitario': '\u2014',
                'monto_fijo': 'Monto fijo ($)',
                'porcentaje': 'Porcentaje (%)',
            };
            inputs.forEach(function(inp) {
                if (modo === 'igualitario') {
                    inp.value = '';
                    inp.style.display = 'none';
                    inp.placeholder = 'autom\u00e1tico';
                } else {
                    inp.style.display = '';
                    inp.placeholder = labels[modo] || 'Valor';
                }
            });
            if (modo !== 'igualitario') {
                marcarPresetActivo(null);
            }
            recalcularDivision();
        }

        function recalcularDivision() {
            var modoSelect = document.getElementById('divisionModo');
            if (!modoSelect) return;

            var modo = modoSelect.value;
            var tipoHidden = document.getElementById('divisionTipoHidden');
            if (tipoHidden) {
                tipoHidden.value = modo;
            }
            var rawValue = document.getElementById('monto_real').value;
            var montoTotal = parseFloat(rawValue) || 0;
            var rows = document.querySelectorAll('.participante-div-row:has(.participante-checkbox:checked)');
            var resultado = document.getElementById('divisionResultado');
            var error = document.getElementById('divisionError');

            var seleccionados = rows.length;

            if (!rawValue || isNaN(montoTotal) || montoTotal <= 0) {
                resultado.innerHTML = 'Ingres\u00e1 un monto v\u00e1lido para ver la divisi\u00f3n.';
                error.classList.add('d-none');
                actualizarMontosCalculados(modo, montoTotal, rows, []);
                sincronizarDivisionHidden(rows);
                return;
            }
            if (seleccionados === 0) {
                resultado.innerHTML = 'Seleccion\u00e1 al menos un participante para dividir el gastito.';
                error.classList.add('d-none');
                actualizarMontosCalculados(modo, montoTotal, rows, []);
                sincronizarDivisionHidden(rows);
                return;
            }

            var valores = [];
            rows.forEach(function(row) {
                var inp = row.querySelector('.division-valor');
                var cb = row.querySelector('.participante-checkbox');
                if (cb && cb.checked) {
                    var v = parseFloat((inp.value || '0').replace(',', '.')) || 0;
                    valores.push(v);
                }
            });

            var totalValor = valores.reduce(function(a, b) { return a + b; }, 0);
            var errores = [];

            if (modo === 'monto_fijo') {
                var diff = montoTotal - totalValor;
                if (Math.abs(diff) > 0.01) {
                    errores.push(diff > 0 ? 'Faltan $' + diff.toFixed(2) + ' para llegar al total' : 'Excede por $' + Math.abs(diff).toFixed(2));
                }
            } else if (modo === 'porcentaje') {
                if (Math.abs(totalValor - 100) > 0.1) {
                    errores.push('Los porcentajes deben sumar 100% (actual: ' + totalValor.toFixed(1) + '%)');
                }
            }

            if (errores.length > 0) {
                error.innerHTML = errores.join('<br>');
                error.classList.remove('d-none');
                resultado.innerHTML = '';
            } else {
                error.classList.add('d-none');
                if (modo === 'igualitario') {
                    var porcion = montoTotal / seleccionados;
                    resultado.innerHTML = 'Cada uno paga <strong>$' + porcion.toFixed(2) + '</strong>';
                } else if (modo === 'monto_fijo') {
                    resultado.innerHTML = 'Total asignado: <strong>$' + totalValor.toFixed(2) + '</strong>';
                } else if (modo === 'porcentaje') {
                    resultado.innerHTML = 'Porcentajes verificados: <strong>' + totalValor.toFixed(1) + '%</strong>';
                }
            }

            actualizarMontosCalculados(modo, montoTotal, rows, valores);
            sincronizarDivisionHidden(rows);
        }

        function sincronizarDivisionHidden(rows) {
            var participantesContainer = document.getElementById('participantesHiddenContainer');
            var container = document.getElementById('divisionValoresContainer');
            if (!participantesContainer || !container) return;

            participantesContainer.innerHTML = '';
            container.innerHTML = '';

            var index = 0;
            rows.forEach(function(row) {
                var cb = row.querySelector('.participante-checkbox');
                if (cb && cb.checked) {
                    var uid = row.dataset.uid;
                    var inp = row.querySelector('.division-valor');
                    var v = (inp.value || '0').replace(',', '.');
                    participantesContainer.innerHTML += '<input type="hidden" name="participantes[]" value="' + uid + '">';
                    container.innerHTML += '<input type="hidden" name="division_valores[' + index + '][user_id]" value="' + uid + '">';
                    container.innerHTML += '<input type="hidden" name="division_valores[' + index + '][valor]" value="' + v + '">';
                    index++;
                }
            });
        }

        function actualizarMontosCalculados(modo, montoTotal, rows, valores) {
            rows.forEach(function(row, i) {
                var span = row.querySelector('.division-monto');
                var cb = row.querySelector('.participante-checkbox');
                if (!span) return;
                if (!cb || !cb.checked) {
                    span.textContent = '$0,00';
                    return;
                }
                var v = valores[i] || 0;
                var calc = 0;
                if (modo === 'igualitario' && rows.length > 0) {
                    calc = montoTotal / rows.length;
                } else if (modo === 'monto_fijo') {
                    calc = v;
                } else if (modo === 'porcentaje') {
                    calc = montoTotal * v / 100;
                }
                span.textContent = '$' + calc.toFixed(2).replace('.', ',');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var montoInput = document.getElementById('monto');
            if (!montoInput) return;

            var modoSelect = document.getElementById('divisionModo');
            if (!modoSelect) return;

            var modo = modoSelect.value;
            cambiarModoDivision(modo);
            recalcularDivision();

            var checkboxes = document.querySelectorAll('.participante-checkbox');
            checkboxes.forEach(function(cb) {
                cb.addEventListener('change', recalcularDivision);
            });

            (function() {
                var vis = document.getElementById('monto');
                var real = document.getElementById('monto_real');
                if (vis && real && vis.value) { formatearMonto(vis); }
            })();
            recalcularDivision();
        });
    </script>

    <?php if (!isset($gasto) && empty($grupoId)): ?>
    <script>
        document.getElementById('grupo_id').addEventListener('change', function() {
            if (this.value) {
                var montoReal = document.getElementById('monto_real');
                var monto = montoReal ? montoReal.value : '';
                var desc = encodeURIComponent(document.getElementById('descripcion').value);
                var fecha = encodeURIComponent(document.getElementById('fecha').value);
                var categoria = encodeURIComponent(document.getElementById('categoria_id').value);
                window.location.href = '<?= base_url('gastos/nuevo?grupo_id=') ?>' + this.value
                    + '&descripcion=' + desc
                    + '&monto=' + monto
                    + '&fecha=' + fecha
                    + '&categoria_id=' + categoria;
            }
        });
    </script>
    <?php endif; ?>
<div class="modal fade" id="divisionPresetModal" tabindex="-1" aria-labelledby="divisionPresetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-md-down modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="divisionPresetModalLabel">&iquest;C&oacute;mo se dividi&oacute; este gastito?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-3">
                <?php if (isset($miembros) && count($miembros) > 0): ?>
                    <!-- Presets rapidos -->
                    <div class="list-group mb-3">
                        <button type="button" class="list-group-item list-group-item-action quick-split-option active" data-preset="equal" onclick="aplicarPresetDivision('equal', this)">
                            <span class="d-block fw-semibold">Partes iguales</span>
                            <span class="d-block small text-muted">Todos participan del gastito.</span>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action quick-split-option" data-preset="me_paid_others" onclick="aplicarPresetDivision('me_paid_others', this)">
                            <span class="d-block fw-semibold">Yo pagu&eacute;, me deben</span>
                            <span class="d-block small text-muted">Solo los dem&aacute;s consumieron.</span>
                        </button>
                        <?php if ($otroMiembro && !$pagadorBloqueado): ?>
                            <button type="button" class="list-group-item list-group-item-action quick-split-option" data-preset="other_paid_equal" onclick="aplicarPresetDivision('other_paid_equal', this)">
                                <span class="d-block fw-semibold"><?= esc($otroMiembro['name']) ?> pag&oacute;</span>
                                <span class="d-block small text-muted">Dividido en partes iguales.</span>
                            </button>
                            <button type="button" class="list-group-item list-group-item-action quick-split-option" data-preset="other_paid_me" onclick="aplicarPresetDivision('other_paid_me', this)">
                                <span class="d-block fw-semibold">Le debo a <?= esc($otroMiembro['name']) ?></span>
                                <span class="d-block small text-muted">Solo vos consumiste.</span>
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Mas opciones de division -->
                    <?php $mostrarOpcionesDivision = isset($gasto) && old('division_tipo', $gasto['division_tipo'] ?? 'igualitario') !== 'igualitario'; ?>
                    <button class="btn btn-secondary w-100 mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#opcionesDivisionAvanzadas" aria-expanded="<?= $mostrarOpcionesDivision ? 'true' : 'false' ?>" aria-controls="opcionesDivisionAvanzadas">
                        M&aacute;s opciones de divisi&oacute;n
                    </button>

                    <div class="collapse <?= $mostrarOpcionesDivision ? 'show' : '' ?>" id="opcionesDivisionAvanzadas">
                        <!-- Modo de division -->
                        <div class="mb-3">
                            <label class="form-label fw-medium">Modalidad</label>
                            <select class="form-select" id="divisionModo" name="division_tipo" onchange="cambiarModoDivision(this.value)">
                                <option value="igualitario" <?= old('division_tipo', $gasto['division_tipo'] ?? 'igualitario') === 'igualitario' ? 'selected' : '' ?>>Partes iguales</option>
                                <option value="monto_fijo" <?= old('division_tipo', $gasto['division_tipo'] ?? '') === 'monto_fijo' ? 'selected' : '' ?>>Monto fijo</option>
                                <option value="porcentaje" <?= old('division_tipo', $gasto['division_tipo'] ?? '') === 'porcentaje' ? 'selected' : '' ?>>Porcentaje</option>
                            </select>
                        </div>

                        <!-- Participantes -->
                        <div class="mb-2">
                            <label class="form-label fw-medium">Participantes</label>
                            <div id="participantesDivision">
                                <?php if (isset($miembros)): ?>
                                    <?php
                                        $modoActual = old('division_tipo', $gasto['division_tipo'] ?? 'igualitario');
                                        $divValores = $divisionValoresExistentes ?? [];
                                    ?>
                                    <?php foreach ($miembros as $i => $m): $uid = $m['user_id']; ?>
                                    <div class="division-participant-row participante-div-row" data-uid="<?= $uid ?>">
                                        <input class="form-check-input participante-checkbox" type="checkbox"
                                               name="participantes[]"
                                               value="<?= $uid ?>"
                                               id="participante_<?= $uid ?>"
                                               <?= isset($participantesIds) && in_array($uid, $participantesIds) ? 'checked' : '' ?>
                                               onchange="recalcularDivision()">
                                        <label class="division-participant-name" for="participante_<?= $uid ?>">
                                            <?= esc($m['name']) ?>
                                        </label>
                                        <input type="text" class="form-control form-control-sm division-valor"
                                               data-uid="<?= $uid ?>"
                                               value="<?= isset($divValores[$uid]) ? esc((string)$divValores[$uid]) : '' ?>"
                                               inputmode="decimal"
                                               oninput="recalcularDivision()"
                                               style="<?= $modoActual === 'igualitario' ? 'display:none' : '' ?>"
                                               placeholder="Valor">
                                        <span class="division-monto small text-muted">$0,00</span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted small mb-0">Seleccion&aacute; un grupo primero.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Resultado / Errores -->
                        <div id="divisionResultado" class="small text-muted mb-1">Ingres&aacute; un monto y seleccion&aacute; participantes para ver la divisi&oacute;n.</div>
                        <div id="divisionError" class="small text-danger mb-1 d-none"></div>
                    </div>

                <?php else: ?>
                    <p class="text-muted mb-0">Seleccion&aacute; un grupo primero.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">Listo</button>
            </div>
        </div>
    </div>
</div>
<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var modal = document.getElementById('confirmModal');
        if (!modal) return;

        modal.addEventListener('gastito:confirm-action', function(event) {
            if (!event.detail || event.detail.action !== 'delete-receipt') return;

            var trigger = event.detail.trigger;
            fetch(trigger.getAttribute('data-receipt-url'), {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': trigger.getAttribute('data-csrf-token') || ''
                }
            }).then(function(response) {
                return response.json().catch(function() {
                    return { ok: response.ok, message: response.ok ? 'Recibo eliminado correctamente.' : 'No se pudo eliminar el recibo.' };
                }).then(function(payload) {
                    if (payload.csrf) {
                        trigger.setAttribute('data-csrf-token', payload.csrf);
                    }
                    if (!response.ok || !payload.ok) {
                        throw new Error(payload.message || 'No se pudo eliminar el recibo.');
                    }
                    var reciboActual = document.getElementById('recibo-actual');
                    if (reciboActual) reciboActual.remove();
                    GastitoFeedback.show('success', payload.message || 'Recibo eliminado correctamente.');
                });
            }).catch(function() {
                GastitoFeedback.show('error', 'No se pudo eliminar el recibo. Intentá nuevamente.');
            });
        });
    });
})();
</script>
<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
