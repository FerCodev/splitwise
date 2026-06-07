<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitWise - <?= isset($gasto) ? 'Editar' : 'Nuevo' ?> Gasto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --touch-target: 44px; }
        .btn { min-height: var(--touch-target); display: inline-flex; align-items: center; justify-content: center; }
        .form-control, .form-select { min-height: var(--touch-target); font-size: 16px; }
        @media (max-width: 575.98px) {
            .card { border-radius: 12px; }
            .container { padding-left: 12px; padding-right: 12px; }
            h2 { font-size: 1.5rem; }
            .navbar-brand { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">SplitWise</a>
            <div class="d-flex align-items-center gap-1">
                <a href="<?= base_url('grupos') ?>" class="btn btn-outline-light btn-sm">Grupos</a>
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menú">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/></svg>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text"><?= session()->get('userName') ?></span></li>
                        <li><span class="dropdown-item-text text-muted small"><?= session()->get('userEmail') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= base_url('grupos') ?>">Grupos</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('gastos') ?>">Gastos</a></li>
                        <li><a class="dropdown-item" href="<?= base_url('pagos') ?>">Pagos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
