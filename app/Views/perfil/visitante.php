<?php $profileColor = \App\Services\UserColor::get($user['color'] ?? null); ?>
<?= view('partials/_head', ['title' => $user['name'] . ' - Gastito']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Perfil']) ?>
<div class="container py-4 visitor-profile-page">
    <?= view('partials/_feedback') ?>
    <div class="card visitor-profile-card"><div class="card-body text-center">
        <button type="button" class="profile-photo-button" data-bs-toggle="modal" data-bs-target="#profilePhotoModal" aria-label="Ampliar foto de <?= esc($user['name']) ?>">
            <?= view('components/avatar', ['userId' => $user['id'], 'name' => $user['name'], 'avatarFilename' => $user['avatar_filename'] ?? null, 'avatarUpdatedAt' => $user['avatar_updated_at'] ?? null, 'size' => 144, 'interactive' => false]) ?>
        </button>
        <h2 class="mt-3 mb-1"><?= esc($user['name']) ?></h2>
        <?php if ($isFriend): ?>
            <span class="badge bg-success mb-3">Amigo</span>
            <div class="visitor-profile-details text-start">
                <div><span>Email</span><strong><?= esc($user['email']) ?></strong></div>
                <div><span>Color</span><strong><i class="profile-color-dot" style="--profile-color:<?= esc($profileColor['solid'] ?? '#2563eb') ?>"></i><?= esc($profileColor['label'] ?? 'Predeterminado') ?></strong></div>
                <div><span>Grupos compartidos</span><strong><?= $sharedGroups ? esc(implode(', ', array_column($sharedGroups, 'nombre'))) : 'Ninguno' ?></strong></div>
            </div>
        <?php else: ?><p class="text-muted">Solo sus amigos pueden ver m&aacute;s informaci&oacute;n.</p><?php endif; ?>
    </div></div>
</div>
<div class="modal fade profile-photo-modal" id="profilePhotoModal" tabindex="-1" aria-labelledby="profilePhotoTitle" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down"><div class="modal-content"><div class="modal-header"><h5 id="profilePhotoTitle" class="modal-title"><?= esc($user['name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body"><img src="<?= base_url('usuarios/'.$user['id'].'/avatar') ?>?v=<?= esc($user['avatar_updated_at'] ? strtotime($user['avatar_updated_at']) : 0) ?>" alt="Foto de perfil de <?= esc($user['name']) ?>"></div></div></div>
</div>
<?= view('partials/_footer') ?>
