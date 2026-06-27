<?= view('partials/_head', ['title' => 'Gastito - Grupos']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Grupos']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0 d-none d-md-block">Mis Grupos</h2>
            <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary d-none d-md-inline-flex">+ Nuevo</a>
        </div>

        <?= view('partials/_feedback') ?>

        <?php if (empty($grupos)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:64px;height:64px;background:#e2e8f0;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#64748b" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.26.26 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.27.27 0 0 1-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.5 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/></svg>
                    </div>
                    <p class="text-muted mb-3">No ten&eacute;s grupos a&uacute;n. Cre&aacute; uno nuevo.</p>
                    <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">Crear Grupo</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($grupos as $grupo): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <?php
                                    $badgeClases = ['activo' => 'bg-success', 'cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
                                    $clase = $badgeClases[$grupo['estado']] ?? 'bg-secondary';
                                    $avatarColors = ['activo' => ['bg' => '#dbeafe', 'text' => '#2563eb'], 'cerrado' => ['bg' => '#fef3c7', 'text' => '#d97706'], 'liquidado' => ['bg' => '#e2e8f0', 'text' => '#64748b']];
                                    $avatarColor = $avatarColors[$grupo['estado']] ?? $avatarColors['liquidado'];
                                ?>
                                <div class="d-flex align-items-start gap-3 mb-2">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width:48px;height:48px;background:<?= $avatarColor['bg'] ?>;font-weight:700;color:<?= $avatarColor['text'] ?>;font-size:1.2rem;">
                                        <?= esc(mb_substr($grupo['nombre'], 0, 1)) ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <h5 class="card-title fw-bold mb-0"><?= esc($grupo['nombre']) ?></h5>
                                            <span class="badge <?= $clase ?> flex-shrink-0"><?= ucfirst($grupo['estado']) ?></span>
                                        </div>
                                        <p class="card-text text-muted small mb-0 mt-1"><?= esc($grupo['descripcion'] ?? 'Sin descripción') ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0 d-flex gap-2">
                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-primary flex-fill">Abrir</a>
                                <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-secondary flex-fill">Editar</a>
                                <?= view('components/forms/delete_form', [
                                    'action' => base_url('grupos/' . $grupo['id']),
                                    'formId' => 'delete-grupo-' . $grupo['id'],
                                    'buttonClass' => 'btn btn-danger w-100',
                                    'confirmTitle' => 'Eliminar grupo',
                                    'confirmMsg' => 'Se eliminará el grupo y ya no podrás consultarlo. Esta acción no se puede deshacer.',
                                    'confirmBtn' => 'Eliminar grupo',
                                ]) ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('grupos/nuevo') ?>" class="fab fab-extended d-md-none">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
            <span>Nuevo grupo</span>
        </a>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
