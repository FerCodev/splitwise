<?= view('partials/_head', ['title' => 'SplitWise - Grupos']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold mb-0">Mis Grupos</h2>
            <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">+ Nuevo</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (empty($grupos)): ?>
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <p class="text-muted mb-0">No tenés grupos aún. Creá uno nuevo.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($grupos as $grupo): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?= esc($grupo['nombre']) ?>
                                    <?php
                                        $badgeClases = ['activo' => 'bg-success', 'cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
                                        $clase = $badgeClases[$grupo['estado']] ?? 'bg-secondary';
                                    ?>
                                    <span class="badge <?= $clase ?>"><?= ucfirst($grupo['estado']) ?></span>
                                </h5>
                                <p class="card-text text-muted small"><?= esc($grupo['descripcion'] ?? 'Sin descripción') ?></p>
                            </div>
                            <div class="card-footer bg-transparent border-0 pt-0 d-flex gap-2">
                                <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-primary flex-fill">Abrir</a>
                                <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-secondary flex-fill">Editar</a>
                                <form action="<?= base_url('grupos/' . $grupo['id']) ?>" method="post" class="flex-fill" id="delete-grupo-<?= $grupo['id'] ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="button" class="btn btn-danger w-100"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-confirm-title="Eliminar grupo"
                                        data-confirm-msg="Se eliminará el grupo y ya no podrás consultarlo. Esta acción no se puede deshacer."
                                        data-confirm-btn="Eliminar grupo"
                                        data-confirm-form="delete-grupo-<?= $grupo['id'] ?>">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
