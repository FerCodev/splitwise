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
    $layout = $index % 5;
    $accent = $group['accent'];
    $surface = $group['surface'];
    $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
    ob_start();
    ?>
    <div class="design-proposal-card design-proposal-layout-<?= $layout ?>" style="--proposal-accent: <?= esc($accent, 'attr') ?>; --proposal-surface: <?= esc($surface, 'attr') ?>;">
        <div class="design-proposal-topline"><span><?= $number ?></span><small><?= esc($group['title']) ?></small></div>
        <div class="design-proposal-body">
            <div><strong><?= esc($name) ?></strong><p>Explora estructura, jerarquia visual y estados para esta zona de la app.</p></div>
            <div class="design-proposal-preview" aria-hidden="true"><span></span><span></span><span></span></div>
        </div>
        <div class="design-proposal-footer"><span>Propuesta visual</span><b>UI</b></div>
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
                            <div class="catalog-source-badge">Dise&ntilde;o app</div>
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
