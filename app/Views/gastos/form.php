<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - <?= isset($gasto) ? 'Editar' : 'Nuevo' ?> Gasto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">SplitWise</a>
            <div class="d-flex align-items-center">
                <a href="<?= base_url('grupos') ?>" class="btn btn-outline-light btn-sm me-2">Grupos</a>
                <a href="<?= base_url('gastos') ?>" class="btn btn-outline-light btn-sm me-2">Gastos</a>
                <span class="navbar-text me-3"><?= session()->get('userName') ?></span>
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><?= isset($gasto) ? 'Editar Gasto' : 'Nuevo Gasto' ?></h2>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="<?= isset($gasto) ? base_url('gastos/' . $gasto['id']) : base_url('gastos') ?>" method="post">
                    <?php if (isset($gasto)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                   value="<?= esc(old('descripcion', $gasto['descripcion'] ?? '')) ?>" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="monto" class="form-label">Monto total</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="monto" name="monto"
                                   value="<?= esc(old('monto', $gasto['monto'] ?? '')) ?>" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha"
                                   value="<?= esc(old('fecha', $gasto['fecha'] ?? date('Y-m-d'))) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="grupo_id" class="form-label">Grupo</label>
                            <select class="form-select" id="grupo_id" name="grupo_id" required>
                                <option value="">Seleccionar grupo</option>
                                <?php foreach ($grupos as $g): ?>
                                    <option value="<?= $g['id'] ?>" <?= ($grupoId ?? '') == $g['id'] ? 'selected' : '' ?>><?= esc($g['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="pagador_id" class="form-label">Pagador</label>
                            <select class="form-select" id="pagador_id" name="pagador_id" required>
                                <option value="">Seleccionar pagador</option>
                                <?php if (isset($miembros)): ?>
                                    <?php foreach ($miembros as $m): ?>
                                        <option value="<?= $m['user_id'] ?>" <?= old('pagador_id', $gasto['pagador_id'] ?? '') == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Participantes (división igualitaria)</label>
                        <div class="row">
                            <?php if (isset($miembros)): ?>
                                <?php foreach ($miembros as $m): ?>
                                    <div class="col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="participantes[]"
                                                   value="<?= $m['user_id'] ?>"
                                                   id="participante_<?= $m['user_id'] ?>"
                                                   <?= isset($participantesIds) && in_array($m['user_id'], $participantesIds) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="participante_<?= $m['user_id'] ?>">
                                                <?= esc($m['name']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <p class="text-muted mb-0">Seleccioná un grupo primero.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= base_url('gastos') ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <?= isset($gasto) ? 'Guardar Cambios' : 'Crear Gasto' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (isset($miembros) && count($miembros) > 0 && !isset($gasto)): ?>
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title">Vista previa de división</h6>
                <p class="text-muted mb-0">
                    Al seleccionar participantes, el monto se dividirá en partes iguales.
                    El pagador puede ser también participante.
                </p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!isset($gasto)): ?>
    <script>
        document.getElementById('grupo_id').addEventListener('change', function() {
            if (this.value) {
                window.location.href = '<?= base_url('gastos/nuevo?grupo_id=') ?>' + this.value;
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
