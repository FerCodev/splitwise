<?= view('partials/_head', ['title' => 'SplitWise - ' . $grupo['nombre']]) ?>
<?= view('partials/_navbar') ?>

    <div class="container mt-3 mt-md-4">
        <a href="<?= base_url('grupos') ?>" class="btn btn-outline-secondary btn-sm mb-3">&larr; Volver</a>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="card-title mb-1 fw-bold"><?= esc($grupo['nombre']) ?></h3>
                        <p class="text-muted small mb-0">
                            Creado el <?= date('d/m/Y H:i', strtotime($grupo['created_at'])) ?>
                        </p>
                        <span class="badge bg-<?= $rol === 'admin' ? 'warning' : 'secondary' ?> mt-2">
                            <?= $rol === 'admin' ? 'Administrador' : 'Miembro' ?>
                        </span>
                        <?php
                            $badgeEstado = [
                                'activo' => 'bg-success',
                                'cerrado' => 'bg-warning text-dark',
                                'liquidado' => 'bg-secondary',
                            ];
                            $claseEstado = $badgeEstado[$grupo['estado']] ?? 'bg-secondary';
                        ?>
                        <span class="badge <?= $claseEstado ?> mt-2 ms-1">
                            <?= ucfirst($grupo['estado']) ?>
                        </span>
                    </div>
                    <?php if ($permisos['puede_editar_grupo'] || $permisos['puede_eliminar_grupo']): ?>
                        <div class="d-flex gap-1">
                            <?php if ($permisos['puede_editar_grupo']): ?>
                                <a href="<?= base_url('grupos/' . $grupo['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                            <?php endif; ?>
                            <?php if ($permisos['puede_eliminar_grupo']): ?>
                                <form action="<?= base_url('grupos/' . $grupo['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar grupo?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($grupo['descripcion']): ?>
                    <p class="mt-3 mb-0"><?= esc($grupo['descripcion']) ?></p>
                <?php endif; ?>

                <div class="d-flex gap-2 mt-3">
                    <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-info flex-fill">Ver balance</a>
                    <?php if ($permisos['puede_crear_gasto']): ?>
                        <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary flex-fill">+ Gasto</a>
                    <?php endif; ?>
                    <?php if ($permisos['puede_crear_pago']): ?>
                        <a href="<?= base_url('pagos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-success flex-fill">+ Pago</a>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <?php if ($permisos['puede_cambiar_estado']): ?>
            <div class="card border-0 shadow-sm mb-4">
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Miembros (<?= count($miembros) ?>)</h5>
            </div>
            <div class="card-body p-0 d-none d-md-block">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Desde</th>
                                <?php if ($permisos['puede_cambiar_rol'] || $permisos['puede_quitar_miembro']): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($miembros as $miembro): ?>
                                <tr>
                                    <td class="fw-medium"><?= esc($miembro['name']) ?></td>
                                    <td><?= esc($miembro['email']) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $miembro['rol'] === 'admin' ? 'warning' : 'secondary' ?>">
                                            <?= $miembro['rol'] === 'admin' ? 'Admin' : 'Miembro' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($miembro['created_at'])) ?></td>
                                    <?php if ($permisos['puede_cambiar_rol'] || $permisos['puede_quitar_miembro']): ?>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <?php if ($miembro['user_id'] !== session()->get('userId')): ?>
                                                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $miembro['user_id'] . '/rol') ?>" method="post" class="d-inline">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="rol" value="<?= $miembro['rol'] === 'admin' ? 'member' : 'admin' ?>">
                                                        <button type="submit" class="btn btn-sm <?= $miembro['rol'] === 'admin' ? 'btn-outline-secondary' : 'btn-outline-warning' ?>">
                                                            <?= $miembro['rol'] === 'admin' ? 'Hacer miembro' : 'Hacer admin' ?>
                                                        </button>
                                                    </form>
                                                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $miembro['user_id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Quitar este miembro del grupo?')">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">Quitar</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-md-none">
                <?php foreach ($miembros as $miembro): ?>
                    <div class="mobile-card-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-medium"><?= esc($miembro['name'])?></div>
                                <div class="text-muted small"><?= esc($miembro['email'])?></div>
                                <div class="mt-1">
                                    <span class="badge bg-<?= $miembro['rol'] === 'admin' ? 'warning' : 'secondary' ?>">
                                        <?= $miembro['rol'] === 'admin' ? 'Admin' : 'Miembro' ?>
                                    </span>
                                    <span class="text-muted small ms-2"><?= date('d/m/Y', strtotime($miembro['created_at'])) ?></span>
                                </div>
                            </div>
                                    <?php if (($permisos['puede_cambiar_rol'] || $permisos['puede_quitar_miembro']) && $miembro['user_id'] !== session()->get('userId')): ?>
                                        <div class="d-flex flex-column gap-1">
                                            <?php if ($permisos['puede_cambiar_rol']): ?>
                                            <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $miembro['user_id'] . '/rol') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="rol" value="<?= $miembro['rol'] === 'admin' ? 'member' : 'admin' ?>">
                                                <button type="submit" class="btn btn-sm <?= $miembro['rol'] === 'admin' ? 'btn-outline-secondary' : 'btn-outline-warning' ?> w-100">
                                                    <?= $miembro['rol'] === 'admin' ? 'Hacer miembro' : 'Hacer admin' ?>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <?php if ($permisos['puede_quitar_miembro']): ?>
                                            <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros/' . $miembro['user_id']) ?>" method="post" onsubmit="return confirm('¿Quitar este miembro del grupo?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">Quitar</button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($permisos['puede_agregar_miembro'] && !empty($usuariosDisponibles)): ?>
                <div class="card-footer bg-white">
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
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Gastos (<?= count($gastos) ?>)</h5>
                <?php if ($permisos['puede_crear_gasto']): ?>
                    <a href="<?= base_url('gastos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-primary btn-sm">+ Nuevo</a>
                <?php endif; ?>
            </div>
            <?php if (empty($gastos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay gastos en este grupo.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Monto</th>
                                    <th>Pagó</th>
                                    <th>Participantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastos as $gasto): ?>
                                    <?php $puedeEditarGasto = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'gasto_edit', session()->get('userId'), (int) $gasto['pagador_id']) === null; ?>
                                    <?php $puedeEliminarGasto = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'gasto_delete', session()->get('userId'), (int) $gasto['pagador_id']) === null; ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($gasto['fecha'])) ?></td>
                                        <td><?= esc($gasto['descripcion']) ?></td>
                                        <td class="text-end fw-medium">$<?= number_format($gasto['monto'], 2) ?></td>
                                        <td><?= esc($gasto['pagador_nombre']) ?></td>
                                        <td><?= $gasto['total_participantes'] ?></td>
                                        <td>
                                            <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                            <?php if ($puedeEditarGasto): ?>
                                                <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                            <?php endif; ?>
                                            <?php if ($puedeEliminarGasto): ?>
                                                <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar gasto?')">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total gastado</th>
                                    <th class="text-end">$<?= number_format($totalGastado, 2) ?></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($gastos as $gasto): ?>
                        <?php $puedeEditarGasto = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'gasto_edit', session()->get('userId'), (int) $gasto['pagador_id']) === null; ?>
                        <?php $puedeEliminarGasto = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'gasto_delete', session()->get('userId'), (int) $gasto['pagador_id']) === null; ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-medium"><?= esc($gasto['descripcion']) ?></div>
                                <div class="fw-bold text-primary">$<?= number_format($gasto['monto'], 2) ?></div>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= date('d/m/Y', strtotime($gasto['fecha'])) ?> &middot;
                                Pagó: <?= esc($gasto['pagador_nombre']) ?> &middot;
                                <?= $gasto['total_participantes'] ?> participante(s)
                            </div>
                            <div class="mt-2 d-flex gap-1">
                                <a href="<?= base_url('gastos/' . $gasto['id']) ?>" class="btn btn-outline-info btn-sm flex-fill">Ver</a>
                                <?php if ($puedeEditarGasto): ?>
                                    <a href="<?= base_url('gastos/' . $gasto['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm flex-fill">Editar</a>
                                <?php endif; ?>
                                <?php if ($puedeEliminarGasto): ?>
                                    <form action="<?= base_url('gastos/' . $gasto['id']) ?>" method="post" class="flex-fill" onsubmit="return confirm('¿Eliminar gasto?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mobile-card-item bg-light fw-medium">
                        <div class="d-flex justify-content-between">
                            <span>Total gastado</span>
                            <span>$<?= number_format($totalGastado, 2) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Pagos (<?= count($pagos) ?>)</h5>
                <?php if ($permisos['puede_crear_pago']): ?>
                    <a href="<?= base_url('pagos/nuevo?grupo_id=' . $grupo['id']) ?>" class="btn btn-success btn-sm">+ Nuevo</a>
                <?php endif; ?>
            </div>
            <?php if (empty($pagos)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay pagos en este grupo.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th class="text-end">Monto</th>
                                    <th>Pagó</th>
                                    <th>Recibió</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pago): ?>
                                    <?php $puedeEditarPago = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'pago_edit', session()->get('userId'), (int) $pago['pagador_id']) === null; ?>
                                    <?php $puedeEliminarPago = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'pago_delete', session()->get('userId'), (int) $pago['pagador_id']) === null; ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($pago['fecha'])) ?></td>
                                        <td><?= esc($pago['descripcion'] ?: '-') ?></td>
                                        <td class="text-end fw-medium">$<?= number_format($pago['monto'], 2) ?></td>
                                        <td><?= esc($pago['pagador_nombre']) ?></td>
                                        <td><?= esc($pago['receptor_nombre']) ?></td>
                                        <td>
                                            <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-sm btn-outline-info">Ver</a>
                                            <?php if ($puedeEditarPago): ?>
                                                <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                                            <?php endif; ?>
                                            <?php if ($puedeEliminarPago): ?>
                                                <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('¿Eliminar pago?')">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="_method" value="DELETE">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2">Total pagado</th>
                                    <th class="text-end">$<?= number_format($totalPagado, 2) ?></th>
                                    <th colspan="3"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($pagos as $pago): ?>
                        <?php $puedeEditarPago = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'pago_edit', session()->get('userId'), (int) $pago['pagador_id']) === null; ?>
                        <?php $puedeEliminarPago = \App\Services\GroupPermission::check($rol, $grupo['estado'], 'pago_delete', session()->get('userId'), (int) $pago['pagador_id']) === null; ?>
                        <div class="mobile-card-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-medium"><?= esc($pago['descripcion'] ?: 'Pago') ?></div>
                                <div class="fw-bold text-success">$<?= number_format($pago['monto'], 2) ?></div>
                            </div>
                            <div class="text-muted small mt-1">
                                <?= date('d/m/Y', strtotime($pago['fecha'])) ?> &middot;
                                <?= esc($pago['pagador_nombre']) ?> pagó a <?= esc($pago['receptor_nombre']) ?>
                            </div>
                            <div class="text-muted small">Grupo: <?= esc($pago['grupo_nombre']) ?></div>
                            <div class="mt-2 d-flex gap-1">
                                <a href="<?= base_url('pagos/' . $pago['id']) ?>" class="btn btn-outline-info btn-sm flex-fill">Ver</a>
                                <?php if ($puedeEditarPago): ?>
                                    <a href="<?= base_url('pagos/' . $pago['id'] . '/editar') ?>" class="btn btn-outline-primary btn-sm flex-fill">Editar</a>
                                <?php endif; ?>
                                <?php if ($puedeEliminarPago): ?>
                                    <form action="<?= base_url('pagos/' . $pago['id']) ?>" method="post" class="flex-fill" onsubmit="return confirm('¿Eliminar pago?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mobile-card-item bg-light fw-medium">
                        <div class="d-flex justify-content-between">
                            <span>Total pagado</span>
                            <span>$<?= number_format($totalPagado, 2) ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Balance</h5>
                <a href="<?= base_url('grupos/' . $grupo['id'] . '/balance') ?>" class="btn btn-outline-info btn-sm">Ver detalle</a>
            </div>
            <?php if (empty($balance)): ?>
                <div class="card-body text-center py-4">
                    <p class="text-muted mb-0">No hay datos de balance para mostrar.</p>
                </div>
            <?php else: ?>
                <div class="card-body p-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th class="text-end">Pagó en gastos</th>
                                    <th class="text-end">Consumió</th>
                                    <th class="text-end">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($balance as $b): ?>
                                    <tr>
                                        <td class="fw-medium"><?= esc($b['name']) ?></td>
                                        <td class="text-end">$<?= number_format($b['total_pagado_gastos'], 2) ?></td>
                                        <td class="text-end">$<?= number_format($b['total_consumido'], 2) ?></td>
                                        <td class="text-end fw-medium <?= $b['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            $<?= number_format($b['saldo'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="d-md-none">
                    <?php foreach ($balance as $b): ?>
                        <div class="mobile-card-item">
                            <div class="fw-medium"><?= esc($b['name']) ?></div>
                            <div class="d-flex justify-content-between small mt-1">
                                <span class="text-muted">Pagó:</span>
                                <span>$<?= number_format($b['total_pagado_gastos'], 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted">Consumió:</span>
                                <span>$<?= number_format($b['total_consumido'], 2) ?></span>
                            </div>
                            <div class="fw-medium mt-1 <?= $b['saldo'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                Saldo: $<?= number_format($b['saldo'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($deudas)): ?>
                    <div class="card-footer bg-white">
                        <h6 class="mb-2 fw-bold">Transferencias sugeridas</h6>
                        <?php foreach ($deudas as $d): ?>
                            <div class="py-1">
                                <strong><?= esc($d['deudor']) ?></strong> debe
                                <strong>$<?= number_format($d['monto'], 2) ?></strong> a
                                <strong><?= esc($d['acreedor']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

<?= view('partials/_footer') ?>
