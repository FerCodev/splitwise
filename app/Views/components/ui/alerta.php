<?php
    $type = (string) ($type ?? 'info');
    $title = (string) ($title ?? '');
    $message = $message ?? null;
    $actionText = $actionText ?? null;
    $actionUrl = $actionUrl ?? null;
    $dismissible = (bool) ($dismissible ?? true);
    $allowedTypes = ['success', 'danger', 'warning', 'info', 'neutral'];
    $type = in_array($type, $allowedTypes, true) ? $type : 'info';

    $icons = [
        'success' => '&check;',
        'danger' => '!',
        'warning' => '!',
        'info' => 'i',
        'neutral' => '~',
    ];
?>

<div class="app-alert app-alert-<?= esc($type, 'attr') ?> <?= $dismissible ? 'app-alert-dismissible' : '' ?>" role="alert">
    <span class="app-alert-icon" aria-hidden="true"><?= $icons[$type] ?></span>
    <div class="app-alert-copy">
        <?php if ($title !== ''): ?>
            <strong><?= esc($title) ?></strong>
        <?php endif; ?>
        <?php if ($message): ?>
            <small><?= esc($message) ?></small>
        <?php endif; ?>
    </div>
    <?php if ($actionText && $actionUrl): ?>
        <a class="btn btn-sm app-alert-action" href="<?= esc($actionUrl, 'attr') ?>"><?= esc($actionText) ?></a>
    <?php endif; ?>
    <?php if ($dismissible): ?>
        <button type="button" class="app-alert-close" data-app-alert-close aria-label="Cerrar alerta">&times;</button>
    <?php endif; ?>
</div>
