<?php

use App\Services\UiComponentResolver;

$alertType = $type ?? 'info';
$alertMessage = (string) ($message ?? '');
$alertMessages = array_values(array_filter((array) ($messages ?? []), static fn ($item) => (string) $item !== ''));

$config = [
    'success' => [
        'component' => UiComponentResolver::COMPONENT_ALERT_SUCCESS,
        'title' => 'Listo',
        'icon' => '&check;',
        'classes' => [
            'success_compact' => 'tabler-alert-success',
            'settled' => 'tabler-alert-settled',
            'favorite_updated' => 'tabler-alert-favorite',
            'import_ready' => 'tabler-alert-import',
            'backup_created' => 'tabler-alert-neutral',
            'synced' => 'tabler-alert-sync',
            'member_added' => 'tabler-alert-member',
        ],
    ],
    'error' => [
        'component' => UiComponentResolver::COMPONENT_ALERT_ERROR,
        'title' => 'No se pudo completar',
        'icon' => '!',
        'classes' => [
            'error_action' => 'tabler-alert-danger',
            'amount_validation' => 'tabler-alert-inline-error',
            'incomplete_payment_method' => 'tabler-alert-warning',
        ],
    ],
    'warning' => [
        'component' => UiComponentResolver::COMPONENT_ALERT_WARNING,
        'title' => 'Atenci&oacute;n',
        'icon' => '!',
        'classes' => [
            'warning_debt' => 'tabler-alert-warning',
            'out_of_period_date' => 'tabler-alert-calendar',
            'import_partial' => 'tabler-alert-partial',
            'duplicate_payment' => 'tabler-alert-duplicate',
            'unsaved_changes' => 'tabler-alert-unsaved',
        ],
    ],
    'info' => [
        'component' => UiComponentResolver::COMPONENT_ALERT_INFO,
        'title' => 'Informaci&oacute;n',
        'icon' => 'i',
        'classes' => [
            'info_filter' => 'tabler-alert-info',
            'offline' => 'tabler-alert-neutral',
            'update_available' => 'tabler-alert-update',
            'maintenance' => 'tabler-alert-maintenance',
        ],
    ],
];

$current = $config[$alertType] ?? $config['info'];
$variant = UiComponentResolver::variant(UiComponentResolver::SCREEN_SYSTEM_ALERTS, $current['component']);
$variantClass = $current['classes'][$variant] ?? reset($current['classes']);
$alertTitle = (string) ($title ?? $current['title']);
$alertIcon = (string) ($icon ?? $current['icon']);
$alertDuration = (int) ($duration ?? ($alertMessages ? 9000 : 6000));
?>
<div class="tabler-alert-card <?= esc($variantClass, 'attr') ?> app-feedback" role="<?= $alertType === 'error' ? 'alert' : 'status' ?>" data-alert-variant="<?= esc($variant, 'attr') ?>" style="--feedback-duration: <?= $alertDuration ?>ms">
    <span class="app-feedback-timer" aria-hidden="true"></span>
    <span class="tabler-alert-icon" aria-hidden="true"><?= $alertIcon ?></span>
    <div>
        <strong><?= $alertTitle ?></strong>
        <?php if ($alertMessages): ?>
            <ul class="app-feedback-list">
                <?php foreach ($alertMessages as $item): ?>
                    <li><?= esc($item) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <small><?= esc($alertMessage) ?></small>
        <?php endif; ?>
    </div>
</div>
<script>
(function (script) {
    var alert = script.previousElementSibling;
    if (!alert || !alert.classList.contains('app-feedback')) return;

    var timer = alert.querySelector('.app-feedback-timer');
    if (!timer) return;

    timer.addEventListener('animationend', function () {
        alert.classList.add('app-feedback-exit');
        window.setTimeout(function () { alert.remove(); }, 220);
    }, { once: true });
})(document.currentScript);
</script>
