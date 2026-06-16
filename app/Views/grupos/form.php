<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($grupo) ? 'Editar' : 'Nuevo') . ' Grupo']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4"><?= isset($grupo) ? 'Editar Grupo' : 'Nuevo Grupo' ?></h2>

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

                    <?php if (!isset($grupo) && !empty($usuarios)): ?>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Miembros iniciales</label>
                            <p class="text-muted small mb-2">Seleccion&aacute; los miembros que quer&eacute;s agregar al grupo. Vos qued&aacute;s como administrador.</p>
                            <div class="row g-2">
                                <?php foreach ($usuarios as $u): ?>
                                    <div class="col-12 col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="miembros[]"
                                                   value="<?= $u['id'] ?>" id="miembro_<?= $u['id'] ?>">
                                            <label class="form-check-label" for="miembro_<?= $u['id'] ?>">
                                                <?= esc($u['name']) ?>
                                                <small class="text-muted">&lt;<?= esc($u['email']) ?>&gt;</small>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('grupos') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($grupo) ? 'Guardar Cambios' : 'Crear Grupo' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

            <?php if (isset($grupo) && isset($miembros)): ?>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Miembros (<?= count($miembros) ?>)</h5>
                    <?php if (!empty($usuariosDisponibles)): ?>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#agregarMiembroForm" aria-expanded="false">
                        + Agregar
                    </button>
                    <?php endif; ?>
                </div>
                <?php if (!empty($usuariosDisponibles)): ?>
                <div class="collapse" id="agregarMiembroForm">
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
                                                    <button type="submit" class="btn btn-sm <?= $m['rol'] === 'admin' ? 'btn-outline-secondary' : 'btn-outline-warning' ?>"><?= $m['rol'] === 'admin' ? 'Hacer miembro' : 'Hacer admin' ?></button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if ($permisos['puede_quitar_miembro'] ?? false): ?>
                                                <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $m['user_id']) ?>" method="post" class="d-inline" id="quitar-miembro-<?= $m['user_id'] ?>">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-confirm-title="Quitar miembro" data-confirm-msg="Se quitará este usuario del grupo." data-confirm-btn="Quitar miembro" data-confirm-form="quitar-miembro-<?= $m['user_id'] ?>">Quitar</button>
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
