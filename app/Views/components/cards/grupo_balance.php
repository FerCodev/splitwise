<?php
$variant = $variant ?? 'status_pill';
$saldo = (float) ($saldo ?? 0);
$href = $href ?? '#';
$estadoTexto = $saldo > 0 ? 'Te deben' : ($saldo < 0 ? 'Debés' : 'Saldado');
$tonoClase = $saldo > 0 ? 'is-positive' : ($saldo < 0 ? 'is-negative' : 'is-settled');
$montoClase = $saldo > 0 ? 'text-success' : ($saldo < 0 ? 'text-danger' : 'text-muted');
$ariaLabel = $estadoTexto . ' ' . moneda(abs($saldo), false);
?>
<a href="<?= esc($href, 'attr') ?>"
    class="group-personal-balance-card <?= esc($tonoClase) ?> text-decoration-none"
    aria-label="<?= esc($ariaLabel) ?>">
    <span class="group-personal-balance-pill">Tu balance</span>
    <span class="group-personal-balance-status"><?= esc($estadoTexto) ?></span>
    <strong class="group-personal-balance-amount <?= esc($montoClase) ?>"><?= moneda(abs($saldo)) ?></strong>
</a>
