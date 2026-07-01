<?php
$variant = $variant ?? 'status_pill';
$saldo = (float) ($saldo ?? 0);
$href = $href ?? '#';
$modalTarget = $modalTarget ?? null;
$estadoTexto = $saldo > 0 ? 'Te deben' : ($saldo < 0 ? 'Debés' : 'Saldado');
$tonoClase = $saldo > 0 ? 'is-positive' : ($saldo < 0 ? 'is-negative' : 'is-settled');
$montoClase = $saldo > 0 ? 'text-success' : ($saldo < 0 ? 'text-danger' : 'text-muted');
$ariaLabel = $estadoTexto . ' ' . moneda(abs($saldo), false);
?>

<?php if ($modalTarget): ?>
<button type="button"
    class="group-personal-balance-card <?= esc($tonoClase) ?>"
    data-bs-toggle="modal"
    data-bs-target="<?= esc($modalTarget, 'attr') ?>"
    aria-label="<?= esc($ariaLabel) ?>">
<?php else: ?>
<a href="<?= esc($href, 'attr') ?>"
    class="group-personal-balance-card <?= esc($tonoClase) ?> text-decoration-none"
    aria-label="<?= esc($ariaLabel) ?>">
<?php endif; ?>
    <span class="group-personal-balance-pill">Tu balance</span>
    <span class="group-personal-balance-status"><?= esc($estadoTexto) ?></span>
    <strong class="group-personal-balance-amount <?= esc($montoClase) ?>"><?= moneda(abs($saldo)) ?></strong>
<?php if ($modalTarget): ?>
</button>
<?php else: ?>
</a>
<?php endif; ?>
