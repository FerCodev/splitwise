<?php
$designProposalBlueprints = [
    ['title' => 'Menu lateral desktop', 'description' => 'Exploraciones para la navegacion principal de escritorio.', 'accent' => '#206bc4', 'surface' => '#eff6ff', 'items' => ['Sidebar compacto', 'Sidebar con metricas', 'Sidebar oscuro sobrio', 'Sidebar con grupos favoritos', 'Sidebar por secciones', 'Sidebar icon-only expandible', 'Sidebar con perfil arriba', 'Sidebar financiero', 'Sidebar admin destacado', 'Sidebar con acciones rapidas']],
    ['title' => 'Menu mobile', 'description' => 'Variantes para offcanvas, bottom bar y accesos tactiles.', 'accent' => '#0f766e', 'surface' => '#ecfdf5', 'items' => ['Offcanvas compacto', 'Menu con avatar grande', 'Bottom nav con FAB', 'Menu por tarjetas', 'Menu con acciones frecuentes', 'Menu seguro de sesion', 'Menu de una mano', 'Menu admin mobile', 'Menu con resumen', 'Menu minimalista']],
    ['title' => 'Home', 'description' => 'Primer pantallazo enfocado en grupos activos y carga rapida.', 'accent' => '#2563eb', 'surface' => '#eff6ff', 'items' => ['Home grupos primero', 'Home saldo primero', 'Home con deuda protagonista', 'Home por tabs densas', 'Home con CTA persistente', 'Home modo resumen', 'Home sin ruido visual', 'Home cards grandes', 'Home lista operativa', 'Home foco nuevo gasto']],
    ['title' => 'Grupos', 'description' => 'Detalle del grupo, balance, movimientos y contexto.', 'accent' => '#7c3aed', 'surface' => '#f5f3ff', 'items' => ['Grupo cabecera limpia', 'Grupo con gauge central', 'Grupo timeline', 'Grupo balance hero', 'Grupo miembros visibles', 'Grupo acciones fijas', 'Grupo historial cerrado', 'Grupo filtros integrados', 'Grupo por usuario', 'Grupo liquidado']],
    ['title' => 'Gastos y pagos', 'description' => 'Listados, totalizadores, exportacion y lectura de dinero.', 'accent' => '#0891b2', 'surface' => '#ecfeff', 'items' => ['Listado escaneable', 'Totalizador sticky', 'Exportacion destacada', 'Filtros superiores', 'Tabla mobile densa', 'Pago pendiente rojo', 'Pago confirmado verde', 'Gasto por pagador', 'Acciones por fila', 'Resumen del periodo']],
    ['title' => 'Formularios', 'description' => 'Alta y edicion de gastos, pagos, grupos y medios.', 'accent' => '#4f46e5', 'surface' => '#eef2ff', 'items' => ['Formulario por pasos', 'Formulario compacto', 'Monto protagonista', 'Selector tipo card', 'Pagador clickeable', 'Division visible', 'Validacion inline', 'Acciones inferiores', 'Carga con resumen', 'Formulario admin']],
    ['title' => 'Reportes', 'description' => 'Dashboards, metricas, rankings y exportaciones.', 'accent' => '#ca8a04', 'surface' => '#fffbeb', 'items' => ['Reporte KPI grid', 'Reporte mensual', 'Ranking por grupo', 'Ranking categoria', 'Movimientos del periodo', 'Pagos pendientes', 'Filtros avanzados', 'Export panel', 'Grafico simple', 'Reporte saldado']],
    ['title' => 'Medios de cobro', 'description' => 'Credenciales, datos copiables, favorito y ABM.', 'accent' => '#0369a1', 'surface' => '#f0f9ff', 'items' => ['Credencial bancaria', 'Credencial premium', 'Lista compacta', 'Favorito brillante', 'Gear visible', 'Datos copiables', 'Medio inactivo', 'Alias protagonista', 'CBU protagonista', 'Medio sin datos']],
    ['title' => 'Login y seguridad', 'description' => 'Acceso, recuperacion de cuenta y mensajes de seguridad.', 'accent' => '#475569', 'surface' => '#f8fafc', 'items' => ['Login centrado', 'Login split', 'Login mobile', 'Login seguro', 'Login operativo', 'Login admin', 'Recuperar password', 'Reset con guia', 'Error de credenciales', 'Sesion expirada']],
    ['title' => 'Admin y catalogo', 'description' => 'Pantallas internas, seleccion de componentes y propuestas.', 'accent' => '#be123c', 'surface' => '#fff1f2', 'items' => ['Catalogo por pantalla', 'Catalogo por componente', 'Selector activo', 'Propuesta aislada', 'Admin usuarios', 'Admin categorias', 'Panel de estilos', 'Vista comparativa', 'Estado sin selector', 'Historial de cambios']],
];

$renderDesignProposal = static function (array $group, string $name, int $index): string {
    $accent = $group['accent'];
    $surface = $group['surface'];
    $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
    $variant = $index % 10;
    $money = static fn (int|float $value): string => moneda($value);
    $groupTitle = $group['title'];
    $saldo = [5000, -112697, 130920, 0, -14570, 25000, -63800, 898212, 665511, 116351][$variant];
    $saldoClass = $saldo < 0 ? 'text-danger' : ($saldo > 0 ? 'text-success' : 'text-muted');
    $saldoText = $saldo < 0 ? $money(abs($saldo)) . ' debe' : ($saldo > 0 ? $money($saldo) . ' a favor' : $money(0) . ' saldado');
    $movements = [
        ['label' => 'Gasto', 'name' => 'Supermercado', 'person' => 'Antonella', 'date' => '20/06/2026', 'amount' => 59300, 'class' => 'primary'],
        ['label' => 'Gasto', 'name' => 'Garrafa', 'person' => 'Fernando', 'date' => '21/06/2026', 'amount' => 57000, 'class' => 'primary'],
        ['label' => 'Pago', 'name' => 'Transferencia', 'person' => 'Fernando -> Antonella', 'date' => '22/06/2026', 'amount' => 30000, 'class' => 'success'],
    ];
    $movement = $movements[$variant % count($movements)];

    ob_start();
    ?>
    <div class="design-proposal-card design-real-card" style="--proposal-accent: <?= esc($accent, 'attr') ?>; --proposal-surface: <?= esc($surface, 'attr') ?>;">
        <div class="design-proposal-topline"><span><?= $number ?></span><small><?= esc($groupTitle) ?></small></div>
        <div class="design-real-title"><strong><?= esc($name) ?></strong><p>Ejemplo con datos reales de SplitWise para validar lectura, jerarquia y acciones.</p></div>
        <?php if ($groupTitle === 'Menu lateral desktop'): ?>
            <div class="design-real-sidebar">
                <div class="design-real-user"><span>F</span><div><b>Fernando</b><small>Admin</small></div></div>
                <nav><b class="active">Home</b><b>Grupos</b><b>Gastos</b><b>Pagos</b><b>Reportes</b><b>Catalogo</b></nav>
                <div class="design-real-mini"><span>Mayo</span><strong class="text-success"><?= $money(130920) ?></strong></div>
            </div>
        <?php elseif ($groupTitle === 'Menu mobile'): ?>
            <div class="design-real-phone-menu">
                <div><span>F</span><strong>Fernando</strong><small>codigomontesdeoca@gmail.com</small></div>
                <button type="button">+ Nuevo gasto</button>
                <div class="design-real-bottom-nav"><b>Home</b><b>Reportes</b><b>Perfil</b><b>Menu</b></div>
            </div>
        <?php elseif ($groupTitle === 'Home'): ?>
            <div class="design-real-home-card">
                <div class="design-real-row"><div><b>Junio</b><small>Activo &middot; 2 integrantes</small></div><span class="badge bg-success">Activo</span></div>
                <div class="design-real-row"><span>Saldo</span><strong class="<?= $saldoClass ?>"><?= $saldoText ?></strong></div>
                <div class="design-real-movement"><span class="badge bg-primary">Gasto</span><b>Ines</b><strong><?= $money(25000) ?></strong><small>01/06/2026 &middot; Fernando</small></div>
                <div class="design-real-actions"><button type="button">Entrar</button><button type="button">+ Gasto</button></div>
            </div>
        <?php elseif ($groupTitle === 'Grupos'): ?>
            <div class="design-real-group-detail">
                <div class="design-real-row"><div><b>Junio</b><small>Movimientos filtrados: 01/06 al 30/06</small></div><strong class="<?= $saldoClass ?>"><?= $saldoText ?></strong></div>
                <div class="design-real-gauge"><i style="width: <?= 30 + ($variant * 6) ?>%"></i><span>Vos pagaste <?= $money(898212) ?> de <?= $money(1563723) ?></span></div>
                <div class="design-real-list"><div><b>Supermercado</b><span><?= $money(59300) ?></span></div><div><b>Pago parcial</b><span><?= $money(30000) ?></span></div></div>
            </div>
        <?php elseif ($groupTitle === 'Gastos y pagos'): ?>
            <div class="design-real-ledger">
                <div class="design-real-total"><small>Total filtrado</small><strong><?= $money($variant % 2 === 0 ? 898212 : 665511) ?></strong></div>
                <div class="design-real-movement"><span class="badge bg-<?= esc($movement['class']) ?>"><?= esc($movement['label']) ?></span><b><?= esc($movement['name']) ?></b><strong><?= $money($movement['amount']) ?></strong><small><?= esc($movement['date']) ?> &middot; <?= esc($movement['person']) ?> &middot; Junio</small></div>
                <div class="design-real-actions"><button type="button">PDF</button><button type="button">Excel</button><button type="button">Filtros</button></div>
            </div>
        <?php elseif ($groupTitle === 'Formularios'): ?>
            <div class="design-real-form">
                <label>Monto total</label><div class="design-real-input"><?= $money([10000, 25000, 59300, 9200, 57000][$variant % 5]) ?></div>
                <label>Pagado por</label><div class="design-real-picker"><span>F</span><b>Fernando</b><small>Seleccionar</small></div>
                <label>Division</label><div class="design-real-input muted">Por defecto, dividido en partes iguales.</div>
                <div class="design-real-actions"><button type="button">Cancelar</button><button type="button">Crear gasto</button></div>
            </div>
        <?php elseif ($groupTitle === 'Reportes'): ?>
            <div class="design-real-report">
                <div class="design-real-kpis"><div><small>Total gastado</small><b><?= $money(1563723) ?></b></div><div><small>Vos pagaste</small><b><?= $money(898212) ?></b></div></div>
                <div class="design-real-rank"><span>Mayo &middot; 58 gastos</span><strong><?= $money(1630862) ?></strong><i style="width: 92%"></i></div>
                <div class="design-real-movement"><span class="badge bg-primary">Gasto</span><b>Vacio</b><strong><?= $money(59300) ?></strong><small>01/05/2026 &middot; Antonella &middot; Mayo</small></div>
            </div>
        <?php elseif ($groupTitle === 'Medios de cobro'): ?>
            <div class="design-real-bank-card">
                <div class="design-real-row"><b>Cuenta Galicia</b><span>*</span></div>
                <small>TITULAR</small><strong>Montes de oca Fernando</strong>
                <small>ALIAS</small><strong>fernando.montesdeoca</strong>
                <div class="design-real-row"><small>CBU</small><b>0000003100098765432101</b></div>
            </div>
        <?php elseif ($groupTitle === 'Login y seguridad'): ?>
            <div class="design-real-login">
                <b>SplitWise</b><small>Entrar a tu cuenta</small>
                <div class="design-real-input">codigomontesdeoca@gmail.com</div>
                <div class="design-real-input">**********</div>
                <button type="button">Iniciar sesion</button>
                <small>Recuperar contrasena &middot; Crear cuenta</small>
            </div>
        <?php else: ?>
            <div class="design-real-admin">
                <div class="design-real-row"><div><b>Catalogo Admin</b><small>Componentes elegibles</small></div><span class="badge bg-primary">Admin</span></div>
                <div class="design-real-list"><div><b>Home</b><span>2 componentes</span></div><div><b>Grupos</b><span>3 componentes</span></div><div><b>Disenos app</b><span>100 propuestas</span></div></div>
                <div class="design-real-actions"><button type="button">Abrir</button><button type="button">Usar en app</button></div>
            </div>
        <?php endif; ?>
        <div class="design-proposal-footer"><span><?= esc($name) ?></span><b>Demo</b></div>
    </div>
    <?php
    return ob_get_clean();
};
?>

<section class="catalog-section">
    <div class="catalog-section-head">
        <div><h5>100 propuestas globales</h5><p>Exploraciones visuales para toda la app. Incluye menu lateral, mobile, pantallas principales, formularios, login y admin.</p></div>
        <span class="badge bg-secondary">100 propuestas</span>
    </div>
    <div class="design-curation-panel" data-design-curation-summary>
        <div>
            <span class="design-curation-kicker">Curaduria visual</span>
            <strong>Marca que ideas queres implementar o descartar</strong>
            <p>Las marcas quedan guardadas en este navegador para revisarlas despues.</p>
        </div>
        <div class="design-curation-counters">
            <span><b data-design-count="selected">0</b> para implementar</span>
            <span><b data-design-count="discarded">0</b> descartadas</span>
        </div>
        <div class="design-curation-actions">
            <button class="btn btn-outline-primary btn-sm" type="button" data-design-copy>Copiar seleccion</button>
            <button class="btn btn-outline-secondary btn-sm" type="button" data-design-clear>Limpiar marcas</button>
        </div>
    </div>
    <div class="catalog-nested-groups design-proposal-groups">
        <?php foreach ($designProposalBlueprints as $groupIndex => $group): ?>
            <div class="catalog-nested-section">
                <div class="catalog-nested-head"><div><h6><?= esc($group['title']) ?></h6><p><?= esc($group['description']) ?></p></div><span><?= count($group['items']) ?> propuesta(s)</span></div>
                <div class="catalog-grid design-proposal-grid">
                    <?php foreach ($group['items'] as $itemIndex => $item): ?>
                        <?php $globalIndex = ($groupIndex * 10) + $itemIndex; ?>
                        <?php $proposalId = 'design-' . str_pad((string) ($globalIndex + 1), 3, '0', STR_PAD_LEFT); ?>
                        <article class="catalog-variant design-curation-item" data-design-proposal-id="<?= esc($proposalId, 'attr') ?>" data-design-proposal-name="<?= esc($item, 'attr') ?>" data-design-proposal-group="<?= esc($group['title'], 'attr') ?>">
                            <div class="catalog-variant-meta"><span><?= esc($item) ?></span><small><?= esc($group['title']) ?></small></div>
                            <div class="catalog-source-badge">Diseno app</div>
                            <?= $renderDesignProposal($group, $item, $globalIndex) ?>
                            <div class="design-curation-controls" aria-label="Curar propuesta <?= esc($item, 'attr') ?>">
                                <button class="design-curation-btn design-curation-btn-keep" type="button" data-design-action="selected">Implementar</button>
                                <button class="design-curation-btn design-curation-btn-discard" type="button" data-design-action="discarded">Descartar</button>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<script>
(function() {
    var storageKey = 'splitwise.designCatalogCuration.v1';
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-design-proposal-id]'));
    var selectedCount = document.querySelector('[data-design-count="selected"]');
    var discardedCount = document.querySelector('[data-design-count="discarded"]');
    var copyButton = document.querySelector('[data-design-copy]');
    var clearButton = document.querySelector('[data-design-clear]');
    var state = {};

    try {
        state = JSON.parse(window.localStorage.getItem(storageKey) || '{}') || {};
    } catch (error) {
        state = {};
    }

    function save() {
        window.localStorage.setItem(storageKey, JSON.stringify(state));
    }

    function applyItemState(item) {
        var id = item.dataset.designProposalId;
        var value = state[id] || '';
        item.dataset.designState = value;
        item.querySelectorAll('[data-design-action]').forEach(function(button) {
            button.classList.toggle('is-active', button.dataset.designAction === value);
            button.setAttribute('aria-pressed', button.dataset.designAction === value ? 'true' : 'false');
        });
    }

    function updateCounts() {
        var selected = 0;
        var discarded = 0;
        Object.keys(state).forEach(function(id) {
            if (state[id] === 'selected') selected += 1;
            if (state[id] === 'discarded') discarded += 1;
        });
        if (selectedCount) selectedCount.textContent = String(selected);
        if (discardedCount) discardedCount.textContent = String(discarded);
    }

    function refresh() {
        items.forEach(applyItemState);
        updateCounts();
    }

    items.forEach(function(item) {
        item.querySelectorAll('[data-design-action]').forEach(function(button) {
            button.addEventListener('click', function() {
                var id = item.dataset.designProposalId;
                var action = button.dataset.designAction;
                state[id] = state[id] === action ? '' : action;
                if (!state[id]) delete state[id];
                save();
                refresh();
            });
        });
    });

    if (copyButton) {
        copyButton.addEventListener('click', function() {
            var lines = ['Catalogo de disenos - seleccion'];
            ['selected', 'discarded'].forEach(function(status) {
                var label = status === 'selected' ? 'Para implementar' : 'Descartadas';
                lines.push('', label + ':');
                var matches = items.filter(function(item) {
                    return state[item.dataset.designProposalId] === status;
                });
                if (!matches.length) {
                    lines.push('- Sin marcas');
                    return;
                }
                matches.forEach(function(item) {
                    lines.push('- ' + item.dataset.designProposalGroup + ' / ' + item.dataset.designProposalName + ' (' + item.dataset.designProposalId + ')');
                });
            });
            navigator.clipboard.writeText(lines.join('\n')).then(function() {
                copyButton.textContent = 'Copiado';
                window.setTimeout(function() { copyButton.textContent = 'Copiar seleccion'; }, 1500);
            }).catch(function() {
                window.prompt('Copia la seleccion', lines.join('\n'));
            });
        });
    }

    if (clearButton) {
        clearButton.addEventListener('click', function() {
            state = {};
            save();
            refresh();
        });
    }

    refresh();
})();
</script>