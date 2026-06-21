<?php
    $session = session();
    $alerts = $session->getFlashdata('alerts') ?? [];

    if (!is_array($alerts)) {
        $alerts = [];
    }

    if (isset($alerts['type']) || isset($alerts['title'])) {
        $alerts = [$alerts];
    }

    $legacyFlash = [
        'success' => 'success',
        'error' => 'danger',
        'warning' => 'warning',
        'info' => 'info',
    ];

    foreach ($legacyFlash as $flashKey => $type) {
        $message = $session->getFlashdata($flashKey);
        if ($message) {
            $alerts[] = [
                'type' => $type,
                'title' => $message,
                'message' => null,
            ];
            $session->remove($flashKey);
        }
    }
?>

<?php if ($alerts): ?>
    <div class="app-alert-stack">
        <?php foreach ($alerts as $alert): ?>
            <?php if (is_string($alert)): ?>
                <?= view('components/ui/alerta', ['type' => 'info', 'title' => $alert]) ?>
            <?php elseif (is_array($alert)): ?>
                <?= view('components/ui/alerta', [
                    'type' => $alert['type'] ?? 'info',
                    'title' => $alert['title'] ?? '',
                    'message' => $alert['message'] ?? null,
                    'actionText' => $alert['actionText'] ?? null,
                    'actionUrl' => $alert['actionUrl'] ?? null,
                    'dismissible' => $alert['dismissible'] ?? true,
                ]) ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
