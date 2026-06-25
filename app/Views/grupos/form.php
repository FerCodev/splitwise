<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($grupo) ? 'Editar' : 'Nuevo') . ' Grupo']) ?>
<?= view('partials/_navbar', ['pageTitle' => isset($grupo) ? 'Editar grupo' : 'Nuevo grupo']) ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4 d-none d-md-block"><?= isset($grupo) ? 'Editar Grupo' : 'Nuevo Grupo' ?></h2>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?= view('partials/_feedback') ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="<?= isset($grupo) ? base_url('grupos/' . $grupo['id']) : base_url('grupos') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if (isset($grupo)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-medium">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                               value="<?= esc(old('nombre', $grupo['nombre'] ?? '')) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label fw-medium">Descripci&oacute;n</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= esc(old('descripcion', $grupo['descripcion'] ?? '')) ?></textarea>
                    </div>

                    <?php if (!isset($grupo)): ?>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Miembros iniciales</label>
                            <p class="text-muted small mb-2">Seleccion&aacute; los miembros que quer&eacute;s agregar al grupo. Vos qued&aacute;s como administrador.</p>
                            <?php if (!empty($usuarios)): ?>
                                <div class="initial-member-list">
                                    <?php foreach ($usuarios as $u): ?>
                                        <?php
                                            $nombre = trim($u['name'] ?? '');
                                            $inicial = strtoupper(substr($nombre !== '' ? $nombre : ($u['email'] ?? '?'), 0, 1));
                                        ?>
                                        <label class="initial-member-option" for="miembro_<?= $u['id'] ?>">
                                            <input class="form-check-input" type="checkbox" name="miembros[]"
                                                   value="<?= $u['id'] ?>" id="miembro_<?= $u['id'] ?>">
                                            <span class="initial-member-avatar" aria-hidden="true"><?= esc($inicial) ?></span>
                                            <span class="initial-member-copy">
                                                <span class="initial-member-name"><?= esc($u['name']) ?></span>
                                                <span class="initial-member-email"><?= esc($u['email']) ?></span>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="initial-member-empty">
                                    <div class="fw-medium">No hay otros usuarios disponibles</div>
                                    <div class="text-muted small">Pod&eacute;s crear el grupo y sumar miembros m&aacute;s adelante.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <a href="<?= isset($grupo) ? base_url('grupos/' . $grupo['id']) : base_url('grupos') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($grupo) ? 'Guardar Cambios' : 'Crear Grupo' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

            <?php if (isset($grupo) && isset($miembros)): ?>
            <?php
                $badgeEstado = ['activo' => 'bg-success', 'cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
                $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
            ?>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Estado del grupo</h5>
                    <span class="badge <?= $claseEstado ?>"><?= ucfirst($grupo['estado']) ?></span>
                </div>
                <div class="card-body">
                    <?php if ($permisos['puede_cambiar_estado'] ?? false): ?>
                        <?php if ($grupo['estado'] === 'activo'): ?>
                            <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post" id="cerrar-grupo-<?= $grupo['id'] ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="estado" value="cerrado">
                                <button type="button" class="btn btn-warning"
                                    data-bs-toggle="modal" data-bs-target="#confirmModal"
                                    data-confirm-title="Cerrar grupo"
                                    data-confirm-msg="Se cerrara el grupo. No se podran crear ni editar gastos, solo registrar pagos para saldar deudas."
                                    data-confirm-btn="Cerrar grupo"
                                    data-confirm-class="btn-warning"
                                    data-confirm-form="cerrar-grupo-<?= $grupo['id'] ?>">Cerrar grupo</button>
                            </form>
                        <?php elseif ($grupo['estado'] === 'cerrado'): ?>
                            <div class="d-flex gap-2 flex-wrap">
                                <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post" id="reabrir-grupo-<?= $grupo['id'] ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="estado" value="activo">
                                    <button type="button" class="btn btn-success"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-confirm-title="Reabrir grupo"
                                        data-confirm-msg="Se reabrira el grupo. Los miembros podran crear y editar gastos nuevamente."
                                        data-confirm-btn="Reabrir grupo"
                                        data-confirm-class="btn-success"
                                        data-confirm-form="reabrir-grupo-<?= $grupo['id'] ?>">Reabrir grupo</button>
                                </form>
                                <?php if (empty($deudas)): ?>
                                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post" id="liquidar-grupo-<?= $grupo['id'] ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="estado" value="liquidado">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-toggle="modal" data-bs-target="#confirmModal"
                                            data-confirm-title="Liquidar grupo"
                                            data-confirm-msg="Se liquidara el grupo. Esta accion es definitiva y no se puede deshacer."
                                            data-confirm-btn="Liquidar grupo"
                                            data-confirm-class="btn-secondary"
                                            data-confirm-form="liquidar-grupo-<?= $grupo['id'] ?>">Liquidar grupo</button>
                                    </form>
                                <?php else: ?>
                                    <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-info">Ver deudas pendientes</a>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($grupo['estado'] === 'liquidado'): ?>
                            <p class="text-muted mb-0">Este grupo est&aacute; finalizado. No se pueden realizar m&aacute;s cambios.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted mb-0">Solo los administradores pueden cambiar el estado del grupo.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Miembros (<?= count($miembros) ?>)</h5>
                </div>
                <?php if (!empty($usuariosDisponibles)): ?>
                <div class="card-body border-bottom">
                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros') ?>" method="post" class="row g-2 align-items-end">
                        <?= csrf_field() ?>
                        <div class="col-8 col-md-10">
                            <select name="user_id" class="form-select" required>
                                <option value="">Agregar miembro...</option>
                                <?php foreach ($usuariosDisponibles as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?> (<?= esc($u['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4 col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Agregar</button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($miembros as $m): ?>
                                <tr>
                                    <td class="fw-medium"><?= esc($m['name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $m['rol'] === 'admin' ? 'warning' : 'secondary' ?>">
                                            <?= $m['rol'] === 'admin' ? 'Admin' : 'Miembro' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ((int) $m['user_id'] !== (int) session()->get('userId')): ?>
                                            <div class="d-flex gap-1">
                                                <?php if ($permisos['puede_cambiar_rol'] ?? false): ?>
                                                <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $m['user_id'] . '/rol') ?>" method="post" class="d-inline">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="rol" value="<?= $m['rol'] === 'admin' ? 'member' : 'admin' ?>">
                                                    <button type="submit" class="btn btn-sm <?= $m['rol'] === 'admin' ? 'btn-secondary' : 'btn-warning' ?>"><?= $m['rol'] === 'admin' ? 'Hacer miembro' : 'Hacer admin' ?></button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if ($permisos['puede_quitar_miembro'] ?? false): ?>
                                                <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $m['user_id']) ?>" method="post" class="d-inline" id="quitar-miembro-<?= $m['user_id'] ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-confirm-title="Quitar miembro" data-confirm-msg="Se quitará este usuario del grupo." data-confirm-btn="Quitar miembro" data-confirm-form="quitar-miembro-<?= $m['user_id'] ?>">Quitar</button>
                                                </form>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Sos vos</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
