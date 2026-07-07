<?= view('partials/_head', ['title' => 'Notificaciones - Gastito']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Notificaciones']) ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 d-none d-md-block">Notificaciones</h2>
        <?php if ($unreadCount > 0): ?>
        <form method="post" action="<?= base_url('notificaciones/marcar-todas-leidas') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-primary btn-sm">Marcar todas como le&iacute;das</button>
        </form>
        <?php endif; ?>
    </div>

    <?= view('partials/_feedback') ?>

    <?php if (empty($notifications)): ?>
        <div class="text-center py-5 text-muted">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16" class="mb-3 opacity-50">
                <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zm.995-14.901a1 1 0 1 0-1.99 0A5.002 5.002 0 0 0 3 6c0 1.098-.5 6-2 7h14c-1.5-1-2-5.902-2-7 0-2.42-1.72-4.44-4.005-4.901z"/>
            </svg>
            <p class="mb-0">No ten&eacute;s notificaciones.</p>
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($notifications as $n): ?>
            <a href="<?= base_url("notificaciones/{$n['id']}/abrir") ?>"
               class="list-group-item list-group-item-action d-flex justify-content-between align-items-start <?= $n['read_at'] === null ? 'list-group-item-primary' : '' ?>">
                <div class="ms-2 me-auto">
                    <div class="fw-bold <?= $n['read_at'] === null ? '' : 'fw-normal' ?>"><?= esc($n['title']) ?></div>
                    <div class="text-muted small"><?= esc(html_entity_decode($n['body'], ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?></div>
                    <div class="text-muted small mt-1"><?= esc(date('d/m/Y H:i', strtotime($n['created_at']))) ?></div>
                </div>
                <?php if ($n['read_at'] === null): ?>
                <span class="badge bg-primary rounded-pill" style="width:10px;height:10px;min-width:10px;padding:0;" title="No le&iacute;da">&nbsp;</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
        <div class="pagination-wrap mt-3">
            <?= $pager->links() ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?= view('partials/_footer') ?>
