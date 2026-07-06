<?= view('partials/_head', ['title' => 'Gastito - ' . $gasto['descripcion']]) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Detalle de gastito']) ?>

    <div class="container mt-3 mt-md-4">

        <?= view('partials/_feedback') ?>

        <!-- Header -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="fw-bold text-primary mb-0"><?= moneda($gasto['monto']) ?></h2>
                        <p class="text-muted small mb-0">Pag&oacute; <?= esc($gasto['pagador_nombre']) ?></p>
                    </div>
                    <?php if ($permisos['puede_editar_gasto'] || $permisos['puede_eliminar_gasto']): ?>
                    <button type="button" class="btn btn-light border" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;padding:0;" data-bs-toggle="modal" data-bs-target="#accionesGastoModal" aria-label="Acciones del gastito" title="Acciones">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/><path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/></svg>
                    </button>
                    <?php endif; ?>
                </div>
                <h5 class="fw-bold mb-1"><?= esc($gasto['descripcion']) ?></h5>
                <p class="text-muted small mb-0 d-flex flex-wrap align-items-center gap-1">
                    <a href="<?= base_url('grupos/' . $gasto['grupo_id']) ?>" class="meta-pill-link"><?= esc($gasto['grupo_nombre']) ?></a>
                    <span>&middot;</span>
                    <span><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></span>
                    <span>&middot;</span>
                    <span class="badge bg-light text-dark"><?= esc($gasto['categoria_nombre'] ?? 'Otros') ?></span>
                </p>

            </div>
        </div>

        <?php if (!empty($gasto['nota']) || !empty($gasto['recibo_path'])): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <?php if (!empty($gasto['nota'])): ?>
                <div class="mb-2">
                    <span class="text-muted small fw-medium">Nota:</span>
                    <p class="mb-0"><?= esc($gasto['nota']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($gasto['recibo_path'])): ?>
                <div>
                    <span class="text-muted small fw-medium">Comprobante:</span>
                    <div class="mt-1">
                        <span class="small"><?= esc($gasto['recibo_nombre'] ?? 'Archivo adjunto') ?></span>
                        <a href="<?= base_url('gastos/' . $gasto['id'] . '/recibo') ?>" class="btn btn-sm btn-primary ms-2" target="_blank">Ver</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php $totalParticipantes = array_sum(array_column($participantes, 'monto_asignado')); ?>

        <!-- Participantes -->
        <div class="card border-0 shadow-sm mb-4 expense-people-card">
            <div class="card-header bg-white expense-section-header">
                <div>
                    <h5 class="mb-0 fw-bold">Participantes</h5>
                    <span class="text-muted small"><?= count($participantes) ?> personas</span>
                </div>
                <div class="expense-avatar-stack" aria-hidden="true">
                    <?php foreach (array_slice($participantes, 0, 4) as $p): ?>
                        <?= view('components/avatar', [
                            'userId' => $p['user_id'],
                            'name' => $p['name'],
                            'avatarFilename' => $p['avatar_filename'] ?? null,
                            'avatarUpdatedAt' => $p['avatar_updated_at'] ?? null,
                            'size' => 32,
                            'classes' => 'expense-stack-avatar',
                        ]) ?>
                    <?php endforeach; ?>
                    <?php if (count($participantes) > 4): ?>
                        <span class="expense-stack-more">+<?= count($participantes) - 4 ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="expense-people-list">
                <?php foreach ($participantes as $p): ?>
                    <div class="expense-person-row">
                        <?= view('components/avatar', [
                            'userId' => $p['user_id'],
                            'name' => $p['name'],
                            'avatarFilename' => $p['avatar_filename'] ?? null,
                            'avatarUpdatedAt' => $p['avatar_updated_at'] ?? null,
                            'size' => 44,
                        ]) ?>
                        <div class="expense-person-copy">
                            <div class="expense-person-name">
                                <?= esc($p['name']) ?>
                                <?php if ((int) $p['user_id'] === (int) $gasto['pagador_id']): ?>
                                    <span class="expense-payer-badge">Pagó</span>
                                <?php endif; ?>
                            </div>
                            <div class="expense-person-email"><?= esc($p['email']) ?></div>
                        </div>
                        <div class="expense-person-amount">
                            <span>Parte asignada</span>
                            <strong><?= moneda($p['monto_asignado']) ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="expense-people-total">
                <span>Total distribuido</span>
                <strong><?= moneda($totalParticipantes) ?></strong>
            </div>
        </div>

        <!-- Resumen -->
        <div class="card border-0 shadow-sm expense-summary-card">
            <div class="card-header bg-white expense-section-header">
                <div>
                    <h5 class="mb-0 fw-bold">Resumen</h5>
                    <span class="text-muted small">Quién le debe a quién</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="expense-payer-summary">
                    <?= view('components/avatar', [
                        'userId' => $gasto['pagador_id'],
                        'name' => $gasto['pagador_nombre'],
                        'avatarFilename' => $gasto['pagador_avatar_filename'] ?? null,
                        'avatarUpdatedAt' => $gasto['pagador_avatar_updated_at'] ?? null,
                        'size' => 48,
                    ]) ?>
                    <div>
                        <span class="text-muted small">Pagado por</span>
                        <div class="fw-bold"><?= esc($gasto['pagador_nombre']) ?></div>
                    </div>
                    <strong class="expense-payer-total"><?= moneda($gasto['monto']) ?></strong>
                </div>

                <div class="expense-transfer-list">
                    <?php $hayTransferencias = false; ?>
                    <?php foreach ($participantes as $p): ?>
                        <?php if ((int) $p['user_id'] !== (int) $gasto['pagador_id']): ?>
                            <?php $hayTransferencias = true; ?>
                            <div class="expense-transfer-row">
                                <div class="expense-transfer-person">
                                    <?= view('components/avatar', [
                                        'userId' => $p['user_id'],
                                        'name' => $p['name'],
                                        'avatarFilename' => $p['avatar_filename'] ?? null,
                                        'avatarUpdatedAt' => $p['avatar_updated_at'] ?? null,
                                        'size' => 36,
                                    ]) ?>
                                    <div>
                                        <strong><?= esc($p['name']) ?></strong>
                                        <span>debe a <?= esc($gasto['pagador_nombre']) ?></span>
                                    </div>
                                </div>
                                <div class="expense-transfer-amount"><?= moneda($p['monto_asignado']) ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (!$hayTransferencias): ?>
                        <div class="expense-summary-empty">No hay importes pendientes entre participantes.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?= view('partials/_confirm_modal') ?>

<div class="modal fade expense-actions-modal" id="accionesGastoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Acciones del gastito</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-2">
                <?php if ($permisos['puede_editar_gasto']): ?>
                    <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-primary w-100">Editar</a>
                <?php endif; ?>
                <?php if ($permisos['puede_eliminar_gasto']): ?>
                    <?= view('components/forms/delete_form', [
                        'action' => base_url('gastos/' . $gasto['id']),
                        'formId' => 'delete-gasto-' . $gasto['id'],
                        'buttonClass' => 'btn btn-danger w-100',
                        'confirmTitle' => 'Eliminar gastito',
                        'confirmMsg' => 'Se eliminará este gastito del grupo y el balance se recalculará. Esta acción no se puede deshacer.',
                        'confirmBtn' => 'Eliminar gastito',
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= view('partials/_footer') ?>
