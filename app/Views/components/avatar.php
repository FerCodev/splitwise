<?php
$userId = (int) ($userId ?? 0);
$name = trim((string) ($name ?? ''));
$initial = mb_strtoupper(mb_substr($name !== '' ? $name : '?', 0, 1));
$filename = (string) ($avatarFilename ?? '');
$updated = $avatarUpdatedAt ?? null;
$size = max(24, min(160, (int) ($size ?? 40)));
$classes = trim('user-avatar ' . ($classes ?? ''));
$alt = (string) ($alt ?? ('Foto de perfil de ' . ($name !== '' ? $name : 'usuario')));
$version = $updated ? (string) strtotime((string) $updated) : '0';
$interactive = (bool) ($interactive ?? true);
$profileUrl = $userId === (int) session()->get('userId') ? base_url('perfil') : base_url('usuarios/' . $userId . '/perfil');
$interaction = $interactive && $userId > 0
    ? ' role="link" tabindex="0" data-profile-url="' . esc($profileUrl) . '" onclick="event.preventDefault();event.stopPropagation();window.location.href=this.dataset.profileUrl" onkeydown="if(event.key===\'Enter\'||event.key===\' \'){event.preventDefault();event.stopPropagation();window.location.href=this.dataset.profileUrl}"'
    : '';
?>
<span class="<?= esc($classes) ?><?= $interactive ? ' user-avatar-interactive' : '' ?>" style="--avatar-size:<?= $size ?>px" aria-label="<?= esc($alt) ?>"<?= $interaction ?>>
    <?php if ($filename !== '' && $userId > 0): ?>
        <img src="<?= base_url('usuarios/' . $userId . '/avatar') ?>?v=<?= esc($version) ?>" alt="<?= esc($alt) ?>" width="<?= $size ?>" height="<?= $size ?>">
    <?php else: ?>
        <span class="user-avatar-fallback" aria-hidden="true"><?= esc($initial) ?></span>
    <?php endif; ?>
</span>
