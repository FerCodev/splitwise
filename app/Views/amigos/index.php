<?= view('partials/_head', ['title' => 'Amigos - Gastito']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Amigos']) ?>
<div class="container py-4 friends-page">
    <h2 class="mb-4 d-none d-md-block">Amigos</h2>
    <?= view('partials/_feedback') ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5>Buscar amigos</h5>
            <form method="get" action="<?= base_url('amigos') ?>" class="d-flex gap-2">
                <input type="text" name="query" class="form-control" required placeholder="email@ejemplo.com o @usuario" value="<?= esc($query) ?>">
                <button class="btn btn-primary">Buscar</button>
            </form>
            <?php if ($searched): ?>
                <div class="mt-3">
                    <?php if ($found): ?>
                        <div class="friend-row">
                            <?= view('components/avatar', ['userId' => $found['id'], 'name' => $found['name'], 'avatarFilename' => $found['avatar_filename'] ?? null, 'avatarUpdatedAt' => $found['avatar_updated_at'] ?? null, 'size' => 48]) ?>
                            <div>
                                <strong class="d-block"><?= esc($found['name']) ?></strong>
                                <small class="text-muted">@<?= esc($found['username']) ?></small>
                            </div>
                            <div class="ms-auto">
                                <?php if (empty($found['relationship']) || $found['relationship']['status'] === 'rejected'): ?>
                                    <form method="post" action="<?= base_url('amigos/enviar') ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="user_id" value="<?= $found['id'] ?>">
                                        <button class="btn btn-primary btn-sm">Agregar amigo</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= $found['relationship']['status'] === 'accepted' ? 'Ya son amigos' : 'Solicitud pendiente' ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No encontramos un usuario con ese email o nombre de usuario exacto.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($received): ?>
        <h5>Solicitudes recibidas</h5>
        <div class="card mb-4">
            <div class="card-body p-0">
                <?php foreach ($received as $item): ?>
                    <div class="friend-row">
                        <?= view('components/avatar', ['userId' => $item['other_id'], 'name' => $item['other_name'], 'avatarFilename' => $item['other_avatar_filename'], 'avatarUpdatedAt' => $item['other_avatar_updated_at'], 'size' => 44]) ?>
                        <div>
                            <strong class="d-block"><?= esc($item['other_name']) ?></strong>
                            <small class="text-muted">@<?= esc($item['other_username']) ?></small>
                        </div>
                        <div class="ms-auto d-flex gap-2">
                            <form method="post" action="<?= base_url('amigos/' . $item['id'] . '/aceptar') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-success btn-sm">Aceptar</button>
                            </form>
                            <form method="post" action="<?= base_url('amigos/' . $item['id'] . '/rechazar') ?>">
                                <?= csrf_field() ?>
                                <button class="btn btn-outline-secondary btn-sm">Rechazar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($sent): ?>
        <h5>Solicitudes enviadas</h5>
        <div class="card mb-4">
            <div class="card-body p-0">
                <?php foreach ($sent as $item): ?>
                    <div class="friend-row">
                        <?= view('components/avatar', ['userId' => $item['other_id'], 'name' => $item['other_name'], 'avatarFilename' => $item['other_avatar_filename'], 'avatarUpdatedAt' => $item['other_avatar_updated_at'], 'size' => 44]) ?>
                        <div>
                            <strong class="d-block"><?= esc($item['other_name']) ?></strong>
                            <small class="text-muted">@<?= esc($item['other_username']) ?></small>
                        </div>
                        <form method="post" action="<?= base_url('amigos/' . $item['id'] . '/cancelar') ?>" class="ms-auto">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger btn-sm">Cancelar</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <h5>Mis amigos</h5>
    <div class="card">
        <div class="card-body p-0">
            <?php if (!$friends): ?>
                <p class="text-muted p-3 mb-0">Todav&iacute;a no agregaste amigos.</p>
            <?php endif; ?>
            <?php foreach ($friends as $item): ?>
                <div class="friend-row">
                    <?= view('components/avatar', ['userId' => $item['other_id'], 'name' => $item['other_name'], 'avatarFilename' => $item['other_avatar_filename'], 'avatarUpdatedAt' => $item['other_avatar_updated_at'], 'size' => 48]) ?>
                    <a href="<?= base_url('usuarios/' . $item['other_id'] . '/perfil') ?>" class="text-decoration-none flex-grow-1">
                        <strong class="d-block"><?= esc($item['other_name']) ?></strong>
                        <small class="text-muted">@<?= esc($item['other_username']) ?></small>
                    </a>
                    <?= view('components/forms/delete_form', [
                        'action' => base_url('amigos/' . $item['id'] . '/eliminar'),
                        'formId' => 'eliminar-amistad-' . $item['id'],
                        'label' => 'Eliminar',
                        'buttonClass' => 'btn btn-outline-danger btn-sm',
                        'confirmTitle' => 'Eliminar amistad',
                        'confirmMsg' => 'Se eliminará esta amistad. Esta acción no se puede deshacer.',
                        'confirmBtn' => 'Eliminar',
                        'methodOverride' => false,
                    ]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
