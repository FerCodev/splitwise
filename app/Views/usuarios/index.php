<?= view('partials/_head', ['title' => 'Gastito - Usuarios']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Usuarios']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0 d-none d-md-block">Usuarios</h4>
            <a href="<?= base_url('usuarios/nuevo') ?>" class="btn btn-primary d-none d-md-inline-flex">+ Nuevo</a>
        </div>

        <?= view('partials/_feedback') ?>
        <?= view('partials/_validation_errors') ?>

        <div class="card border-0 shadow-sm d-none d-md-block">
            <div class="card-body p-0 d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="fw-medium"><?= esc($user['name']) ?></td>
                                    <td>@<?= esc($user['username']) ?></td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td><?= $user['role'] === 'admin' ? '<span class="badge bg-warning text-dark">Admin</span>' : '<span class="badge bg-secondary">User</span>' ?></td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('usuarios/' . $user['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-md-none user-card-list">
            <?php foreach ($users as $user): ?>
                <div class="card border-0 shadow-sm user-card">
                    <div class="card-body">
                        <div class="user-card-main">
                            <?= view('components/avatar', [
                                'userId' => $user['id'],
                                'name' => $user['name'],
                                'avatarFilename' => $user['avatar_filename'] ?? null,
                                'avatarUpdatedAt' => $user['avatar_updated_at'] ?? null,
                                'size' => 48,
                            ]) ?>
                            <div class="user-card-copy">
                                <div class="user-card-name"><?= esc($user['name']) ?></div>
                                <div class="text-muted small">@<?= esc($user['username']) ?></div>
                                <div class="user-card-email"><?= esc($user['email']) ?></div>
                                <div class="user-card-meta">
                                    <?= $user['role'] === 'admin' ? '<span class="badge bg-warning">Admin</span>' : '<span class="badge bg-secondary">User</span>' ?>
                                    <span>Creado <?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                                </div>
                            </div>
                            <a href="<?= base_url('usuarios/' . $user['id'] . '/editar') ?>" class="btn btn-primary btn-sm user-card-action">Editar</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="<?= base_url('usuarios/nuevo') ?>" class="fab fab-extended d-md-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
            <span>Nuevo usuario</span>
        </a>

        <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
            <div class="mt-4">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
