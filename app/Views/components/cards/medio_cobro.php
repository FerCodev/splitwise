<?php
$variant = $variant ?? 'bank_card';
$nombre = $nombre ?? 'Medio de cobro';
$activo = (bool) ($activo ?? true);
$favorito = (bool) ($favorito ?? false);
$titular = $titular ?? null;
$alias = $alias ?? null;
$cbu = $cbu ?? null;
$editUrl = $editUrl ?? '#';
$favoritoUrl = $favoritoUrl ?? null;
$statusBadge = $activo ? 'Activo' : 'Inactivo';
$statusClass = $activo ? 'bg-light text-dark' : 'bg-danger';
?>

<?php $favButton = static function () use ($favorito, $favoritoUrl): void { ?>
    <?php if ($favoritoUrl): ?>
        <form action="<?= esc($favoritoUrl, 'attr') ?>" method="post" class="d-inline flex-shrink-0">
            <?= csrf_field() ?>
            <button type="submit" class="payment-bank-fav <?= $favorito ? 'payment-bank-fav-active' : '' ?>" aria-label="<?= $favorito ? 'Quitar favorito' : 'Marcar como favorito' ?>" title="<?= $favorito ? 'Quitar favorito' : 'Marcar como favorito' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
            </button>
        </form>
    <?php else: ?>
        <button type="button" class="payment-bank-fav <?= $favorito ? 'payment-bank-fav-active' : '' ?>" aria-label="<?= $favorito ? 'Quitar favorito' : 'Marcar favorito' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
        </button>
    <?php endif; ?>
<?php }; ?>

<?php $copyLine = static function (string $label, ?string $value): void { ?>
    <?php if ($value): ?>
        <div class="payment-bank-copy">
            <span><?= esc($label) ?></span><strong class="text-truncate"><?= esc($value) ?></strong>
            <button type="button" class="copiar-icon-btn" data-copiar="<?= esc($value, 'attr') ?>" aria-label="Copiar <?= esc($label) ?>" title="Copiar <?= esc($label) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
            </button>
        </div>
    <?php endif; ?>
<?php }; ?>

<?php if ($variant === 'compact'): ?>
    <div class="dash-card catalog-preview-card catalog-payment-method <?= $activo ? '' : 'payment-bank-card-inactive' ?>">
        <div class="dash-card-body">
            <div class="catalog-card-top">
                <div class="catalog-row min-width-0">
                    <div class="catalog-payment-icon"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm1 2.5V12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6.5H1Z"/></svg></div>
                    <div class="min-width-0"><strong><?= esc($nombre) ?></strong><div class="text-muted small text-truncate"><?= $alias ? 'Alias: ' . esc($alias) : ($cbu ? 'CBU/CVU: ' . esc($cbu) : 'Sin datos cargados') ?></div></div>
                </div>
                <div class="payment-bank-controls"><?php $favButton(); ?><a href="<?= esc($editUrl, 'attr') ?>" class="payment-bank-gear" aria-label="Configurar medio de cobro" title="Configurar"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.43 12.98c.04-.32.07-.65.07-.98s-.02-.66-.07-.98l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.37 7.37 0 0 0-1.69-.98L14.5 2.42A.5.5 0 0 0 14 2h-4a.5.5 0 0 0-.5.42L9.12 5.07c-.61.24-1.18.57-1.69.98l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64l2.11 1.65c-.04.32-.07.65-.07.98s.02.66.07.98l-2.11 1.65a.5.5 0 0 0-.12.64l2 3.46c.13.22.39.31.6.22l2.49-1c.51.4 1.08.73 1.69.98l.38 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.38-2.65c.61-.24 1.18-.57 1.69-.98l2.49 1c.22.09.48 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.65Z"/></svg></a></div>
            </div>
        </div>
    </div>
<?php elseif ($variant === 'favorite'): ?>
    <div class="dash-card catalog-preview-card catalog-payment-favorite <?= $activo ? '' : 'payment-bank-card-inactive' ?>">
        <div class="dash-card-body">
            <div class="catalog-card-top"><span class="badge <?= esc($statusClass) ?>"><?= esc($statusBadge) ?></span><?php $favButton(); ?></div>
            <strong class="d-block mt-2"><?= esc($nombre) ?></strong>
            <?php $copyLine('Alias', $alias); ?>
            <?php $copyLine('CBU/CVU', $cbu); ?>
            <?php if ($titular): ?><div class="text-muted small mt-2">Titular: <?= esc($titular) ?></div><?php endif; ?>
            <a href="<?= esc($editUrl, 'attr') ?>" class="btn btn-outline-primary btn-sm w-100 mt-3">Configurar</a>
        </div>
    </div>
<?php else: ?>
    <div class="payment-bank-card <?= $activo ? '' : 'payment-bank-card-inactive' ?>">
        <div class="payment-bank-card-top">
            <div class="payment-bank-name text-truncate"><?= esc($nombre) ?></div>
            <div class="payment-bank-controls">
                <span class="badge <?= esc($statusClass) ?> flex-shrink-0"><?= esc($statusBadge) ?></span>
                <?php $favButton(); ?>
                <a href="<?= esc($editUrl, 'attr') ?>" class="payment-bank-gear" aria-label="Configurar medio de cobro" title="Configurar"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.43 12.98c.04-.32.07-.65.07-.98s-.02-.66-.07-.98l2.11-1.65a.5.5 0 0 0 .12-.64l-2-3.46a.5.5 0 0 0-.6-.22l-2.49 1a7.37 7.37 0 0 0-1.69-.98L14.5 2.42A.5.5 0 0 0 14 2h-4a.5.5 0 0 0-.5.42L9.12 5.07c-.61.24-1.18.57-1.69.98l-2.49-1a.5.5 0 0 0-.6.22l-2 3.46a.5.5 0 0 0 .12.64l2.11 1.65c-.04.32-.07.65-.07.98s.02.66.07.98l-2.11 1.65a.5.5 0 0 0-.12.64l2 3.46c.13.22.39.31.6.22l2.49-1c.51.4 1.08.73 1.69.98l.38 2.65c.04.24.25.42.5.42h4c.25 0 .46-.18.5-.42l.38-2.65c.61-.24 1.18-.57 1.69-.98l2.49 1c.22.09.48 0 .6-.22l2-3.46a.5.5 0 0 0-.12-.64l-2.11-1.65Z"/></svg></a>
            </div>
        </div>
        <div class="payment-bank-body">
            <div class="payment-bank-meta payment-bank-meta-primary">
                <?php if ($titular): ?><div><span>Titular</span><strong><?= esc($titular) ?></strong></div><?php endif; ?>
                <?php $copyLine('Alias', $alias); ?>
                <?php $copyLine('CBU/CVU', $cbu); ?>
                <?php if (!$titular && !$alias && !$cbu): ?><div><span>Datos</span><strong>Sin datos cargados</strong></div><?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
