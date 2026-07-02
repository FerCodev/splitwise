<?php

return [
    'key' => 'profile_avatar',
    'name' => 'Avatar de usuario',
    'description' => 'Foto de perfil con fallback de iniciales y tama?os reutilizables.',
    'variants' => [
        ['key' => 'photo', 'name' => 'Con foto', 'hint' => 'Usa el componente real.', 'render' => static fn () => view('components/avatar', ['userId' => session()->get('userId'), 'name' => session()->get('userName'), 'avatarFilename' => 'catalog-preview.webp', 'avatarUpdatedAt' => date('Y-m-d H:i:s'), 'size' => 80])],
        ['key' => 'initials', 'name' => 'Con inicial', 'hint' => 'Fallback sin archivo.', 'render' => static fn () => view('components/avatar', ['userId' => 2, 'name' => 'Antonella', 'avatarFilename' => null, 'avatarUpdatedAt' => null, 'size' => 56])],
        ['key' => 'sizes', 'name' => 'Tama&ntilde;os', 'hint' => '24, 40 y 72 px.', 'render' => static fn () => view('components/avatar', ['userId' => 3, 'name' => 'Maria', 'avatarFilename' => null, 'avatarUpdatedAt' => null, 'size' => 24]) . ' ' . view('components/avatar', ['userId' => 3, 'name' => 'Maria', 'avatarFilename' => null, 'avatarUpdatedAt' => null, 'size' => 40]) . ' ' . view('components/avatar', ['userId' => 3, 'name' => 'Maria', 'avatarFilename' => null, 'avatarUpdatedAt' => null, 'size' => 72])],
    ],
];
