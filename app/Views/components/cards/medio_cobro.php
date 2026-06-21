<?php
$nombre = $nombre ?? 'Medio de cobro';
$activo = (bool) ($activo ?? true);
$favorito = (bool) ($favorito ?? false);
$titular = $titular ?? null;
$alias = $alias ?? null;
$cbu = $cbu ?? null;
?>

<div class="payment-bank-card <?= $activo ? '' : 'payment-bank-card-inactive' ?>">
    <div class="payment-bank-card-top">
        <div class="payment-bank-name"><?= esc($nombre) ?></div>
        <div class="payment-bank-controls">
            <span class="badge bg-light text-dark"><?= $activo ? 'Activo' : 'Inactivo' ?></span>
            <button type="button" class="payment-bank-fav <?= $favorito ? 'payment-bank-fav-active' : '' ?>" aria-label="<?= $favorito ? 'Quitar favorito' : 'Marcar favorito' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/></svg>
            </button>
            <a href="#" class="payment-bank-gear" aria-label="Configurar medio de cobro">
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492zM5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0z"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.902 3.433 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.892 3.433-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.892-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52l-.094-.319z"/></svg>
            </a>
        </div>
    </div>
    <div class="payment-bank-body">
        <div class="payment-bank-meta payment-bank-meta-primary">
            <?php if ($titular): ?><div><span>Titular</span><strong><?= esc($titular) ?></strong></div><?php endif; ?>
            <?php foreach ([['Alias', $alias], ['CBU/CVU', $cbu]] as [$label, $value]): ?>
                <?php if ($value): ?>
                    <div class="payment-bank-copy">
                        <div><span><?= esc($label) ?></span><strong><?= esc($value) ?></strong></div>
                        <button type="button" class="copiar-icon-btn" aria-label="Copiar <?= esc($label) ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3z"/></svg>
                        </button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
