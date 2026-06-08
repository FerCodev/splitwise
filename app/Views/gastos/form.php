<?= view('partials/_head', ['title' => 'SplitWise - ' . (isset($gasto) ? 'Editar' : 'Nuevo') . ' Gasto']) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4"><?= isset($gasto) ? 'Editar Gasto' : 'Nuevo Gasto' ?></h2>

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
                <form action="<?= isset($gasto) ? base_url('gastos/' . $gasto['id']) : base_url('gastos') ?>" method="post">
                    <?= csrf_field() ?>
                    <?php if (isset($gasto)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-medium">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion"
                               value="<?= esc(old('descripcion', $gasto['descripcion'] ?? '')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="monto" class="form-label fw-medium">Monto total</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="monto" name="monto"
                               value="<?= esc(old('monto', $gasto['monto'] ?? '')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="fecha" class="form-label fw-medium">Fecha</label>
                        <input type="date" class="form-control" id="fecha" name="fecha"
                               value="<?= esc(old('fecha', $gasto['fecha'] ?? date('Y-m-d'))) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="grupo_id" class="form-label fw-medium">Grupo</label>
                        <?php if (!empty($grupoId) && !isset($gasto)): ?>
                        <?php
                            $grupoActual = array_filter($grupos, fn($g) => $g['id'] == $grupoId);
                            $grupoActual = reset($grupoActual);
                        ?>
                        <input type="hidden" name="grupo_id" value="<?= $grupoId ?>">
                        <input type="text" class="form-control" value="<?= esc($grupoActual['nombre'] ?? '') ?>" disabled>
                        <?php else: ?>
                        <select class="form-select" id="grupo_id" name="grupo_id" required>
                            <option value="">Seleccionar grupo</option>
                            <?php foreach ($grupos as $g): ?>
                                <option value="<?= $g['id'] ?>" <?= ($grupoId ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="pagador_id" class="form-label fw-medium">Pagador</label>
                        <select class="form-select" id="pagador_id" name="pagador_id" required>
                            <option value="">Seleccionar pagador</option>
                            <?php if (isset($miembros)): ?>
                                <?php foreach ($miembros as $m): ?>
                                    <option value="<?= $m['user_id'] ?>" <?= old('pagador_id', $gasto['pagador_id'] ?? '') == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="categoria_id" class="form-label fw-medium">Categoría</label>
                        <select class="form-select" id="categoria_id" name="categoria_id">
                            <option value="">Sin categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= old('categoria_id', $gasto['categoria_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Participantes (división igualitaria)</label>
                        <?php if (isset($miembros)): ?>
                            <?php foreach ($miembros as $m): ?>
                                <div class="form-check py-1">
                                    <input class="form-check-input" type="checkbox" name="participantes[]"
                                           value="<?= $m['user_id'] ?>"
                                           id="participante_<?= $m['user_id'] ?>"
                                           <?= isset($participantesIds) && in_array($m['user_id'], $participantesIds) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="participante_<?= $m['user_id'] ?>">
                                        <?= esc($m['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Seleccioná un grupo primero.</p>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= !empty($grupoId) ? base_url('grupos/' . $grupoId) : base_url('gastos') ?>" class="btn btn-secondary flex-fill">Cancelar</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <?= isset($gasto) ? 'Guardar Cambios' : 'Crear Gasto' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($miembros) && count($miembros) > 0 && !isset($gasto)): ?>
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <h6 class="fw-bold">Vista previa de división</h6>
                <p class="text-muted small mb-0">
                    Al seleccionar participantes, el monto se dividirá en partes iguales.
                    El pagador puede ser también participante.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!isset($gasto) && empty($grupoId)): ?>
    <script>
        document.getElementById('grupo_id').addEventListener('change', function() {
            if (this.value) {
                window.location.href = '<?= base_url('gastos/nuevo?grupo_id=') ?>' + this.value;
            }
        });
    </script>
    <?php endif; ?>
<?= view('partials/_footer') ?>
