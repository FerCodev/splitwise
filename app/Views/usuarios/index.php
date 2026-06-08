<?= view('partials/_head', ['title' => 'SplitWise - Usuarios']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold mb-0">Usuarios</h4>
            <a href="<?= base_url('usuarios/nuevo') ?>" class="btn btn-primary">+ Nuevo</a>
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

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0 d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="fw-medium"><?= esc($user['name']) ?></td>
                                    <td><?= esc($user['email']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('usuarios/' . $user['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-md-none">
                <?php foreach ($users as $user): ?>
                    <div class="mobile-card-item">
                        <div class="fw-medium"><?= esc($user['name']) ?></div>
                        <div class="text-muted small"><?= esc($user['email']) ?></div>
                        <div class="text-muted small">Creado: <?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
                        <div class="mt-2">
                            <a href="<?= base_url('usuarios/' . $user['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

<?= view('partials/_footer') ?>
