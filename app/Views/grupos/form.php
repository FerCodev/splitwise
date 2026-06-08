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
                        <label for="descripcion" class="form-label fw-medium">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= esc(old('descripcion', $grupo['descripcion'] ?? '')) ?></textarea>
                    </div>

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
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Estado del grupo</h5>
                </div>
                <div class="card-body">
                    <?php
                        $badgeEstado = [
                            'activo' => 'bg-success',
                            'cerrado' => 'bg-warning text-dark',
                            'liquidado' => 'bg-secondary',
                        ];
                        $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                    ?>
                    <p class="mb-2">
                        Estado actual:
                        <span class="badge <?= $claseEstado ?> ms-1"><?= ucfirst($grupo['estado']) ?></span>
                    </p>

                    <?php if ($grupo['estado'] === 'activo'): ?>
                        <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="estado" value="cerrado">
                            <button type="submit" class="btn btn-outline-warning">Cerrar grupo</button>
                        </form>
                    <?php elseif ($grupo['estado'] === 'cerrado'): ?>
                        <div class="d-flex gap-2">
                            <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post">
                                <?= csrf_field() ?>
                                <input type="hidden" name="estado" value="activo">
                                <button type="submit" class="btn btn-outline-success">Reabrir grupo</button>
                            </form>
                            <?php if (empty($deudas)): ?>
                                <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="estado" value="liquidado">
                                    <button type="submit" class="btn btn-outline-secondary">Liquidar grupo</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($grupo['estado'] === 'liquidado'): ?>
                        <p class="text-muted mb-0">Este grupo está finalizado. No se pueden realizar más cambios de estado.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?= view('partials/_footer') ?>
