<?= view('partials/_head', ['title' => 'SplitWise - Usuarios']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Usuarios']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0 d-none d-md-block">Usuarios</h2>
            <a href="<?= base_url('usuarios/nuevo') ?>" class="btn btn-primary btn-sm d-none d-md-inline-flex">+ Nuevo</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Desktop table -->
        <div class="card d-none d-md-block">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Creado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="fw-semibold"><?= esc($user['name']) ?></td>
                                <td class="text-muted"><?= esc($user['email']) ?></td>
                                <td><span class="badge bg-<?= $user['role'] === 'admin' ? 'warning' : 'secondary' ?>"><?= $user['role'] === 'admin' ? 'Admin' : 'User' ?></span></td>
                                <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                <td><a href="<?= base_url('usuarios/' . $user['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile list -->
        <div class="card d-md-none">
            <?php foreach ($users as $user): ?>
                <?php
                    $nombre = trim($user['name'] ?? '');
                    $inicial = strtoupper(substr($nombre !== '' ? $nombre : ($user['email'] ?? '?'), 0, 1));
                ?>
                <div class="financial-list-item">
                    <div class="avatar avatar-sm" style="background:var(--primary-light);color:var(--primary);"><?= esc($inicial) ?></div>
                    <div class="financial-list-item-info">
                        <div class="financial-list-item-title" style="font-size:14px;"><?= esc($user['name']) ?></div>
                        <div class="financial-list-item-subtitle">
                            <?= esc($user['email']) ?> &middot;
                            <span class="badge bg-<?= $user['role'] === 'admin' ? 'warning' : 'secondary' ?>" style="font-size:10px;padding:2px 6px;"><?= $user['role'] === 'admin' ? 'Admin' : 'User' ?></span>
                        </div>
                    </div>
                    <a href="<?= base_url('usuarios/' . $user['id'] . '/editar') ?>" class="btn btn-primary btn-sm">Editar</a>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="<?= base_url('usuarios/nuevo') ?>" class="d-md-none fab" aria-label="Nuevo usuario">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
        </a>

        <?php if (isset($pager)): ?>
            <div class="pagination-wrap mt-3">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
