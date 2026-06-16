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
                <form action="<?= isset($gasto) ? base_url('gastos/' . $gasto['id']) : base_url('gastos') ?>" method="post" enctype="multipart/form-data">
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
                               oninput="formatearMonto(this); recalcularDivision();">
                        <input type="hidden" name="monto" id="monto_real" value="<?= esc(old('monto', $gasto['monto'] ?? '')) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="fecha" class="form-label fw-medium">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha"
                               value="<?= esc(old('fecha', $gasto['fecha'] ?? date('Y-m-d'))) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="nota" class="form-label fw-medium">Nota <small class="text-muted">(opcional)</small></label>
                        <textarea class="form-control" id="nota" name="nota" rows="2"><?= esc(old('nota', $gasto['nota'] ?? '')) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="recibo" class="form-label fw-medium">Comprobante <small class="text-muted">(opcional, JPG/PNG/WebP/PDF, m&aacute;x 5MB)</small></label>
                        <input type="file" class="form-control" id="recibo" name="recibo" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        <?php if (isset($gasto) && !empty($gasto['recibo_nombre'])): ?>
                            <div class="mt-1 small">
                                Archivo actual: <?= esc($gasto['recibo_nombre']) ?>
                                <a href="<?= base_url('gastos/' . $gasto['id'] . '/recibo') ?>" class="text-primary ms-2" target="_blank">Ver</a>
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="if(confirm('Eliminar recibo?')){fetch('<?= base_url('gastos/' . $gasto['id'] . '/recibo') ?>',{method:'DELETE',headers:{'X-CSRF-TOKEN':'<?= csrf_hash() ?>'}}).then(()=>location.reload())}">Eliminar</button>
                            </div>
                        <?php endif; ?>
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
                    <input type="hidden" name="categoria_id" id="categoria_id" value="<?= old('categoria_id', $gasto['categoria_id'] ?? '') ?>">

                    <!-- SECCION UNIFICADA DE DIVISION -->
                    <div id="divisionSection" class="card border-0 shadow-sm mb-4 <?= !isset($miembros) ? 'd-none' : '' ?>">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold">Divisi&oacute;n del gasto</h5>
                        </div>
                        <div class="card-body">
                            <!-- Pagador -->
                            <div class="mb-3">
                                <label class="form-label fw-medium">Pagado por</label>
                                <?php if (isset($miembros) && count($miembros) > 0): ?>
                                <select class="form-select" id="pagador_id" name="pagador_id" required>
                                    <option value="">Seleccionar</option>
                                    <?php
                                        $pagadorDefault = old('pagador_id', $gasto['pagador_id'] ?? session()->get('userId'));
                                    ?>
                                    <?php foreach ($miembros as $m): ?>
                                        <option value="<?= $m['user_id'] ?>" <?= $pagadorDefault == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php else: ?>
                                <input type="text" class="form-control" value="Vos" disabled>
                                <input type="hidden" name="pagador_id" value="<?= $pagadorDefault ?? session()->get('userId') ?>">
                                <?php endif; ?>
                            </div>

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
                                        <div class="d-flex align-items-center gap-2 mb-2 participante-div-row" data-uid="<?= $uid ?>">
                                            <div class="form-check flex-shrink-0" style="min-width:36px">
                                                <input class="form-check-input participante-checkbox" type="checkbox"
                                                       name="participantes[]"
                                                       value="<?= $uid ?>"
                                                       id="participante_<?= $uid ?>"
                                                       <?= isset($participantesIds) && in_array($uid, $participantesIds) ? 'checked' : '' ?>
                                                       onchange="recalcularDivision()">
                                            </div>
                                            <label class="form-check-label flex-shrink-0" for="participante_<?= $uid ?>" style="min-width:100px;font-size:14px">
                                                <?= esc($m['name']) ?>
                                            </label>
                                            <input type="text" class="form-control form-control-sm division-valor flex-shrink-0"
                                                   data-uid="<?= $uid ?>"
                                                   value="<?= isset($divValores[$uid]) ? esc((string)$divValores[$uid]) : '' ?>"
                                                   inputmode="decimal"
                                                   oninput="recalcularDivision()"
                                                   style="max-width:130px;<?= $modoActual === 'igualitario' ? 'display:none' : '' ?>"
                                                   placeholder="Valor">
                                            <span class="division-monto small text-muted" style="min-width:80px">$0,00</span>
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

                            <div id="divisionValoresContainer"></div>
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
            recalcularDivision();
        }

        function recalcularDivision() {
            var modo = document.getElementById('divisionModo').value;
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
                return;
            }
            if (seleccionados === 0) {
                resultado.innerHTML = 'Seleccion\u00e1 al menos un participante para dividir el gasto.';
                error.classList.add('d-none');
                actualizarMontosCalculados(modo, montoTotal, rows, []);
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

            // Generar hidden inputs for form submission
            var container = document.getElementById('divisionValoresContainer');
            container.innerHTML = '';
            var index = 0;
            rows.forEach(function(row) {
                var cb = row.querySelector('.participante-checkbox');
                if (cb && cb.checked) {
                    var uid = row.dataset.uid;
                    var inp = row.querySelector('.division-valor');
                    var v = (inp.value || '0').replace(',', '.');
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

            var modo = document.getElementById('divisionModo').value;
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
                window.location.href = '<?= base_url('gastos/nuevo?grupo_id=') ?>' + this.value;
            }
        });
    </script>
    <?php endif; ?>
<?= view('partials/_footer') ?>
