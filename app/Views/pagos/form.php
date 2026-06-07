<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - <?= isset($pago) ? 'Editar' : 'Nuevo' ?> Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?= base_url('dashboard') ?>">SplitWise</a>
            <div class="d-flex align-items-center">
                <a href="<?= base_url('grupos') ?>" class="btn btn-outline-light btn-sm me-2">Grupos</a>
                <span class="navbar-text me-3"><?= session()->get('userName') ?></span>
                <a href="<?= base_url('logout') ?>" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><?= isset($pago) ? 'Editar Pago' : 'Nuevo Pago' ?></h2>

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
                <form action="<?= isset($pago) ? base_url('pagos/' . $pago['id']) : base_url('pagos') ?>" method="post">
                    <?php if (isset($pago)): ?>
                        <input type="hidden" name="_method" value="PUT">
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="descripcion" class="form-label">Descripción <small class="text-muted">(opcional)</small></label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion"
                                   value="<?= esc(old('descripcion', $pago['descripcion'] ?? '')) ?>">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="monto" class="form-label">Monto</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="monto" name="monto"
                                   value="<?= esc(old('monto', $pago['monto'] ?? '')) ?>" required>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="fecha" name="fecha"
                                   value="<?= esc(old('fecha', $pago['fecha'] ?? date('Y-m-d'))) ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="grupo_id" class="form-label">Grupo</label>
                            <?php if (!empty($grupoId) && !isset($pago)): ?>
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
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pagador_id" class="form-label">Pagador (quien entrega el dinero)</label>
                            <select class="form-select" id="pagador_id" name="pagador_id" required>
                                <option value="">Seleccionar pagador</option>
                                <?php if (isset($miembros)): ?>
                                    <?php foreach ($miembros as $m): ?>
                                        <option value="<?= $m['user_id'] ?>" <?= old('pagador_id', $pago['pagador_id'] ?? '') == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="receptor_id" class="form-label">Receptor (quien recibe el dinero)</label>
                            <select class="form-select" id="receptor_id" name="receptor_id" required>
                                <option value="">Seleccionar receptor</option>
                                <?php if (isset($miembros)): ?>
                                    <?php foreach ($miembros as $m): ?>
                                        <option value="<?= $m['user_id'] ?>" <?= old('receptor_id', $pago['receptor_id'] ?? '') == $m['user_id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= !empty($grupoId) ? base_url('grupos/' . $grupoId) : base_url('pagos') ?>" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <?= isset($pago) ? 'Guardar Cambios' : 'Registrar Pago' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php if (!isset($pago) && empty($grupoId)): ?>
    <script>
        document.getElementById('grupo_id').addEventListener('change', function() {
            if (this.value) {
                window.location.href = '<?= base_url('pagos/nuevo?grupo_id=') ?>' + this.value;
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
