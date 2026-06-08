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

        <?php if (isset($grupo)): ?>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1">Miembros</h5>
                            <p class="text-muted small mb-0">Gestion&aacute; los miembros desde la pantalla de detalle del grupo.</p>
                        </div>
                        <a href="<?= base_url('grupos/' . $grupo['id']) ?>" class="btn btn-outline-primary">Gestionar miembros</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
