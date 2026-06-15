<?php
$participantes = $participantes ?? $miembros ?? [];
?>
<div class="modal fade" id="divisionModal" tabindex="-1" aria-labelledby="divisionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="divisionModalLabel">C&oacute;mo se divide este gasto?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-medium">Modo de divisi&oacute;n</label>
                    <select class="form-select" id="divisionModo" onchange="cambiarModoDivision(this.value)">
                        <option value="igualitario">Partes iguales</option>
                        <option value="monto_fijo">Monto fijo</option>
                        <option value="porcentaje">Porcentaje</option>
                        <option value="partes">Cuotas / partes</option>
                        <option value="ajuste">Ajuste</option>
                    </select>
                </div>
                <div id="divisionResultado" class="small text-muted mb-2">Cargando...</div>
                <div id="divisionError" class="small text-danger mb-2 d-none"></div>
                <hr>
                <div id="participantesDivision">
                    <?php foreach ($participantes as $p): $uid = $p['user_id'] ?? $p['id']; ?>
                    <div class="d-flex align-items-center gap-2 mb-2 participante-div-row" data-uid="<?= $uid ?>">
                        <span class="flex-shrink-0" style="width:120px;font-size:14px"><?= esc($p['name']) ?></span>
                        <input type="text" class="form-control form-control-sm division-valor" data-uid="<?= $uid ?>" value="" inputmode="decimal" oninput="recalcularDivision()" style="max-width:140px">
                        <span class="division-monto small text-muted" style="min-width:80px">$0,00</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="divisionAplicar" onclick="aplicarDivision()">Aplicar</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="division_tipo" id="divisionTipoInput" value="igualitario">
<div id="divisionValoresContainer"></div>
<script>
function cambiarModoDivision(modo) {
    document.getElementById('divisionTipoInput').value = modo;
    var rows = document.querySelectorAll('.participante-div-row');
    var inputs = document.querySelectorAll('.division-valor');
    var labels = {
        'igualitario': '&mdash;',
        'monto_fijo': 'Monto fijo ($)',
        'porcentaje': 'Porcentaje (%)',
        'partes': 'Partes',
        'ajuste': 'Ajuste ($)',
    };
    inputs.forEach(function(inp) {
        if (modo === 'igualitario') {
            inp.value = '';
            inp.disabled = true;
            inp.placeholder = 'autom&aacute;tico';
        } else {
            inp.disabled = false;
            inp.placeholder = labels[modo] || 'Valor';
        }
    });
    recalcularDivision();
}

function recalcularDivision() {
    var modo = document.getElementById('divisionTipoInput').value;
    var montoTotal = parseFloat(document.getElementById('monto_real')?.value) || 0;
    var rows = document.querySelectorAll('.participante-div-row');
    var resultado = document.getElementById('divisionResultado');
    var error = document.getElementById('divisionError');

    if (modo === 'igualitario' || rows.length === 0) {
        if (rows.length > 0 && montoTotal > 0) {
            var porcion = montoTotal / rows.length;
            resultado.innerHTML = 'Cada uno paga <strong>$' + porcion.toFixed(2) + '</strong>';
        } else {
            resultado.innerHTML = 'Ingres&aacute; un monto total v&aacute;lido.';
        }
        error.classList.add('d-none');
        return;
    }

    var valores = [];
    rows.forEach(function(row) {
        var inp = row.querySelector('.division-valor');
        var v = parseFloat(inp.value.replace(',', '.')) || 0;
        valores.push(v);
    });

    var totalValor = valores.reduce(function(a, b) { return a + b; }, 0);
    var diff = montoTotal - totalValor;
    var errores = [];

    if (modo === 'monto_fijo') {
        if (Math.abs(diff) > 0.01) {
            errores.push(diff > 0 ? 'Faltan asignar $' + diff.toFixed(2) : 'Te pasaste por $' + Math.abs(diff).toFixed(2));
        }
    } else if (modo === 'porcentaje') {
        if (Math.abs(totalValor - 100) > 0.1) {
            errores.push('Los porcentajes deben sumar 100% (actual: ' + totalValor.toFixed(1) + '%)');
        }
    } else if (modo === 'partes') {
        if (totalValor < 1) {
            errores.push('Debe haber al menos 1 parte en total.');
        }
    } else if (modo === 'ajuste') {
        if (Math.abs(totalValor) > 0.01) {
            errores.push('Los ajustes deben sumar $0 (actual: $' + totalValor.toFixed(2) + ')');
        }
    }

    if (errores.length > 0) {
        error.innerHTML = errores.join('<br>');
        error.classList.remove('d-none');
    } else {
        error.classList.add('d-none');
    }

    // Mostrar montos calculados
    var montoRows = document.querySelectorAll('.division-monto');
    rows.forEach(function(row, i) {
        var span = row.querySelector('.division-monto');
        var v = valores[i];
        var calc = 0;
        if (modo === 'monto_fijo') calc = v;
        else if (modo === 'porcentaje') calc = montoTotal * v / 100;
        else if (modo === 'partes' && totalValor > 0) calc = montoTotal * v / totalValor;
        else if (modo === 'ajuste') {
            var porcion = montoTotal / rows.length;
            calc = porcion + v;
        }
        if (span) span.textContent = '$' + calc.toFixed(2);
    });
}

function aplicarDivision() {
    var modo = document.getElementById('divisionTipoInput').value;
    var rows = document.querySelectorAll('.participante-div-row');
    var container = document.getElementById('divisionValoresContainer');
    container.innerHTML = '';

    var index = 0;
    rows.forEach(function(row) {
        var uid = row.dataset.uid;
        var inp = row.querySelector('.division-valor');
        var v = inp.value.replace(',', '.') || '0';
        container.innerHTML += '<input type="hidden" name="division_valores[' + index + '][user_id]" value="' + uid + '">';
        container.innerHTML += '<input type="hidden" name="division_valores[' + index + '][valor]" value="' + v + '">';
        index++;
    });

    // Actualizar resumen
    var resumen = document.getElementById('divisionSummary');
    if (resumen) {
        var modosTexto = {
            'igualitario': 'dividido a partes iguales',
            'monto_fijo': 'dividido por monto fijo',
            'porcentaje': 'dividido por porcentaje',
            'partes': 'dividido por partes',
            'ajuste': 'dividido con ajustes',
        };
        resumen.innerHTML = 'Pagado por <strong>vos</strong> y ' + (modosTexto[modo] || modo) + ' entre <strong>' + rows.length + ' participante(s)</strong>.';
    }

    bootstrap.Modal.getInstance(document.getElementById('divisionModal')).hide();
}

// Inicializar el modal al cargar
document.addEventListener('DOMContentLoaded', function() {
    var modo = document.getElementById('divisionTipoInput').value;
    cambiarModoDivision(modo);
});
</script>
