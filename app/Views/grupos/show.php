<?= view('partials/_head', ['title' => 'SplitWise - ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <!-- Acciones principales -->
        <div class="d-flex gap-2 mb-3 flex-wrap">
            <?php if ($permisos['puede_crear_gasto']): ?>
                <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary flex-fill">+ Gasto</a>
            <?php endif; ?>
            <?php if ($permisos['puede_crear_pago']): ?>
                <a href="<?= base_url('pagos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-success flex-fill">+ Pago</a>
            <?php endif; ?>
            <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-info flex-fill">Balance</a>
        </div>

        <!-- Cabecera del grupo -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h4 class="fw-bold mb-1"><?= esc($grupo['nombre']) ?></h4>
                        <div class="d-flex gap-1 flex-wrap">
                            <span class="badge bg-<?= $rol === 'admin' ? 'warning' : 'secondary' ?> mt-1">
                                <?= $rol === 'admin' ? 'Administrador' : 'Miembro' ?>
                            </span>
                            <?php
                                $badgeEstado = ['activo' => 'bg-success', 'cerrado' => 'bg-warning text-dark', 'liquidado' => 'bg-secondary'];
                                $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $claseEstado ?> mt-1"><?= ucfirst($grupo['estado']) ?></span>
                            <?php if ($permisos['puede_editar_grupo']): ?>
                                <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm mt-1">Editar</a>
                            <?php endif; ?>
                            <?php if ($permisos['puede_eliminar_grupo']): ?>
                                <form action="<?= base_url('grupos/' . $grupo['id']) ?>" method="post" class="d-inline" id="delete-grupo-<?= $grupo['id'] ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="button" class="btn btn-outline-danger btn-sm mt-1"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-confirm-title="Eliminar grupo"
                                        data-confirm-msg="Se eliminará el grupo y ya no podrás consultarlo. Esta acción no se puede deshacer."
                                        data-confirm-btn="Eliminar grupo"
                                        data-confirm-form="delete-grupo-<?= $grupo['id'] ?>">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if ($grupo['descripcion']): ?>
                    <p class="mt-2 mb-0 text-muted small"><?= esc($grupo['descripcion']) ?></p>
                <?php endif; ?>
                <p class="text-muted small mt-1 mb-0">
                    Creado el <?= date('d/m/Y H:i', strtotime($grupo['created_at'])) ?>
                </p>
            </div>
        </div>

        <!-- Balance rapido -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <?php
                    $miBalance = current(array_filter($balance, fn($b) => $b['user_id'] == session()->get('userId')));
                ?>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small">Tu saldo en este grupo</span>
                        <div class="fw-bold fs-5 <?= ($miBalance['saldo'] ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>">
                            $<?= number_format(abs($miBalance['saldo'] ?? 0), 2) ?>
                            <small class="fw-normal text-muted fs-6"><?= ($miBalance['saldo'] ?? 0) >= 0 ? 'a favor' : 'debe' ?></small>
                        </div>
                    </div>
                    <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-info btn-sm">Ver detalle</a>
                </div>
                <?php if (!empty($deudas)): ?>
                    <div class="mt-2 small text-muted">
                        <?= count($deudas) ?> transferencia(s) sugerida(s)
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Movimientos recientes del grupo -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Movimientos</h5>
                <div class="d-flex gap-1">
                    <a href="<?= base_url('gastos?grupo_id=' . $grupo['id']) ?>" class="btn btn-outline-primary btn-sm">Gastos</a>
                    <a href="<?= base_url('pagos?grupo_id=' . $grupo['id']) ?>" class="btn btn-outline-success btn-sm">Pagos</a>
                </div>
            </div>
            <?php if (empty($gastos) && empty($pagos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay movimientos en este grupo todav&iacute;a.</p>
                </div>
            <?php else: ?>
                <?php
                    $movimientos = [];
                    foreach ($gastos as $g) {
                        $movimientos[] = [
                            'tipo' => 'gasto',
                            'fecha' => $g['fecha'],
                            'descripcion' => $g['descripcion'],
                            'monto' => $g['monto'],
                            'persona' => $g['pagador_nombre'],
                            'id' => $g['id'],
                        ];
                    }
                    foreach ($pagos as $p) {
                        $movimientos[] = [
                            'tipo' => 'pago',
                            'fecha' => $p['fecha'],
                            'descripcion' => $p['descripcion'] ?: 'Pago',
                            'monto' => $p['monto'],
                            'persona' => $p['pagador_nombre'] . ' pag&oacute; a ' . $p['receptor_nombre'],
                            'id' => $p['id'],
                        ];
                    }
                    usort($movimientos, fn($a, $b) => strcmp($b['fecha'] . 'z', $a['fecha'] . 'z'));
                    $movimientos = array_slice($movimientos, 0, 5);
                ?>
                <?php foreach ($movimientos as $m): ?>
                    <div class="mobile-card-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge <?= $m['tipo'] === 'gasto' ? 'bg-primary' : 'bg-success' ?>"><?= $m['tipo'] === 'gasto' ? 'Gasto' : 'Pago' ?></span>
                                <span class="fw-medium small ms-1"><?= esc($m['descripcion']) ?></span>
                            </div>
                            <span class="fw-bold small <?= $m['tipo'] === 'gasto' ? 'text-primary' : 'text-success' ?>">$<?= number_format($m['monto'], 2) ?></span>
                        </div>
                        <div class="text-muted small">
                            <?= date('d/m/Y', strtotime($m['fecha'])) ?> &middot; <?= $m['persona'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if (count($gastos) + count($pagos) > 5): ?>
                    <div class="card-body text-center py-2">
                        <a href="<?= base_url('gastos?grupo_id=' . $grupo['id']) ?>" class="text-decoration-none small">Ver todos los movimientos &rarr;</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Miembros (collapsible en mobile, tabla en desktop) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Miembros (<?= count($miembros) ?>)</h5>
                <?php if ($permisos['puede_agregar_miembro'] && !empty($usuariosDisponibles)): ?>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="collapse" data-bs-target="#agregarMiembroForm" aria-expanded="false">
                        + Agregar
                    </button>
                <?php endif; ?>
            </div>
            <?php if ($permisos['puede_agregar_miembro'] && !empty($usuariosDisponibles)): ?>
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
                                <?php if ($permisos['puede_cambiar_rol'] || $permisos['puede_quitar_miembro']): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($miembros as $miembro): ?>
                                <tr>
                                    <td class="fw-medium"><?= esc($miembro['name']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $miembro['rol'] === 'admin' ? 'warning' : 'secondary' ?>">
                                            <?= $miembro['rol'] === 'admin' ? 'Admin' : 'Miembro' ?>
                                        </span>
                                    </td>
                                    <?php if ($permisos['puede_cambiar_rol'] || $permisos['puede_quitar_miembro']): ?>
                                        <td>
                                            <?php if ($miembro['user_id'] !== session()->get('userId')): ?>
                                                <?php if ($permisos['puede_cambiar_rol']): ?>
                                                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $miembro['user_id'] . '/rol') ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="rol" value="<?= $miembro['rol'] === 'admin' ? 'member' : 'admin' ?>">
                                                        <button type="submit" class="btn btn-sm <?= $miembro['rol'] === 'admin' ? 'btn-outline-secondary' : 'btn-outline-warning' ?>">
                                                            <?= $miembro['rol'] === 'admin' ? 'Hacer miembro' : 'Hacer admin' ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($permisos['puede_quitar_miembro']): ?>
                                                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $miembro['user_id']) ?>" method="post" class="d-inline" id="quitar-miembro-<?= $miembro['user_id'] ?>">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                            data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                            data-confirm-title="Quitar miembro"
                                                            data-confirm-msg="Se quitará este usuario del grupo. Si tiene movimientos asociados, el sistema puede impedir la acción."
                                                            data-confirm-btn="Quitar miembro"
                                                            data-confirm-form="quitar-miembro-<?= $miembro['user_id'] ?>">Quitar</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Estado del grupo -->
        <?php if ($permisos['puede_cambiar_estado']): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Estado del grupo</h5>
                </div>
                <div class="card-body">
                    <?php if ($grupo['estado'] === 'activo'): ?>
                        <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post" id="cerrar-grupo-<?= $grupo['id'] ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="estado" value="cerrado">
                            <button type="button" class="btn btn-outline-warning"
                                data-bs-toggle="modal" data-bs-target="#confirmModal"
                                data-confirm-title="Cerrar grupo"
                                data-confirm-msg="Se cerrará el grupo. No se podrán crear ni editar gastos, solo registrar pagos para saldar deudas."
                                data-confirm-btn="Cerrar grupo"
                                data-confirm-class="btn-warning"
                                data-confirm-form="cerrar-grupo-<?= $grupo['id'] ?>">Cerrar grupo</button>
                        </form>
                    <?php elseif ($grupo['estado'] === 'cerrado'): ?>
                        <div class="d-flex gap-2 flex-wrap">
                            <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post" id="reabrir-grupo-<?= $grupo['id'] ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="estado" value="activo">
                                <button type="button" class="btn btn-outline-success"
                                    data-bs-toggle="modal" data-bs-target="#confirmModal"
                                    data-confirm-title="Reabrir grupo"
                                    data-confirm-msg="Se reabrirá el grupo. Los miembros podrán crear y editar gastos nuevamente."
                                    data-confirm-btn="Reabrir grupo"
                                    data-confirm-class="btn-success"
                                    data-confirm-form="reabrir-grupo-<?= $grupo['id'] ?>">Reabrir grupo</button>
                            </form>
                            <?php if (empty($deudas)): ?>
                                <form action="<?= base_url('grupos/' . $grupo['id'] . '/estado') ?>" method="post" id="liquidar-grupo-<?= $grupo['id'] ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="estado" value="liquidado">
                                    <button type="button" class="btn btn-outline-secondary"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-confirm-title="Liquidar grupo"
                                        data-confirm-msg="Se liquidará el grupo. Esta acción es definitiva y no se puede deshacer."
                                        data-confirm-btn="Liquidar grupo"
                                        data-confirm-class="btn-secondary"
                                        data-confirm-form="liquidar-grupo-<?= $grupo['id'] ?>">Liquidar grupo</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($grupo['estado'] === 'liquidado'): ?>
                        <p class="text-muted mb-0">Este grupo est&aacute; finalizado. No se pueden realizar m&aacute;s cambios.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
