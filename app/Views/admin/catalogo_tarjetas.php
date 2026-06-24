<?php
$numericSeccion = (string) (service('request')->getGet('seccion') ?? '');
if ($numericSeccion !== '' && ctype_digit($numericSeccion)) {
    $tablerTitles = [
        'M&eacute;tricas', 'Grupos', 'Movimientos', 'Deudas y pagos',
        'Medios y formularios', 'Navegaci&oacute;n y filtros', 'Estados', 'Alertas',
        'Tablas y listas', 'Visualizaci&oacute;n', 'Home operativo',
        'Medios de cobro premium', 'Administraci&oacute;n', 'Navegaci&oacute;n mobile',
        'Veloc&iacute;metros', 'Microinteracciones',
    ];
    $idx = (int) $numericSeccion;
    if (isset($tablerTitles[$idx])) {
        $raw = html_entity_decode($tablerTitles[$idx], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $raw = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $raw) ?: strtolower($raw);
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', $raw) ?? $raw, '-');
        $qs = http_build_query(['seccion' => $slug] + $_GET);
        header('Location: ' . base_url('admin/catalogo-tarjetas/tabler?' . $qs));
        exit;
    }
}
?>
<?= view('partials/_head', ['title' => 'SplitWise - Cat&aacute;logo de tarjetas']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Cat&aacute;logo Admin']) ?>

<?php
$fechaDemo = '2026-04-15';
$fechaCorta = date('d/m/Y', strtotime($fechaDemo));
$activeScreen = $activeScreen ?? '';

$selectedDebtVariant = $selectedDebtVariant ?? 'soft';
$selectedGaugeVariant = $selectedGaugeVariant ?? 'semicircle';
$selectedMovementVariant = $selectedMovementVariant ?? 'feed';
$selectedHomeGroupVariant = $selectedHomeGroupVariant ?? 'operational';
$selectedExpensesTotalVariant = $selectedExpensesTotalVariant ?? 'simple';
$selectedPaymentsTotalVariant = $selectedPaymentsTotalVariant ?? 'simple';
$selectedPaymentMethodVariant = $selectedPaymentMethodVariant ?? 'bank_card';
$catalogCurationState = $catalogCurationState ?? [];
$componentDecisions = $componentDecisions ?? [];

$catalogItemKey = static function (string $catalogKey, string $sectionKey, ?string $groupKey, string $itemName): string {
    return substr(sha1($catalogKey . '|' . $sectionKey . '|' . ($groupKey ?? '') . '|' . $itemName), 0, 16);
};

$catalogDecisionKey = static function (string $catalogKey, string $sectionKey, ?string $groupKey, string $itemKey): string {
    return implode('|', [$catalogKey, $sectionKey, $groupKey ?? '', $itemKey]);
};

$catalogDecision = static function (string $catalogKey, string $sectionKey, ?string $groupKey, string $itemKey) use ($componentDecisions, $catalogDecisionKey): ?array {
    return $componentDecisions[$catalogDecisionKey($catalogKey, $sectionKey, $groupKey, $itemKey)] ?? null;
};

$requestUri = service('request')->getUri();
$currentCatalogUrl = current_url() . ($requestUri->getQuery() ? '?' . $requestUri->getQuery() : '');

$decisionMeta = [
    'implement' => ['label' => 'Para implementar', 'class' => 'bg-success'],
    'discard' => ['label' => 'Descartado', 'class' => 'bg-secondary'],
    'redesign' => ['label' => 'Redisenar', 'class' => 'bg-warning text-dark'],
];

$candidateActions = static function (
    string $catalogKey,
    string $sectionKey,
    ?string $groupKey,
    string $itemKey,
    string $itemName,
    string $itemHint,
    string $sourceLabel,
    ?array $decision,
    ?string $variantKey = null
) use ($decisionMeta, $currentCatalogUrl): string {
    $currentDecision = $decision['decision'] ?? '';
    $currentNotes = $decision['redesign_notes'] ?? '';
    ob_start();
    ?>
    <div class="catalog-decision-panel">
        <form method="post" action="<?= base_url('admin/catalogo-tarjetas/decision') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="catalog_key" value="<?= esc($catalogKey) ?>">
            <input type="hidden" name="section_key" value="<?= esc($sectionKey) ?>">
            <input type="hidden" name="group_key" value="<?= esc($groupKey ?? '') ?>">
            <input type="hidden" name="item_key" value="<?= esc($itemKey) ?>">
            <input type="hidden" name="item_name" value="<?= esc($itemName) ?>">
            <input type="hidden" name="item_hint" value="<?= esc($itemHint) ?>">
            <input type="hidden" name="source_label" value="<?= esc($sourceLabel) ?>">
            <input type="hidden" name="return_url" value="<?= esc($currentCatalogUrl, 'attr') ?>">
            <?php if ($variantKey !== null): ?>
                <input type="hidden" name="variant_key" value="<?= esc($variantKey) ?>">
            <?php endif; ?>

            <div class="catalog-decision-head">
                <span>Decision</span>
                <?php if ($currentDecision && isset($decisionMeta[$currentDecision])): ?>
                    <span class="badge <?= esc($decisionMeta[$currentDecision]['class']) ?>"><?= $decisionMeta[$currentDecision]['label'] ?></span>
                <?php else: ?>
                    <span class="badge bg-light text-dark">Sin marcar</span>
                <?php endif; ?>
            </div>

            <div class="catalog-decision-actions">
                <button type="submit" name="decision" value="implement" class="btn btn-sm <?= $currentDecision === 'implement' ? 'btn-success' : 'btn-outline-success' ?>">
                    Implementar
                </button>
                <button type="submit" name="decision" value="discard" class="btn btn-sm <?= $currentDecision === 'discard' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
                    Descartar
                </button>
                <button type="button" class="btn btn-sm catalog-redesign-toggle <?= $currentDecision === 'redesign' ? 'btn-warning' : 'btn-outline-warning' ?>">
                    Redisenar
                </button>
            </div>

            <div class="catalog-redesign-panel" style="display:none">
                <textarea name="redesign_notes" class="form-control form-control-sm catalog-redesign-notes" rows="2" placeholder="Indicaciones para mejorar este componente"><?= esc($currentNotes) ?></textarea>
                <button type="submit" name="decision" value="redesign" class="btn btn-warning btn-sm mt-2">Guardar rediseno</button>
            </div>
        </form>

        <?php if ($currentDecision !== ''): ?>
            <form method="post" action="<?= base_url('admin/catalogo-tarjetas/decision/limpiar') ?>" class="mt-2">
                <?= csrf_field() ?>
                <input type="hidden" name="catalog_key" value="<?= esc($catalogKey) ?>">
                <input type="hidden" name="section_key" value="<?= esc($sectionKey) ?>">
                <input type="hidden" name="group_key" value="<?= esc($groupKey ?? '') ?>">
                <input type="hidden" name="item_key" value="<?= esc($itemKey) ?>">
                <input type="hidden" name="return_url" value="<?= esc($currentCatalogUrl, 'attr') ?>">
                <button type="submit" class="btn btn-outline-danger btn-sm">Quitar marca</button>
            </form>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
};

$variantAction = static function (string $screenKey, string $componentKey, string $variant, string $selectedVariant, string $returnScreen): string {
    $selected = $selectedVariant === $variant;
    ob_start();
    ?>
    <form method="post" action="<?= base_url('admin/catalogo-tarjetas/componente') ?>" class="catalog-component-action">
        <?= csrf_field() ?>
        <input type="hidden" name="screen_key" value="<?= esc($screenKey) ?>">
        <input type="hidden" name="component_key" value="<?= esc($componentKey) ?>">
        <input type="hidden" name="variant_key" value="<?= esc($variant) ?>">
        <input type="hidden" name="return_screen" value="<?= esc($returnScreen) ?>">
        <button type="submit" class="btn btn-sm <?= $selected ? 'btn-success' : 'btn-outline-primary' ?>" <?= $selected ? 'disabled' : '' ?>>
            <?= $selected ? 'Seleccionado' : 'Usar este dise&ntilde;o' ?>
        </button>
    </form>
    <?php
    return ob_get_clean();
};

$catalogDesignId = static function (string $scope, string $group, string $name, ?string $variant = null): string {
    return \App\Services\UiComponentResolver::designId($scope, $group, $name, $variant);
};

$catalogCurationControls = static function (bool $inUse = false): string {
    ob_start();
    ?>
    <div class="catalog-curation-controls">
        <button class="catalog-curation-btn catalog-curation-btn-discard" type="button" data-catalog-action="discarded" <?= $inUse ? 'disabled' : '' ?>>Descartar</button>
        <button class="catalog-curation-btn catalog-curation-btn-implement" type="button" data-catalog-action="selected">Implementar</button>
        <button class="catalog-curation-btn catalog-curation-btn-redesign" type="button" data-catalog-redesign>Redise&ntilde;ar</button>
    </div>
    <?php if ($inUse): ?>
        <div class="catalog-curation-lock">En uso: no se puede descartar.</div>
    <?php endif; ?>
    <div class="catalog-redesign-note" data-catalog-redesign-panel hidden>
        <label>Comentarios para redise&ntilde;o</label>
        <textarea data-catalog-redesign-note rows="3" placeholder="Que habria que cambiar para que este dise&ntilde;o sirva?"></textarea>
        <button class="btn btn-outline-primary btn-sm" type="button" data-catalog-redesign-save>Guardar comentario</button>
    </div>
    <?php
    return ob_get_clean();
};
$screens = [
    'home' => [
        'title' => 'Home',
        'description' => 'Grupos activos y deudas pendientes.',
        'badge' => '2 componentes',
    ],
    'grupos' => [
        'title' => 'Grupos',
        'description' => 'Detalle del grupo, movimientos y veloc&iacute;metro.',
        'badge' => '2 componentes',
    ],
    'gastos' => [
        'title' => 'Gastos',
        'description' => 'Totalizador superior de gastos filtrados.',
        'badge' => '1 componente',
    ],
    'pagos' => [
        'title' => 'Pagos',
        'description' => 'Totalizador superior de pagos filtrados.',
        'badge' => '1 componente',
    ],
    'medios' => [
        'title' => 'Medios de cobro',
        'description' => 'Tarjeta del medio favorito, datos copiables y configuraci&oacute;n.',
        'badge' => '1 componente',
    ],
    'login' => [
        'title' => 'Login',
        'description' => 'Propuestas para la pantalla de inicio de sesi&oacute;n.',
        'badge' => '6 propuestas',
    ],
    'tabler' => [
        'title' => 'Tabler',
        'description' => 'Subcat&aacute;logo inspirado en Tabler para evaluar una librer&iacute;a visual externa.',
        'badge' => '87 ejemplos',
    ],
    'propuestas' => [
        'title' => 'Propuestas',
        'description' => 'Ideas visuales que todav&iacute;a no est&aacute;n conectadas a pantallas reales.',
        'badge' => 'sin selector',
    ],
];

$catalogDemoData = [
    'fechaDemo' => $fechaDemo,
    'fechaCorta' => $fechaCorta,
];
$catalog = require APPPATH . 'Catalog/componentes.php';

$loginProposals = [
    [
        'name' => 'Centrada limpia',
        'hint' => 'Formulario directo con marca y foco en ingresar.',
        'render' => static fn () => '<div class="login-preview login-preview-centered"><div class="login-preview-card"><div class="login-preview-brand"><span>S</span><strong>SplitWise</strong></div><label>Email</label><div class="login-preview-input">fernando@email.com</div><label>Contrase&ntilde;a</label><div class="login-preview-input login-preview-password">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</div><button class="btn btn-primary w-100" type="button">Ingresar</button><a href="#">Olvid&eacute; mi contrase&ntilde;a</a></div></div>',
    ],
    [
        'name' => 'Panel dividido',
        'hint' => 'Ideal para desktop, con contexto de la app.',
        'render' => static fn () => '<div class="login-preview login-preview-split"><div class="login-preview-side"><span>Control&aacute; gastos compartidos</span><strong>Grupos, pagos y saldos en un solo lugar.</strong><small>Entr&aacute; y carg&aacute; tu pr&oacute;ximo gasto en pocos toques.</small></div><div class="login-preview-card"><strong>Iniciar sesi&oacute;n</strong><div class="login-preview-input">Email</div><div class="login-preview-input">Contrase&ntilde;a</div><button class="btn btn-primary w-100" type="button">Ingresar</button></div></div>',
    ],
    [
        'name' => 'Mobile primero',
        'hint' => 'Tarjeta amplia y controles c&oacute;modos.',
        'render' => static fn () => '<div class="login-preview login-preview-mobile"><div class="login-preview-phone"><div class="login-preview-brand"><span>S</span><strong>SplitWise</strong></div><p>Ingres&aacute; para ver tus grupos activos.</p><div class="login-preview-input">Email</div><div class="login-preview-input">Contrase&ntilde;a</div><button class="btn btn-primary w-100" type="button">Ingresar</button><button class="btn btn-light w-100" type="button">Recuperar acceso</button></div></div>',
    ],
    [
        'name' => 'Segura compacta',
        'hint' => 'Mensaje de seguridad visible sin ocupar demasiado.',
        'render' => static fn () => '<div class="login-preview login-preview-secure"><div class="login-preview-card"><div class="login-preview-alert"><strong>Acceso seguro</strong><small>Tus datos se validan antes de entrar.</small></div><div class="login-preview-input">Email</div><div class="login-preview-input login-preview-password">&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</div><button class="btn btn-primary w-100" type="button">Ingresar</button></div></div>',
    ],
    [
        'name' => 'Resumen operativo',
        'hint' => 'Muestra qu&eacute; podr&aacute; hacer el usuario al entrar.',
        'render' => static fn () => '<div class="login-preview login-preview-summary"><div class="login-preview-card"><strong>Bienvenido</strong><div class="login-preview-mini-list"><span>Grupos activos</span><span>Pagos pendientes</span><span>Medios de cobro</span></div><div class="login-preview-input">Email</div><div class="login-preview-input">Contrase&ntilde;a</div><button class="btn btn-primary w-100" type="button">Ingresar</button></div></div>',
    ],
    [
        'name' => 'Sobria admin',
        'hint' => 'Mas neutra, con estetica de panel interno.',
        'render' => static fn () => '<div class="login-preview login-preview-admin"><div class="login-preview-card"><div class="login-preview-kicker">SplitWise</div><strong>Acceso a la cuenta</strong><div class="login-preview-input">usuario@dominio.com</div><div class="login-preview-input">Contrase&ntilde;a</div><div class="login-preview-actions"><button class="btn btn-primary" type="button">Ingresar</button><button class="btn btn-outline-secondary" type="button">Ayuda</button></div></div></div>',
    ],
];

$tablerCatalog = [
    [
        'title' => 'M&eacute;tricas',
        'description' => 'Cards de resumen tipo dashboard para Home y Reportes.',
        'items' => [
            ['name' => 'Stat card', 'hint' => 'M&eacute;trica compacta con tendencia.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><span class="tabler-proposal-icon tabler-proposal-icon-blue"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-7"/></svg></span><span class="badge bg-success-subtle text-success">+12%</span></div><div class="tabler-proposal-label">Total del mes</div><div class="tabler-proposal-value">' . moneda(1563723) . '</div><div class="text-muted small">58 movimientos cargados</div></div>'],
            ['name' => 'KPI horizontal', 'hint' => 'Resumen con icono y subtotales.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-card-flat"><div class="tabler-proposal-row"><span class="tabler-proposal-icon tabler-proposal-icon-green">$</span><div class="text-end"><div class="tabler-proposal-value text-success">' . moneda(898212) . '</div><div class="text-muted small">Vos pagaste</div></div></div><div class="tabler-proposal-split"><span>Tu parte <b>' . moneda(449106) . '</b></span><span>Registros <b>18</b></span></div></div>'],
        ],
    ],
    [
        'title' => 'Grupos',
        'description' => 'Cards para listado de grupos activos, cerrados o con deuda.',
        'items' => [
            ['name' => 'Grupo dashboard', 'hint' => 'Card limpia con acciones.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row align-items-start"><div><div class="tabler-proposal-title">Junio</div><div class="text-muted small">Grupo activo</div></div><span class="badge bg-success">Activo</span></div><div class="tabler-proposal-balance text-danger">' . moneda(112697) . ' debe</div><div class="tabler-proposal-progress"><span style="width: 62%;"></span></div><div class="tabler-proposal-actions"><button class="btn btn-outline-primary btn-sm" type="button">Entrar</button><button class="btn btn-primary btn-sm" type="button">+ Gasto</button></div></div>'],
            ['name' => 'Grupo con avatar', 'hint' => 'Avatar, saldo y CTA principal.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row align-items-start"><div class="catalog-row"><span class="tabler-proposal-avatar">J</span><div><div class="tabler-proposal-title">Junio</div><div class="text-muted small">2 integrantes</div></div></div><span class="badge bg-success">Activo</span></div><div class="tabler-proposal-copy"><span>Tu balance</span><strong class="text-success">' . moneda(5000) . ' a favor</strong></div><button class="btn btn-primary btn-sm w-100" type="button">Cargar gasto</button></div>'],
            ['name' => 'Grupo cerrado', 'hint' => 'Historial sobrio y consultable.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-muted"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Abril</div><div class="text-muted small">41 movimientos</div></div><span class="badge bg-secondary">Cerrado</span></div><div class="tabler-proposal-split"><span>Total <b>' . moneda(1563723) . '</b></span><span>Saldo <b>' . moneda(0) . '</b></span></div><button class="btn btn-outline-secondary btn-sm w-100" type="button">Ver historial</button></div>'],
        ],
    ],
    [
        'title' => 'Movimientos',
        'description' => 'Listas y feeds para gastos, pagos y actividad del grupo.',
        'items' => [
            ['name' => 'Lista operativa', 'hint' => 'Movimientos tipo timeline.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-list"><div><span class="tabler-proposal-dot bg-primary"></span><div><strong>Supermercado</strong><small>Fernando &middot; Gasto</small></div><b class="text-primary">' . moneda(45200) . '</b></div><div><span class="tabler-proposal-dot bg-success"></span><div><strong>Pago parcial</strong><small>Antonella &middot; Pago</small></div><b class="text-success">' . moneda(30000) . '</b></div><div><span class="tabler-proposal-dot bg-danger"></span><div><strong>Saldo pendiente</strong><small>Junio &middot; Deuda</small></div><b class="text-danger">' . moneda(12600) . '</b></div></div></div>'],
            ['name' => 'Movimiento compacto', 'hint' => 'Una fila densa por movimiento.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><span class="badge bg-primary me-1">Gasto</span><strong>Farmacia</strong><div class="text-muted small">20/06/2026 &middot; Antonella</div></div><b class="text-primary">' . moneda(9200) . '</b></div><div class="tabler-proposal-row"><div><span class="badge bg-success me-1">Pago</span><strong>Transferencia</strong><div class="text-muted small">21/06/2026 &middot; Fernando</div></div><b class="text-success">' . moneda(5000) . '</b></div></div>'],
            ['name' => 'Feed con filtros', 'hint' => 'Header operativo con bot&oacute;n de filtro.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Movimientos</div><div class="text-muted small">Junio &middot; 12 registros</div></div><button class="btn btn-outline-primary btn-sm" type="button">Filtrar</button></div><div class="tabler-proposal-progress"><span style="width: 72%;"></span></div><div class="text-muted small">Mostrando gastos y pagos del grupo.</div></div>'],
        ],
    ],
    [
        'title' => 'Deudas y pagos',
        'description' => 'Tarjetas para pendientes, cobros y comprobantes.',
        'items' => [
            ['name' => 'Deuda suave', 'hint' => 'Rojo sobrio estilo Tabler.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-danger"><div class="tabler-proposal-row"><div><span class="badge bg-danger">Deb&eacute;s</span><strong class="d-block mt-2">Fernando</strong><div class="text-muted small">Junio &middot; Pago pendiente</div></div><b class="text-danger">' . moneda(112697) . '</b></div></div>'],
            ['name' => 'Cobro pendiente', 'hint' => 'Verde para dinero a favor.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-success"><div class="tabler-proposal-row"><div><span class="badge bg-success">Te deben</span><strong class="d-block mt-2">Antonella</strong><div class="text-muted small">Mayo &middot; Cobro pendiente</div></div><b class="text-success">' . moneda(14570) . '</b></div></div>'],
            ['name' => 'Comprobante', 'hint' => 'Formato de pago confirmado.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Pago confirmado</div><div class="text-muted small">Fernando &rarr; Antonella</div></div><span class="badge bg-success">OK</span></div><div class="tabler-proposal-copy"><span>Importe</span><strong class="text-success">' . moneda(30000) . '</strong></div><div class="text-muted small">21/06/2026 &middot; Mercado Pago</div></div>'],
        ],
    ],
    [
        'title' => 'Medios y formularios',
        'description' => 'Credenciales, controles y formularios compactos.',
        'items' => [
            ['name' => 'Medio mini', 'hint' => 'Medio de cobro con datos copiables.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Mercado Pago</div><div class="text-muted small">Favorito</div></div><span class="tabler-proposal-icon tabler-proposal-icon-green"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg></span></div><div class="tabler-proposal-copy"><span>Alias</span><strong>fernando.montesdeoca</strong></div><div class="tabler-proposal-copy"><span>CBU/CVU</span><strong>0000003100012345678901</strong></div></div>'],
            ['name' => 'Campo pagador', 'hint' => 'Selector de persona estilo control.', 'render' => static fn () => '<div class="tabler-proposal-card"><label class="tabler-proposal-label">Pagado por</label><div class="tabler-proposal-input"><span class="tabler-proposal-avatar tabler-proposal-avatar-sm">F</span><strong>Fernando</strong><span>Seleccionar</span></div><label class="tabler-proposal-label">Monto</label><div class="tabler-proposal-input"><strong>' . moneda(10000) . '</strong><span>ARS</span></div></div>'],
            ['name' => 'Botonera', 'hint' => 'Acciones claras para ABM.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-title">Acciones</div><div class="tabler-proposal-actions"><button class="btn btn-primary" type="button">Guardar</button><button class="btn btn-outline-secondary" type="button">Cancelar</button></div><button class="btn btn-outline-danger btn-sm w-100" type="button">Eliminar registro</button><div class="text-muted small">Botones sobrios y previsibles.</div></div>'],
        ],
    ],
    [
        'title' => 'Navegaci&oacute;n y filtros',
        'description' => 'Patrones para tabs, filtros y acciones r&aacute;pidas.',
        'items' => [
            ['name' => 'Tabs segmentados', 'hint' => 'Selector de estado para Home.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-tabs"><span>Todos</span><span class="active">Activos</span><span>Cerrados</span></div><div class="text-muted small">Ideal para alternar listados sin perder contexto.</div></div>'],
            ['name' => 'Barra de filtros', 'hint' => 'Filtros compactos con contador.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Filtros</div><div class="text-muted small">Fecha, categor&iacute;a e integrante</div></div><span class="badge bg-primary">3 activos</span></div><div class="tabler-proposal-filter-row"><span>Junio</span><span>Otros</span><span>Fernando</span></div><button class="btn btn-primary btn-sm w-100" type="button">Aplicar filtros</button></div>'],
            ['name' => 'Acciones r&aacute;pidas', 'hint' => 'Botones densos para exportar.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-title">Exportar movimientos</div><div class="tabler-proposal-actions"><button class="btn btn-danger btn-sm" type="button">PDF</button><button class="btn btn-success btn-sm" type="button">Excel</button></div><button class="btn btn-outline-primary btn-sm w-100" type="button">Filtrar</button><div class="text-muted small">Controles separados y claros.</div></div>'],
        ],
    ],
    [
        'title' => 'Estados',
        'description' => 'Empty states, alertas y confirmaciones con est&eacute;tica Tabler.',
        'items' => [
            ['name' => 'Sin grupos', 'hint' => 'Empty state con acci&oacute;n primaria.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-empty"><span class="tabler-proposal-icon tabler-proposal-icon-blue">+</span><strong>No ten&eacute;s grupos activos</strong><div class="text-muted small">Cre&aacute; uno para empezar a cargar gastos.</div><button class="btn btn-primary btn-sm" type="button">Crear grupo</button></div>'],
            ['name' => 'Sin deudas', 'hint' => 'Estado positivo y liviano.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-empty tabler-proposal-success"><span class="tabler-proposal-icon tabler-proposal-icon-green">&check;</span><strong>Todo saldado</strong><div class="text-muted small">No ten&eacute;s deudas pendientes.</div></div>'],
            ['name' => 'Alerta guardado', 'hint' => 'Feedback de operaci&oacute;n exitosa.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-alert tabler-proposal-alert-success"><strong>Gasto creado</strong><span>El movimiento se agreg&oacute; correctamente.</span></div><div class="tabler-proposal-alert tabler-proposal-alert-danger"><strong>Revis&aacute; el monto</strong><span>El importe ingresado no es v&aacute;lido.</span></div></div>'],
        ],
    ],
    [
        'title' => 'Alertas',
        'description' => 'Propuestas Tabler para mensajes de sistema, pagos, filtros y acciones sensibles.',
        'items' => [
            ['name' => 'Exito compacto', 'hint' => 'Confirmaci&oacute;n corta.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-success"><span class="tabler-alert-icon">&check;</span><div><strong>Gasto creado</strong><small>Se agreg&oacute; correctamente al grupo Junio.</small></div></div>'],
            ['name' => 'Error con acci&oacute;n', 'hint' => 'Problema recuperable.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-danger"><span class="tabler-alert-icon">!</span><div><strong>No se pudo guardar</strong><small>Revis&aacute; el monto y volvelo a intentar.</small><button class="btn btn-outline-danger btn-sm mt-2" type="button">Reintentar</button></div></div>'],
            ['name' => 'Warning deuda', 'hint' => 'Deuda pendiente sin sonar agresiva.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-warning"><span class="tabler-alert-icon">$</span><div><strong>Ten&eacute;s una deuda pendiente</strong><small>Le deb&eacute;s a Fernando ' . moneda(112697) . ' del grupo Junio.</small></div></div>'],
            ['name' => 'Info filtro', 'hint' => 'Explica el contexto filtrado.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-info"><span class="tabler-alert-icon">i</span><div><strong>Mostrando abril 2026</strong><small>Los movimientos est&aacute;n filtrados por fecha del grupo.</small></div></div>'],
            ['name' => 'Liquidado', 'hint' => 'Estado final positivo.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-settled"><span class="tabler-alert-icon">&check;</span><div><strong>Grupo liquidado</strong><small>No quedan pagos ni cobros pendientes.</small></div><span class="badge bg-success">OK</span></div>'],
            ['name' => 'Exportacion lista', 'hint' => 'Feedback para PDF o Excel.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-export"><span class="tabler-alert-icon">&darr;</span><div><strong>Archivo preparado</strong><small>El reporte incluye total filtrado y 41 movimientos.</small></div><button class="btn btn-primary btn-sm" type="button">Descargar</button></div>'],
            ['name' => 'Conexion', 'hint' => 'Aviso t&eacute;cnico suave.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-neutral"><span class="tabler-alert-icon">~</span><div><strong>Trabajando sin conexi&oacute;n</strong><small>Los cambios se sincronizar&aacute;n cuando vuelva la red.</small></div></div>'],
            ['name' => 'Confirmacion borrar', 'hint' => 'Acci&oacute;n destructiva clara.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-delete"><div><strong>Eliminar medio de cobro</strong><small>Esta acci&oacute;n no se puede deshacer.</small></div><div class="tabler-alert-actions"><button class="btn btn-light btn-sm" type="button">Cancelar</button><button class="btn btn-danger btn-sm" type="button">Eliminar</button></div></div>'],
            ['name' => 'Pago sugerido', 'hint' => 'Alerta con monto protagonista.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-payment"><div><small>Pago sugerido</small><strong>Antonella &rarr; Fernando</strong></div><b>' . moneda(116351) . '</b></div>'],
            ['name' => 'Actualizacion disponible', 'hint' => 'Mensaje administrativo.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-update"><span class="tabler-alert-icon">*</span><div><strong>Nuevo dise&ntilde;o disponible</strong><small>Hay una variante de tarjeta lista para probar.</small></div><span class="badge bg-primary">Cat&aacute;logo</span></div>'],
            ['name' => 'Validacion monto', 'hint' => 'Error puntual de formulario.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-inline-error"><span class="tabler-alert-icon">!</span><div><strong>Monto requerido</strong><small>Ingres&aacute; un importe mayor a cero para crear el gasto.</small></div></div>'],
            ['name' => 'Fecha fuera de periodo', 'hint' => 'Aviso preventivo.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-calendar"><span class="tabler-alert-icon">31</span><div><strong>La fecha no coincide</strong><small>El grupo pertenece a abril y elegiste una fecha de junio.</small></div></div>'],
            ['name' => 'Permiso admin', 'hint' => 'Bloqueo por rol.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-lock"><span class="tabler-alert-icon">#</span><div><strong>Solo administradores</strong><small>Esta configuraci&oacute;n del grupo requiere permisos de admin.</small></div></div>'],
            ['name' => 'Favorito actualizado', 'hint' => 'Feedback para medios de cobro.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-favorite"><span class="tabler-alert-icon">&starf;</span><div><strong>Favorito actualizado</strong><small>Mercado Pago queda como medio principal.</small></div></div>'],
            ['name' => 'Importacion lista', 'hint' => 'Carga masiva correcta.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-import"><span class="tabler-alert-icon">&uarr;</span><div><strong>Importaci&oacute;n completada</strong><small>Se cargaron 23 gastos desde el archivo Excel.</small></div><span class="badge bg-success">23</span></div>'],
            ['name' => 'Importacion parcial', 'hint' => 'Carga con advertencias.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-partial"><span class="tabler-alert-icon">!</span><div><strong>Importaci&oacute;n parcial</strong><small>18 registros cargados y 2 filas necesitan revisi&oacute;n.</small></div><button class="btn btn-warning btn-sm" type="button">Ver filas</button></div>'],
            ['name' => 'Liquidacion recomendada', 'hint' => 'Sugerencia inteligente.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-recommend"><span class="tabler-alert-icon">$</span><div><strong>Conviene liquidar</strong><small>Un pago de ' . moneda(14570) . ' deja el grupo saldado.</small></div></div>'],
            ['name' => 'Grupo sin movimientos', 'hint' => 'Estado inicial dentro de grupo.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-empty"><span class="tabler-alert-icon">+</span><div><strong>Sin movimientos todav&iacute;a</strong><small>Carg&aacute; el primer gasto para empezar el balance.</small></div><button class="btn btn-primary btn-sm" type="button">Agregar</button></div>'],
            ['name' => 'Filtro sin resultados', 'hint' => 'B&uacute;squeda vac&iacute;a.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-empty"><span class="tabler-alert-icon">?</span><div><strong>No hay coincidencias</strong><small>Prob&aacute; limpiar filtros o cambiar la palabra clave.</small></div></div>'],
            ['name' => 'Pago duplicado', 'hint' => 'Prevenci&oacute;n de carga repetida.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-duplicate"><span class="tabler-alert-icon">2</span><div><strong>Posible pago duplicado</strong><small>Ya existe un pago similar cargado hoy por ' . moneda(30000) . '.</small></div></div>'],
            ['name' => 'Sesion por vencer', 'hint' => 'Aviso de seguridad.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-session"><span class="tabler-alert-icon">!</span><div><strong>Tu sesi&oacute;n est&aacute; por vencer</strong><small>Guard&aacute; los cambios antes de continuar.</small></div><button class="btn btn-outline-secondary btn-sm" type="button">Seguir</button></div>'],
            ['name' => 'Cambios sin guardar', 'hint' => 'Previene p&eacute;rdida de datos.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-unsaved"><span class="tabler-alert-icon">*</span><div><strong>Hay cambios sin guardar</strong><small>Si sal&iacute;s ahora, se pierde la edici&oacute;n actual.</small></div></div>'],
            ['name' => 'Reporte pesado', 'hint' => 'Exportaci&oacute;n en proceso.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-progress"><div><strong>Preparando reporte</strong><small>Estamos generando el PDF con 58 movimientos.</small><div class="tabler-alert-progressbar"><span style="width: 68%;"></span></div></div><b>68%</b></div>'],
            ['name' => 'Nuevo integrante', 'hint' => 'Evento positivo de grupo.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-member"><span class="tabler-alert-icon">+</span><div><strong>Antonella se sum&oacute;</strong><small>Ya puede participar en gastos y pagos del grupo.</small></div></div>'],
            ['name' => 'Integrante removido', 'hint' => 'Cambio sensible de grupo.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-member-out"><span class="tabler-alert-icon">-</span><div><strong>Integrante removido</strong><small>Sus movimientos hist&oacute;ricos se mantienen en el grupo.</small></div></div>'],
            ['name' => 'Balance recalculado', 'hint' => 'Transparencia del sistema.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-recalc"><span class="tabler-alert-icon">%</span><div><strong>Balance recalculado</strong><small>Se actualiz&oacute; el saldo luego de cambiar participantes.</small></div></div>'],
            ['name' => 'Medio incompleto', 'hint' => 'Dato faltante en medio de cobro.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-warning"><span class="tabler-alert-icon">!</span><div><strong>Falta alias o CBU</strong><small>Complet&aacute; al menos un dato para compartir este medio.</small></div></div>'],
            ['name' => 'Backup creado', 'hint' => 'Administraci&oacute;n segura.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-neutral"><span class="tabler-alert-icon">&check;</span><div><strong>Backup creado</strong><small>El estado actual qued&oacute; guardado antes de cambiar el cat&aacute;logo.</small></div></div>'],
            ['name' => 'Sincronizado', 'hint' => 'Estado remoto actualizado.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-sync"><span class="tabler-alert-icon">&check;</span><div><strong>Todo sincronizado</strong><small>Los cambios locales ya est&aacute;n en GitHub.</small></div><span class="badge bg-success">Push</span></div>'],
            ['name' => 'Mantenimiento', 'hint' => 'Mensaje global sobrio.', 'render' => static fn () => '<div class="tabler-alert-card tabler-alert-maintenance"><span class="tabler-alert-icon">i</span><div><strong>Mantenimiento programado</strong><small>La app puede responder m&aacute;s lento durante unos minutos.</small></div></div>'],
        ],
    ],
    [
        'title' => 'Tablas y listas',
        'description' => 'Vistas densas para escritorio o panel admin.',
        'groups' => [
            [
                'title' => 'Tablas',
                'description' => 'Estructuras para movimientos y datos tabulares.',
                'items' => [
                    ['name' => 'Tabla compacta', 'hint' => 'Movimientos escaneables.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-table"><div><b>Fecha</b><b>Tipo</b><b>Monto</b></div><div><span>20/06</span><span>Gasto</span><strong>' . moneda(9200) . '</strong></div><div><span>21/06</span><span>Pago</span><strong class="text-success">' . moneda(5000) . '</strong></div><div><span>22/06</span><span>Gasto</span><strong>' . moneda(12600) . '</strong></div></div></div>'],
                ],
            ],
            [
                'title' => 'Rankings',
                'description' => 'Listas ordenadas para grupos, categor&iacute;as o usuarios.',
                'items' => [
                    ['name' => 'Ranking', 'hint' => 'Top grupos o categor&iacute;as.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-ranking"><div><span>1</span><b>Mayo</b><strong>' . moneda(1630862) . '</strong></div><div><span>2</span><b>Abril</b><strong>' . moneda(1563723) . '</strong></div><div><span>3</span><b>Junio</b><strong>' . moneda(112697) . '</strong></div></div></div>'],
                ],
            ],
            [
                'title' => 'Integrantes',
                'description' => 'Filas para personas, roles y estados.',
                'items' => [
                    ['name' => 'Lista de usuarios', 'hint' => 'Integrantes y roles.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-list"><div><span class="tabler-proposal-avatar tabler-proposal-avatar-sm">F</span><div><strong>Fernando</strong><small>Administrador</small></div><span class="badge bg-primary">Admin</span></div><div><span class="tabler-proposal-avatar tabler-proposal-avatar-sm">A</span><div><strong>Antonella</strong><small>Miembro</small></div><span class="badge bg-secondary">Member</span></div></div></div>'],
                ],
            ],
        ],
    ],
    [
        'title' => 'Visualizaci&oacute;n',
        'description' => 'Mini gr&aacute;ficos y barras para reportes.',
        'items' => [
            ['name' => 'Barras mensuales', 'hint' => 'Comparaci&oacute;n r&aacute;pida por mes.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-title">Gasto mensual</div><div class="tabler-proposal-bars"><span style="height: 46%;"></span><span style="height: 72%;"></span><span style="height: 58%;"></span><span style="height: 88%;"></span><span style="height: 62%;"></span></div><div class="tabler-proposal-row"><small class="text-muted">Abr</small><small class="text-muted">Ago</small></div></div>'],
            ['name' => 'Progreso de grupo', 'hint' => 'Pagado vs total.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Tu aporte</div><div class="text-muted small">' . moneda(860000) . ' de ' . moneda(1563723) . '</div></div><strong>55%</strong></div><div class="tabler-proposal-meter"><span style="width: 55%;"></span></div><div class="tabler-proposal-filter-row"><span>Bajo</span><span>Medio</span><span>Alto</span></div></div>'],
            ['name' => 'Dona simple', 'hint' => 'Distribuci&oacute;n de categor&iacute;as.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-donut-wrap"><div class="tabler-proposal-donut"><strong>72%</strong></div><div><div class="tabler-proposal-title">Otros</div><div class="text-muted small">Categor&iacute;a principal</div><div class="tabler-proposal-filter-row mt-2"><span>Otros</span><span>Pagos</span></div></div></div>'],
        ],
    ],
    [
        'title' => 'Home operativo',
        'description' => 'Variantes para abrir la app y cargar gastos con pocos toques.',
        'items' => [
            ['name' => 'Grupo acceso directo', 'hint' => 'Card con CTA dominante.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-home-card"><div class="tabler-proposal-row align-items-start"><div><div class="tabler-proposal-title">Junio</div><div class="text-muted small">Activo &middot; 2 integrantes</div></div><span class="badge bg-success">Activo</span></div><div class="tabler-proposal-home-total">' . moneda(25000) . '</div><div class="tabler-proposal-actions tabler-proposal-actions-large"><button class="btn btn-outline-primary" type="button">Entrar</button><button class="btn btn-primary" type="button">+ Gasto</button></div></div>'],
            ['name' => 'Grupo con deuda visible', 'hint' => 'Prioriza saldo antes de acciones.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-danger-soft"><div class="tabler-proposal-row align-items-start"><span class="tabler-proposal-avatar">J</span><div class="flex-grow-1"><div class="tabler-proposal-title">Junio</div><div class="text-muted small">Saldo actual</div><strong class="text-danger">' . moneda(112697) . ' deb&eacute;s</strong></div></div><button class="btn btn-danger btn-sm w-100" type="button">Resolver deuda</button></div>'],
            ['name' => 'Grupo minimalista', 'hint' => 'Una tarjeta limpia para listas largas.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-quiet"><div class="tabler-proposal-row"><div><strong>Junio</strong><div class="text-muted small">Ultimo movimiento: hoy</div></div><b class="text-success">' . moneda(5000) . '</b></div><div class="tabler-proposal-row"><button class="btn btn-light btn-sm" type="button">Ver</button><button class="btn btn-primary btn-sm" type="button">Agregar</button></div></div>'],
        ],
    ],
    [
        'title' => 'Detalle de grupo',
        'description' => 'Bloques para cabecera, resumen y estado del grupo.',
        'items' => [
            ['name' => 'Cabecera balance', 'hint' => 'Resumen visual compacto.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row align-items-start"><span class="tabler-proposal-avatar">M</span><div><div class="tabler-proposal-title">Mayo</div><span class="badge bg-success">Activo</span></div><button class="btn btn-light btn-sm ms-auto" type="button">Config.</button></div><div class="tabler-proposal-copy tabler-proposal-copy-center"><span>Tu balance</span><strong class="text-success">' . moneda(130920) . ' a favor</strong></div></div>'],
            ['name' => 'Resumen doble', 'hint' => 'Total y tu parte juntos.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-grid-2"><div><span>Total grupo</span><strong>' . moneda(1630862) . '</strong></div><div><span>Tu parte</span><strong>' . moneda(815431) . '</strong></div></div><div class="tabler-proposal-meter mt-2"><span style="width: 51%;"></span></div><div class="text-muted small">Participaci&oacute;n equilibrada</div></div>'],
            ['name' => 'Estado liquidado', 'hint' => 'Grupo cerrado o sin saldo.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-success"><div class="tabler-proposal-row"><span class="tabler-proposal-icon tabler-proposal-icon-green">&check;</span><div><div class="tabler-proposal-title">Grupo liquidado</div><div class="text-muted small">No quedan pagos pendientes</div></div></div><button class="btn btn-outline-success btn-sm w-100" type="button">Ver movimientos</button></div>'],
        ],
    ],
    [
        'title' => 'Movimientos avanzados',
        'description' => 'Patrones m&aacute;s visuales para gastos y pagos.',
        'items' => [
            ['name' => 'Movimiento por color', 'hint' => 'Diferencia pagador visualmente.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-movement tabler-proposal-user-a"><div><span class="badge bg-primary">Gasto</span><strong>Supermercado</strong><small>Antonella &middot; 20/06</small></div><b>' . moneda(59300) . '</b></div><div class="tabler-proposal-movement tabler-proposal-user-f"><div><span class="badge bg-primary">Gasto</span><strong>Garrafa</strong><small>Fernando &middot; 21/06</small></div><b>' . moneda(57000) . '</b></div></div>'],
            ['name' => 'Timeline vertical', 'hint' => 'Secuencia por fecha.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-timeline"><div><span></span><strong>Gasto creado</strong><small>10:20 &middot; Fernando</small></div><div><span></span><strong>Pago cargado</strong><small>14:05 &middot; Antonella</small></div><div><span></span><strong>Grupo liquidado</strong><small>18:30 &middot; Sistema</small></div></div></div>'],
            ['name' => 'Movimiento con acciones', 'hint' => 'Fila con ABM r&aacute;pido.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><span class="badge bg-primary me-1">Gasto</span><strong>Farmacia</strong><div class="text-muted small">Otros &middot; 2 participantes</div></div><strong>' . moneda(9200) . '</strong></div><div class="tabler-proposal-actions"><button class="btn btn-light btn-sm" type="button">Ver</button><button class="btn btn-light btn-sm" type="button">Editar</button><button class="btn btn-outline-danger btn-sm" type="button">Borrar</button></div></div>'],
        ],
    ],
    [
        'title' => 'Pagos',
        'description' => 'Opciones para pagos pendientes, confirmados y sugeridos.',
        'items' => [
            ['name' => 'Pago pendiente', 'hint' => 'Rojo suave con acci&oacute;n.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-danger-soft"><div class="tabler-proposal-row"><div><span class="badge bg-danger">Pendiente</span><strong class="d-block mt-2">Pagar a Fernando</strong><small class="text-muted">Junio &middot; Transferencia</small></div><b class="text-danger">' . moneda(112697) . '</b></div><button class="btn btn-danger btn-sm w-100" type="button">Registrar pago</button></div>'],
            ['name' => 'Pago recibido', 'hint' => 'Confirmaci&oacute;n verde.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-success"><div class="tabler-proposal-row"><span class="tabler-proposal-icon tabler-proposal-icon-green">$</span><div><strong>Pago recibido</strong><div class="text-muted small">Antonella pag&oacute; su parte</div></div><b class="text-success">' . moneda(14570) . '</b></div></div>'],
            ['name' => 'Sugerencia de pago', 'hint' => 'Indica la transferencia ideal.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-route"><span>Antonella</span><b>&rarr;</b><span>Fernando</span></div><div class="tabler-proposal-copy"><span>Importe sugerido</span><strong class="text-primary">' . moneda(116351) . '</strong></div><button class="btn btn-primary btn-sm w-100" type="button">Usar este pago</button></div>'],
        ],
    ],
    [
        'title' => 'Reportes',
        'description' => 'Cards para lectura de datos y exportaci&oacute;n.',
        'items' => [
            ['name' => 'Resumen mensual', 'hint' => 'Cuatro KPIs en bloque.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-grid-2"><div><span>Total</span><strong>' . moneda(1563723) . '</strong></div><div><span>Pagaste</span><strong class="text-success">' . moneda(898212) . '</strong></div><div><span>Tu parte</span><strong>' . moneda(781862) . '</strong></div><div><span>Saldo</span><strong class="text-success">' . moneda(0) . '</strong></div></div></div>'],
            ['name' => 'Export panel', 'hint' => 'Reportes con PDF y Excel.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Abril 2026</div><div class="text-muted small">41 movimientos filtrados</div></div><strong>' . moneda(1563723) . '</strong></div><div class="tabler-proposal-actions"><button class="btn btn-danger btn-sm" type="button">PDF</button><button class="btn btn-success btn-sm" type="button">Excel</button></div></div>'],
            ['name' => 'Categoria destacada', 'hint' => 'Ranking con barra.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><strong>Otros</strong><span>58 gastos</span></div><div class="tabler-proposal-meter tabler-proposal-meter-gold"><span style="width: 86%;"></span></div><div class="tabler-proposal-row"><small class="text-muted">Categoria principal</small><b>' . moneda(1630862) . '</b></div></div>'],
        ],
    ],
    [
        'title' => 'Formularios de gasto',
        'description' => 'Campos y controles para crear o editar gastos.',
        'items' => [
            ['name' => 'Monto destacado', 'hint' => 'Importe grande y claro.', 'render' => static fn () => '<div class="tabler-proposal-card"><label class="tabler-proposal-label">Monto total</label><div class="tabler-proposal-money-input"><span>$</span><strong>10.000,00</strong><small>ARS</small></div><label class="tabler-proposal-label">Descripci&oacute;n</label><div class="tabler-proposal-input"><span>Gasto</span></div></div>'],
            ['name' => 'Pagador selector', 'hint' => 'Control clickeable completo.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-payer"><span class="tabler-proposal-icon tabler-proposal-icon-blue">$</span><div><small>Pagado por</small><strong>Fernando</strong></div><span class="text-primary fw-bold">Cambiar</span></div>'],
            ['name' => 'Division simple', 'hint' => 'Regla de reparto visible.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-choice"><div><strong>Dividido en partes iguales</strong><small>Fernando y Antonella pagan 50/50</small></div><span>&rsaquo;</span></div><div class="tabler-proposal-split mt-2"><span>Fernando <b>' . moneda(5000) . '</b></span><span>Antonella <b>' . moneda(5000) . '</b></span></div></div>'],
        ],
    ],
    [
        'title' => 'Medios de cobro premium',
        'description' => 'Variantes de credencial, datos copiables y estado favorito.',
        'items' => [
            ['name' => 'Credencial compacta', 'hint' => 'Tarjeta azul tipo bancaria.', 'render' => static fn () => '<div class="tabler-proposal-bank-card"><div class="tabler-proposal-row"><strong>Mercado Pago</strong><span>&starf;</span></div><small>TITULAR</small><b>Fernando Montes de Oca</b><small>ALIAS</small><b>fernando.montesdeoca</b></div>'],
            ['name' => 'Medio con engranaje', 'hint' => 'Acceso directo a edici&oacute;n.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><div class="tabler-proposal-title">Banco Galicia</div><span class="badge bg-success">Activo</span></div><button class="btn btn-light btn-sm" type="button">&#9881;</button></div><div class="tabler-proposal-copy"><span>CBU/CVU</span><strong>0000003100012345678901</strong></div></div>'],
            ['name' => 'Datos copiables', 'hint' => 'Alias y CBU en filas.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-copy"><span>Alias</span><strong>fer.mercadopago</strong></div><div class="tabler-proposal-copy"><span>CBU/CVU</span><strong>0000003100012345678901</strong></div><button class="btn btn-outline-primary btn-sm w-100" type="button">Copiar datos</button></div>'],
        ],
    ],
    [
        'title' => 'Administraci&oacute;n',
        'description' => 'Componentes para pantallas internas y cat&aacute;logo.',
        'items' => [
            ['name' => 'Selector de pantalla', 'hint' => 'Entrada al cat&aacute;logo por pantalla.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-row"><div><strong>Home</strong><div class="text-muted small">Grupos activos y deudas</div></div><span class="badge bg-primary-subtle text-primary">2 componentes</span></div><button class="btn btn-primary btn-sm w-100" type="button">Abrir pantalla</button></div>'],
            ['name' => 'Componente elegible', 'hint' => 'Selecci&oacute;n &uacute;nica.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-selected"><div class="tabler-proposal-row"><div><strong>Card principal</strong><div class="text-muted small">Usada en Home</div></div><span class="badge bg-success">Activo</span></div><button class="btn btn-outline-primary btn-sm w-100" type="button">Usar dise&ntilde;o</button></div>'],
            ['name' => 'Propuesta aislada', 'hint' => 'No conectada a pantallas.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-muted"><div class="tabler-proposal-title">Dial experimental</div><div class="text-muted small">Solo para exploraci&oacute;n visual.</div><div class="tabler-proposal-meter mt-2"><span style="width: 38%;"></span></div><span class="badge bg-secondary mt-2">Propuesta</span></div>'],
        ],
    ],
    [
        'title' => 'Navegaci&oacute;n mobile',
        'description' => 'Patrones para men&uacute;, bottom nav y acciones flotantes.',
        'items' => [
            ['name' => 'Bottom nav', 'hint' => 'Navegaci&oacute;n inferior compacta.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-bottom-nav"><span>Home</span><span>Reportes</span><span>Perfil</span><span>Men&uacute;</span></div></div>'],
            ['name' => 'FAB gasto', 'hint' => 'Acci&oacute;n principal flotante.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-fab-stage"><button class="btn btn-success tabler-proposal-fab" type="button">+ Nuevo gasto</button></div>'],
            ['name' => 'Menu item', 'hint' => 'Fila de navegaci&oacute;n con icono.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-menu-item"><span class="tabler-proposal-icon tabler-proposal-icon-blue">R</span><div><strong>Reportes</strong><small>Resumen mensual</small></div><b>&rsaquo;</b></div><div class="tabler-proposal-menu-item tabler-proposal-danger-soft"><span class="tabler-proposal-icon">S</span><div><strong>Cerrar sesi&oacute;n</strong><small>Salir de la cuenta</small></div><b>&rsaquo;</b></div></div>'],
        ],
    ],
    [
        'title' => 'Veloc&iacute;metros',
        'description' => 'Variantes extra para medir gasto propio sobre total del grupo.',
        'items' => [
            ['name' => 'Dial Tabler compacto', 'hint' => 'Aguja y escala simple.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-gauge"><span style="--rotation: 42deg;"></span><strong>55%</strong></div><div class="tabler-proposal-row"><small>' . moneda(0) . '</small><small>' . moneda(1563723) . '</small></div></div>'],
            ['name' => 'Gauge por zonas', 'hint' => 'Verde, amarillo y rojo.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-zone-gauge"><span style="--rotation: 68deg;"></span></div><div class="tabler-proposal-row"><strong>' . moneda(898212) . '</strong><small class="text-muted">de ' . moneda(1563723) . '</small></div></div>'],
            ['name' => 'Escala con ticks', 'hint' => 'Valores visibles en la escala.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-tick-scale"><span></span><span></span><span></span><span></span><b style="left: 58%;"></b></div><div class="tabler-proposal-row"><small>0</small><small>50%</small><small>100%</small></div><strong class="text-primary">' . moneda(898212) . '</strong></div>'],
        ],
    ],
    [
        'title' => 'Microinteracciones',
        'description' => 'Estados visuales para hover, loading y confirmaci&oacute;n.',
        'items' => [
            ['name' => 'Skeleton card', 'hint' => 'Carga inicial.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-skeleton w-75"></div><div class="tabler-proposal-skeleton w-100"></div><div class="tabler-proposal-skeleton w-50"></div></div>'],
            ['name' => 'Confirmaci&oacute;n inline', 'hint' => 'Estado guardado sin modal.', 'render' => static fn () => '<div class="tabler-proposal-card"><div class="tabler-proposal-alert tabler-proposal-alert-success"><strong>Listo</strong><span>El cambio qued&oacute; guardado.</span></div><button class="btn btn-outline-success btn-sm w-100" type="button">Continuar</button></div>'],
            ['name' => 'Acci&oacute;n destructiva', 'hint' => 'Rojo suave y seguro.', 'render' => static fn () => '<div class="tabler-proposal-card tabler-proposal-danger-soft"><strong>Eliminar registro</strong><div class="text-muted small">Esta acci&oacute;n requiere confirmaci&oacute;n.</div><button class="btn btn-outline-danger btn-sm w-100" type="button">Eliminar</button></div>'],
        ],
    ],
];

$proposals = [
    [
        'title' => 'Grupo cerrado',
        'description' => 'Opciones visuales para historial de grupos. Todav&iacute;a no est&aacute;n conectadas al selector.',
        'items' => [
            ['name' => 'Hist&oacute;rica', 'hint' => 'Contexto de cierre.', 'render' => static fn () => '<div class="dash-card catalog-preview-card catalog-muted-card"><div class="dash-card-body"><div class="catalog-row"><div class="catalog-avatar catalog-avatar-muted">A</div><div class="min-width-0 flex-grow-1"><div class="catalog-title-row"><strong>Abril</strong><span class="badge bg-warning text-dark">Cerrado</span></div><div class="text-muted small">Cerrado el 30/04/2026</div><div class="small mt-2">Saldo final: <span class="financial-amount text-muted">' . moneda(0) . '</span></div></div></div></div></div>'],
            ['name' => 'Archivo', 'hint' => 'Neutral y sobria.', 'render' => static fn () => '<div class="dash-card catalog-preview-card catalog-archive-card"><div class="dash-card-body"><div class="d-flex justify-content-between align-items-start gap-2"><div><strong>Abril</strong><div class="text-muted small">41 movimientos guardados</div></div><span class="badge bg-secondary">Archivo</span></div><button class="btn btn-outline-secondary btn-sm w-100 mt-3" type="button">Ver historial</button></div></div>'],
            ['name' => 'Liquidada', 'hint' => 'Sin pendientes.', 'render' => static fn () => '<div class="dash-card catalog-preview-card catalog-settled-card"><div class="dash-card-body text-center"><div class="catalog-status-circle catalog-status-ok mx-auto mb-2">&check;</div><strong>Abril liquidado</strong><div class="text-muted small">No quedan deudas pendientes.</div></div></div>'],
        ],
    ],
    [
        'title' => 'Gasto',
        'description' => 'Propuestas para gasto gen&eacute;rico. Hoy las pantallas reales todav&iacute;a usan sus propios listados.',
        'items' => [
            ['name' => 'Movimiento azul', 'hint' => 'Patr&oacute;n actual.', 'render' => static fn () => '<div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card report-movement-expense catalog-border-primary"><div class="catalog-card-top"><div><span class="badge bg-primary me-1">Gasto</span><span class="fw-medium small">Supermercado mensual</span></div><span class="fw-bold small text-primary">' . moneda(45200) . '</span></div><div class="text-muted small mt-1">' . $fechaCorta . ' &middot; Fernando</div><div class="text-muted small"><span class="badge bg-light text-dark">Supermercado</span> Grupo: Mayo &middot; 2 part.</div></div></a></div>'],
            ['name' => 'Pagador protagonista', 'hint' => 'Color por usuario.', 'render' => static fn () => '<div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card catalog-user-fer"><div class="catalog-card-top"><div class="catalog-row"><div class="catalog-avatar catalog-avatar-primary catalog-avatar-xs">F</div><div><span class="badge bg-primary me-1">Gasto</span><span class="fw-medium small">Garrafa</span></div></div><span class="fw-bold small text-primary">' . moneda(57000) . '</span></div><div class="text-muted small mt-1">' . $fechaCorta . ' &middot; Pag&oacute; Fernando</div></div></a></div>'],
            ['name' => 'Recibo compacto', 'hint' => 'Datos en chips.', 'render' => static fn () => '<div class="dash-card catalog-preview-card"><div class="dash-card-body"><div class="catalog-card-top"><strong>Diferencia</strong><span class="financial-amount text-primary">' . moneda(193701) . '</span></div><div class="catalog-chip-row mt-2"><span class="badge bg-primary">Gasto</span><span class="badge bg-light text-dark">Otros</span><span class="badge bg-light text-dark">2 part.</span></div><div class="text-muted small mt-2">01/05/2026 &middot; Fernando &middot; Mayo</div></div></div>'],
        ],
    ],
    [
        'title' => 'Pago',
        'description' => 'Propuestas para pagos y comprobantes. A&uacute;n no est&aacute;n unificadas como componente elegible.',
        'items' => [
            ['name' => 'Movimiento verde', 'hint' => 'Patr&oacute;n actual.', 'render' => static fn () => '<div class="report-movement-list catalog-list-preview"><a href="#" class="report-movement-link"><div class="report-movement-card report-movement-payment catalog-border-success"><div class="catalog-card-top"><div><span class="badge bg-success me-1">Pago</span><span class="fw-medium small">Pago</span></div><span class="fw-bold small text-success">' . moneda(30000) . '</span></div><div class="text-muted small mt-1">18/04/2026 &middot; Fernando pag&oacute; a Antonella</div><div class="text-muted small">Grupo: Mayo</div></div></a></div>'],
            ['name' => 'Transferencia', 'hint' => 'De una persona a otra.', 'render' => static fn () => '<div class="dash-card catalog-preview-card"><div class="dash-card-body"><div class="catalog-transfer"><div><div class="catalog-avatar catalog-avatar-primary catalog-avatar-sm">F</div><small>Fernando</small></div><div class="catalog-transfer-arrow">&rarr;</div><div><div class="catalog-avatar catalog-avatar-success catalog-avatar-sm">A</div><small>Antonella</small></div></div><div class="catalog-balance-amount text-success text-center mt-2">' . moneda(30000) . '</div></div></div>'],
        ],
    ],
];

$activeScreenMeta = $screens[$activeScreen] ?? null;

$sectionSlug = static function (string $title): string {
    $raw = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $raw = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $raw) ?: strtolower($raw);
    $raw = preg_replace('/[^a-z0-9]+/', '-', $raw) ?? $raw;
    return trim($raw, '-');
};

$tablerSectionsBySlug = [];
foreach ($tablerCatalog as $section) {
    $slug = $sectionSlug($section['title']);
    $tablerSectionsBySlug[$slug] = $section;
}

$activeTablerSlug = (string) (service('request')->getGet('seccion') ?? '');
$activeTablerSection = $tablerSectionsBySlug[$activeTablerSlug] ?? null;
$tablerItemCount = static function (array $section): int {
    if (!isset($section['groups'])) {
        return count($section['items'] ?? []);
    }

    $total = 0;
    foreach ($section['groups'] as $group) {
        $total += count($group['items'] ?? []);
    }

    return $total;
};
?>

<div class="container catalog-page mt-3 mt-md-4 pb-4">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="catalog-hero mb-2">
        <div>
            <p class="catalog-kicker mb-1">Administraci&oacute;n visual</p>
            <?php if ($activeScreenMeta): ?>
                <h2 class="fw-bold mb-1"><?= $activeScreenMeta['title'] ?></h2>
                <p class="text-muted mb-0"><?= $activeScreenMeta['description'] ?></p>
            <?php else: ?>
                <h2 class="fw-bold mb-1">Cat&aacute;logo por pantalla</h2>
                <p class="text-muted mb-0">Eleg&iacute; una pantalla para abrir sus componentes disponibles. Cada componente permite activar un solo dise&ntilde;o a la vez.</p>
            <?php endif; ?>
        </div>
        <?php if ($activeScreenMeta): ?>
            <a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/catalogo-tarjetas') ?>">Volver al cat&aacute;logo</a>
        <?php else: ?>
            <span class="badge bg-primary-subtle text-primary catalog-count"><?= count($screens) ?> pantallas</span>
        <?php endif; ?>
    </div>


<?php if ($activeScreen === ''): ?>
        <section class="catalog-section">
            <div class="catalog-section-head">
                <div>
                    <h5>Pantallas</h5>
                    <p>Entr&aacute; a una pantalla para elegir qu&eacute; dise&ntilde;o usa cada componente configurable.</p>
                </div>
                <span class="badge bg-primary">Cat&aacute;logo</span>
            </div>
            <div class="catalog-screen-grid">
                <?php foreach ($screens as $key => $screen): ?>
                    <a class="catalog-screen-card" href="<?= base_url('admin/catalogo-tarjetas/' . rawurlencode($key)) ?>">
                        <div>
                            <strong><?= $screen['title'] ?></strong>
                            <span><?= $screen['description'] ?></span>
                        </div>
                        <small><?= $screen['badge'] ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php elseif ($activeScreen === 'login'): ?>
        <section class="catalog-section">
            <div class="catalog-section-head">
                <div>
                    <h5>Pantallas de login</h5>
                    <p>Propuestas visuales para evaluar antes de aplicar cambios al inicio de sesi&oacute;n real.</p>
                </div>
                <span class="badge bg-secondary">Propuesta</span>
            </div>
            <div class="catalog-grid catalog-login-grid">
                <?php foreach ($loginProposals as $proposal): ?>
                    <?php
                        $designId = $catalogDesignId('login', 'Pantallas de login', $proposal['name']);
                        $itemKey = $catalogItemKey('login', 'login', null, $proposal['name']);
                        $decision = $catalogDecision('login', 'login', null, $itemKey);
                        $isDiscarded = ($decision['decision'] ?? '') === 'discard';
                    ?>
                    <article class="catalog-variant catalog-curation-item <?= $isDiscarded ? 'catalog-variant-discarded' : '' ?>" data-catalog-design-id="<?= esc($designId, 'attr') ?>" data-catalog-design-name="<?= esc($proposal['name'], 'attr') ?>" data-catalog-design-group="Pantallas de login" data-catalog-in-use="0">
                        <?php if ($isDiscarded): ?>
                            <div class="catalog-discarded-badge">Descartado</div>
                        <?php endif; ?>
                        <div class="catalog-variant-meta">
                            <span><?= $proposal['name'] ?></span>
                            <small><?= $proposal['hint'] ?></small>
                        </div>
                        <div class="catalog-source-badge">Login</div>
                        <?= $proposal['render']() ?>
                        <?= $candidateActions('login', 'login', null, $itemKey, $proposal['name'], $proposal['hint'], 'Login', $decision) ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php elseif ($activeScreen === 'tabler'): ?>
        <?php if ($activeTablerSlug === ''): ?>
            <section class="catalog-section">
                <div class="catalog-section-head">
                    <div>
                        <h5>Categor&iacute;as Tabler</h5>
                        <p>Entr&aacute; a una categor&iacute;a para ver sus ejemplos disponibles.</p>
                    </div>
                    <span class="badge bg-primary">Tabler</span>
                </div>
                <div class="catalog-screen-grid">
                    <?php foreach ($tablerCatalog as $section): ?>
                        <?php $slug = $sectionSlug($section['title']); ?>
                        <a class="catalog-screen-card" href="<?= base_url('admin/catalogo-tarjetas/tabler') . '?seccion=' . rawurlencode($slug) ?>">
                            <div>
                                <strong><?= $section['title'] ?></strong>
                                <span><?= $section['description'] ?></span>
                            </div>
                            <small><?= $tablerItemCount($section) ?> ejemplo(s)</small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <?php $section = $activeTablerSection; ?>
            <?php if (!$section): ?>
                <section class="catalog-section">
                    <div class="catalog-section-head">
                        <div>
                            <h5>Categor&iacute;a no encontrada</h5>
                            <p>Volv&eacute; al &iacute;ndice Tabler para elegir una categor&iacute;a disponible.</p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/catalogo-tarjetas/tabler') ?>">Volver a Tabler</a>
                    </div>
                </section>
            <?php else: ?>
                <section class="catalog-section">
                    <div class="catalog-section-head">
                        <div>
                            <h5><?= $section['title'] ?></h5>
                            <p><?= $section['description'] ?></p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="<?= base_url('admin/catalogo-tarjetas/tabler') ?>">Volver a Tabler</a>
                    </div>
                    <?php if (isset($section['groups'])): ?>
                        <div class="catalog-nested-groups">
                            <?php foreach ($section['groups'] as $group): ?>
                                <div class="catalog-nested-section">
                                    <div class="catalog-nested-head">
                                        <div>
                                            <h6><?= $group['title'] ?></h6>
                                            <p><?= $group['description'] ?></p>
                                        </div>
                                        <span><?= count($group['items']) ?> ejemplo(s)</span>
                                    </div>
                                    <div class="catalog-grid">
                                        <?php foreach ($group['items'] as $item): ?>
                                            <?php
                                                $designId = $catalogDesignId('tabler', $group['title'], $item['name']);
                                                $sectionKey = $activeTablerSlug;
                                                $groupKey = $group['title'];
                                                $itemKey = $catalogItemKey('tabler', $sectionKey, $groupKey, $item['name']);
                                                $decision = $catalogDecision('tabler', $sectionKey, $groupKey, $itemKey);
                                                $isDiscarded = ($decision['decision'] ?? '') === 'discard';
                                            ?>
                                            <article class="catalog-variant catalog-curation-item <?= $isDiscarded ? 'catalog-variant-discarded' : '' ?>" data-catalog-design-id="<?= esc($designId, 'attr') ?>" data-catalog-design-name="<?= esc($item['name'], 'attr') ?>" data-catalog-design-group="<?= esc($group['title'], 'attr') ?>" data-catalog-in-use="0">
                                                <?php if ($isDiscarded): ?>
                                                    <div class="catalog-discarded-badge">Descartado</div>
                                                <?php endif; ?>
                                                <div class="catalog-variant-meta">
                                                    <span><?= $item['name'] ?></span>
                                                    <small><?= $item['hint'] ?></small>
                                                </div>
                                                <div class="catalog-source-badge">Ejemplo Tabler</div>
                                                <?= $item['render']() ?>
                                                <?= $candidateActions('tabler', $sectionKey, $groupKey, $itemKey, $item['name'], $item['hint'], 'Ejemplo Tabler', $decision) ?>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="catalog-grid">
                            <?php foreach ($section['items'] as $item): ?>
                                <?php
                                    $designId = $catalogDesignId('tabler', $section['title'], $item['name']);
                                    $sectionKey = $activeTablerSlug;
                                    $itemKey = $catalogItemKey('tabler', $sectionKey, null, $item['name']);
                                    $decision = $catalogDecision('tabler', $sectionKey, null, $itemKey);
                                    $isDiscarded = ($decision['decision'] ?? '') === 'discard';
                                ?>
                                <article class="catalog-variant catalog-curation-item <?= $isDiscarded ? 'catalog-variant-discarded' : '' ?>" data-catalog-design-id="<?= esc($designId, 'attr') ?>" data-catalog-design-name="<?= esc($item['name'], 'attr') ?>" data-catalog-design-group="<?= esc($section['title'], 'attr') ?>" data-catalog-in-use="0">
                                    <?php if ($isDiscarded): ?>
                                        <div class="catalog-discarded-badge">Descartado</div>
                                    <?php endif; ?>
                                    <div class="catalog-variant-meta">
                                        <span><?= $item['name'] ?></span>
                                        <small><?= $item['hint'] ?></small>
                                    </div>
                                    <div class="catalog-source-badge">Ejemplo Tabler</div>
                                    <?= $item['render']() ?>
                                    <?= $candidateActions('tabler', $sectionKey, null, $itemKey, $item['name'], $item['hint'], 'Ejemplo Tabler', $decision) ?>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif ($activeScreen === 'propuestas'): ?>
        <?php foreach ($proposals as $proposal): ?>
            <section class="catalog-section">
                <div class="catalog-section-head">
                    <div>
                        <h5><?= $proposal['title'] ?></h5>
                        <p><?= $proposal['description'] ?></p>
                    </div>
                    <span class="badge bg-secondary">Propuesta</span>
                </div>
                <div class="catalog-grid">
                    <?php foreach ($proposal['items'] as $item): ?>
                        <?php
                            $designId = $catalogDesignId('propuestas', $proposal['title'], $item['name']);
                            $sectionKey = $proposal['title'];
                            $sourceLabel = $proposal['label'] ?? 'Propuesta';
                            $itemKey = $catalogItemKey('propuestas', $sectionKey, null, $item['name']);
                            $decision = $catalogDecision('propuestas', $sectionKey, null, $itemKey);
                            $isDiscarded = ($decision['decision'] ?? '') === 'discard';
                        ?>
                        <article class="catalog-variant catalog-curation-item <?= $isDiscarded ? 'catalog-variant-discarded' : '' ?>" data-catalog-design-id="<?= esc($designId, 'attr') ?>" data-catalog-design-name="<?= esc($item['name'], 'attr') ?>" data-catalog-design-group="<?= esc($proposal['title'], 'attr') ?>" data-catalog-in-use="0">
                            <?php if ($isDiscarded): ?>
                                <div class="catalog-discarded-badge">Descartado</div>
                            <?php endif; ?>
                            <div class="catalog-variant-meta">
                                <span><?= $item['name'] ?></span>
                                <small><?= $item['hint'] ?></small>
                            </div>
                            <?php if (!empty($proposal['label'])): ?>
                                <div class="catalog-source-badge"><?= $proposal['label'] ?></div>
                            <?php endif; ?>
                            <?= $item['render']() ?>
                            <?= $candidateActions('propuestas', $sectionKey, null, $itemKey, $item['name'], $item['hint'], $sourceLabel, $decision) ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php elseif (isset($catalog[$activeScreen])): ?>
        <?php foreach ($catalog[$activeScreen] as $component): ?>
            <section class="catalog-section">
                <div class="catalog-section-head">
                    <div>
                        <h5><?= $component['title'] ?></h5>
                        <p><?= $component['description'] ?></p>
                    </div>
                    <span class="badge bg-success">1 activo</span>
                </div>
                <div class="catalog-grid">
                    <?php foreach ($component['variants'] as $variant): ?>
                        <?php
                            $isSelected = $component['selected'] === $variant['key'];
                            $designId = $catalogDesignId('componente', $component['component'], $variant['name'], $variant['key']);
                            $itemKey = $catalogItemKey('catalog', $component['screen'], $component['component'], $variant['key']);
                            $decision = $catalogDecision('catalog', $component['screen'], $component['component'], $itemKey);
                            $isDiscarded = ($decision['decision'] ?? '') === 'discard';
                        ?>
                        <article class="catalog-variant catalog-curation-item <?= $isSelected ? 'catalog-variant-selected' : '' ?> <?= $isDiscarded ? 'catalog-variant-discarded' : '' ?>" data-catalog-design-id="<?= esc($designId, 'attr') ?>" data-catalog-design-name="<?= esc($variant['name'], 'attr') ?>" data-catalog-design-group="<?= esc($component['title'], 'attr') ?>" data-catalog-in-use="<?= $isSelected ? '1' : '0' ?>">
                            <?php if ($isDiscarded): ?>
                                <div class="catalog-discarded-badge">Descartado</div>
                            <?php endif; ?>
                            <div class="catalog-variant-meta">
                                <span><?= $variant['name'] ?></span>
                                <small><?= $variant['hint'] ?></small>
                            </div>
                            <?= $variant['render']() ?>
                            <?= $variantAction($component['screen'], $component['component'], $variant['key'], $component['selected'], $activeScreen) ?>
                            <?= $candidateActions('catalog', $component['screen'], $component['component'], $itemKey, $variant['name'], $variant['hint'], 'Componente elegible', $decision, $variant['key']) ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php else: ?>
        <section class="catalog-section">
            <div class="catalog-empty-state">
                <strong>La pantalla seleccionada no existe.</strong>
                <span>Volv&eacute; al listado y eleg&iacute; una pantalla disponible.</span>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
(function() {
    document.querySelectorAll('.catalog-redesign-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = this.closest('form');
            var panel = form.querySelector('.catalog-redesign-panel');
            if (!panel) return;
            if (panel.style.display === 'none') {
                panel.style.display = '';
                var ta = panel.querySelector('.catalog-redesign-notes');
                if (ta) ta.focus();
            } else {
                panel.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('[name="decision"][value="implement"], [name="decision"][value="discard"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var form = this.closest('form');
            var panel = form.querySelector('.catalog-redesign-panel');
            if (panel) panel.style.display = 'none';
        });
    });
})();
</script>
<script>
(function() {
    var saveUrl = '<?= base_url('admin/catalogo-tarjetas/curaduria') ?>';
    var clearUrl = '<?= base_url('admin/catalogo-tarjetas/curaduria/limpiar') ?>';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var items = Array.prototype.slice.call(document.querySelectorAll('[data-catalog-design-id]'));
    var state = <?= json_encode($catalogCurationState, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '{}' ?>;

    function getItemState(id) {
        if (!state[id]) state[id] = {};
        return state[id];
    }

    function cleanEmpty(id) {
        if (!state[id]) return;
        if (!state[id].status && !state[id].redesignNote) delete state[id];
    }

    function request(url, payload) {
        payload = payload || {};
        payload[csrfName] = csrfHash;

        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(payload).toString()
        }).then(function(response) {
            return response.json().catch(function() {
                return { ok: false, message: 'Respuesta invalida del servidor.' };
            }).then(function(data) {
                if (data.csrf) csrfHash = data.csrf;
                if (!response.ok || !data.ok) {
                    throw new Error(data.message || 'No se pudo guardar la marca.');
                }
                return data;
            });
        });
    }

    function setSaving(item, saving) {
        if (saving) {
            item.dataset.catalogSaving = '1';
        } else {
            delete item.dataset.catalogSaving;
        }
        item.querySelectorAll('[data-catalog-action], [data-catalog-redesign]').forEach(function(el) {
            el.disabled = saving;
        });
    }

    function isSaving(item) {
        return item.dataset.catalogSaving === '1';
    }

    function persistItem(item) {
        var id = item.dataset.catalogDesignId;
        var itemState = state[id] || {};
        return request(saveUrl, {
            design_id: id,
            design_name: item.dataset.catalogDesignName || '',
            design_group: item.dataset.catalogDesignGroup || '',
            status: itemState.status || '',
            redesign_note: itemState.redesignNote || ''
        });
    }

    function showSaveError(message) {
        window.alert(message || 'No se pudo guardar la marca.');
    }

    function updateCounts() {
        var selected = 0;
        var discarded = 0;
        var redesign = 0;
        Object.keys(state).forEach(function(id) {
            if (state[id].status === 'selected') selected += 1;
            if (state[id].status === 'discarded') discarded += 1;
            if (state[id].redesignNote) redesign += 1;
        });
        document.querySelectorAll('[data-catalog-count="selected"]').forEach(function(node) { node.textContent = String(selected); });
        document.querySelectorAll('[data-catalog-count="discarded"]').forEach(function(node) { node.textContent = String(discarded); });
        document.querySelectorAll('[data-catalog-count="redesign"]').forEach(function(node) { node.textContent = String(redesign); });
    }

    function applyItemState(item) {
        var id = item.dataset.catalogDesignId;
        var itemState = state[id] || {};
        var status = itemState.status || '';
        var inUse = item.dataset.catalogInUse === '1';
        if (inUse && status === 'discarded') {
            delete itemState.status;
            status = '';
            cleanEmpty(id);
        }
        item.dataset.catalogState = status;
        item.dataset.catalogRedesign = itemState.redesignNote ? '1' : '0';
        item.querySelectorAll('[data-catalog-action]').forEach(function(button) {
            var isActive = button.dataset.catalogAction === status;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        var note = item.querySelector('[data-catalog-redesign-note]');
        if (note) note.value = itemState.redesignNote || '';
    }

    function refresh() {
        items.forEach(applyItemState);
        updateCounts();
    }

    items.forEach(function(item) {
        item.querySelectorAll('[data-catalog-action]').forEach(function(button) {
            button.addEventListener('click', function() {
                if (button.disabled || isSaving(item)) return;
                var id = item.dataset.catalogDesignId;
                var action = button.dataset.catalogAction;
                var previous = Object.assign({}, state[id] || {});
                var itemState = getItemState(id);
                itemState.status = itemState.status === action ? '' : action;
                cleanEmpty(id);
                refresh();
                setSaving(item, true);
                persistItem(item).then(function() {
                    setSaving(item, false);
                }).catch(function(error) {
                    setSaving(item, false);
                    if (previous.status || previous.redesignNote) {
                        state[id] = previous;
                    } else {
                        delete state[id];
                    }
                    refresh();
                    showSaveError(error.message);
                });
            });
        });

        var redesignButton = item.querySelector('[data-catalog-redesign]');
        var redesignPanel = item.querySelector('[data-catalog-redesign-panel]');
        var redesignNote = item.querySelector('[data-catalog-redesign-note]');
        var redesignSave = item.querySelector('[data-catalog-redesign-save]');
        if (redesignButton && redesignPanel && redesignNote) {
            redesignButton.addEventListener('click', function() {
                if (isSaving(item)) return;
                redesignPanel.hidden = !redesignPanel.hidden;
                if (!redesignPanel.hidden) redesignNote.focus();
            });
        }
        if (redesignSave && redesignNote) {
            redesignSave.addEventListener('click', function() {
                if (isSaving(item)) return;
                var id = item.dataset.catalogDesignId;
                var previous = Object.assign({}, state[id] || {});
                var itemState = getItemState(id);
                itemState.redesignNote = redesignNote.value.trim();
                cleanEmpty(id);
                refresh();
                setSaving(item, true);
                persistItem(item).then(function() {
                    setSaving(item, false);
                    redesignSave.textContent = 'Guardado';
                    window.setTimeout(function() { redesignSave.textContent = 'Guardar comentario'; }, 1400);
                }).catch(function(error) {
                    setSaving(item, false);
                    if (previous.status || previous.redesignNote) {
                        state[id] = previous;
                    } else {
                        delete state[id];
                    }
                    refresh();
                    showSaveError(error.message);
                });
            });
        }
    });

    document.querySelectorAll('[data-catalog-copy]').forEach(function(copyButton) {
        copyButton.addEventListener('click', function() {
            var lines = ['Catalogo visual - curaduria'];
            [
                ['selected', 'Para implementar'],
                ['discarded', 'Descartadas'],
                ['redesign', 'Para redisenar']
            ].forEach(function(section) {
                lines.push('', section[1] + ':');
                var matches = items.filter(function(item) {
                    var itemState = state[item.dataset.catalogDesignId] || {};
                    return section[0] === 'redesign' ? !!itemState.redesignNote : itemState.status === section[0];
                });
                if (!matches.length) {
                    lines.push('- Sin marcas');
                    return;
                }
                matches.forEach(function(item) {
                    var itemState = state[item.dataset.catalogDesignId] || {};
                    var note = itemState.redesignNote ? ' -> ' + itemState.redesignNote : '';
                    lines.push('- ' + item.dataset.catalogDesignGroup + ' / ' + item.dataset.catalogDesignName + ' (' + item.dataset.catalogDesignId + ')' + note);
                });
            });
            navigator.clipboard.writeText(lines.join('\n')).then(function() {
                copyButton.textContent = 'Copiado';
                window.setTimeout(function() { copyButton.textContent = 'Copiar seleccion'; }, 1500);
            }).catch(function() {
                window.prompt('Copia la seleccion', lines.join('\n'));
            });
        });
    });

    document.querySelectorAll('[data-catalog-clear]').forEach(function(clearButton) {
        clearButton.addEventListener('click', function() {
            if (clearButton.disabled) return;
            clearButton.disabled = true;
            var previous = state;
            state = {};
            refresh();
            request(clearUrl).then(function() {
                clearButton.disabled = false;
            }).catch(function(error) {
                clearButton.disabled = false;
                state = previous;
                refresh();
                showSaveError(error.message);
            });
        });
    });

    refresh();
})();
</script>
<?= view('partials/_footer') ?>
