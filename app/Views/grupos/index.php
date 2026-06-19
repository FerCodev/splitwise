<?= view('partials/_head', ['title' => 'SplitWise - Grupos']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Grupos']) ?>

    <div class="container mt-3 mt-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0 d-none d-md-block">Mis Grupos</h2>
            <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary btn-sm d-none d-md-inline-flex">+ Nuevo</a>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <?php if (empty($grupos)): ?>
            <div class="empty-state">
                <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1A.26.26 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002a.27.27 0 0 1-.014.002zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0M6.936 9.28a6 6 0 0 0-1.23-.247A7 7 0 0 0 5 9c-4 0-5 3-5 4q0 1 1 1h4.216A2.24 2.24 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816M4.5 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/></svg>
                <div class="empty-state-title">No ten&eacute;s grupos a&uacute;n</div>
                <div class="empty-state-text">Cre&aacute; tu primer grupo para empezar a compartir gastos.</div>
                <a href="<?= base_url('grupos/nuevo') ?>" class="btn btn-primary">Crear Grupo</a>
            </div>
        <?php else: ?>
            <?php
                $avatarColors = ['avatar-blue', 'avatar-green', 'avatar-purple', 'avatar-orange', 'avatar-teal', 'avatar-pink', 'avatar-red', 'avatar-indigo'];
                $colorIdx = 0;
            ?>
            <div class="card">
                <?php foreach ($grupos as $grupo): ?>
                    <?php
                        $badgeClases = ['activo' => 'bg-success', 'cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
                        $clase = $badgeClases[$grupo['estado']] ?? 'bg-secondary';
                        $avaColor = $avatarColors[$colorIdx % count($avatarColors)];
                        $colorIdx++;
                    ?>
                    <div class="financial-list-item" style="border-bottom:1px solid var(--border);">
                        <div class="avatar <?= $avaColor ?>"><?= esc(mb_strtoupper(mb_substr($grupo['nombre'], 0, 1))) ?></div>
                        <div class="financial-list-item-info">
                            <div class="financial-list-item-title">
                                <?= esc($grupo['nombre']) ?>
                                <span class="badge <?= $clase ?>" style="font-size:10px;padding:2px 6px;margin-left:6px;"><?= ucfirst($grupo['estado']) ?></span>
                            </div>
                            <div class="financial-list-item-subtitle"><?= esc($grupo['descripcion'] ?? 'Sin descripci&oacute;n') ?></div>
                        </div>
                        <div class="d-flex gap-1">
                            <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-primary btn-sm">Abrir</a>
                            <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-secondary btn-sm d-none d-md-inline-flex">Editar</a>
                            <form action="<?= base_url('grupos/' . $grupo['id']) ?>" method="post" class="d-none d-md-inline" id="delete-grupo-<?= $grupo['id'] ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" class="btn btn-danger btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#confirmModal"
                                    data-confirm-title="Eliminar grupo"
                                    data-confirm-msg="Se eliminar&aacute; el grupo y ya no podr&aacute;s consultarlo. Esta acci&oacute;n no se puede deshacer."
                                    data-confirm-btn="Eliminar grupo"
                                    data-confirm-form="delete-grupo-<?= $grupo['id'] ?>">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="<?= base_url('grupos/nuevo') ?>" class="d-md-none fab" aria-label="Nuevo grupo">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
        </a>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
