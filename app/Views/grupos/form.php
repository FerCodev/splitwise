<?= view('partials/_head', ['title' => 'Gastito - ' . (isset($grupo) ? 'Editar' : 'Nuevo') . ' Grupo']) ?>
<?= view('partials/_navbar', ['pageTitle' => isset($grupo) ? 'Editar grupo' : 'Nuevo grupo']) ?>

    <div class="container mt-3 mt-md-4">
        <h2 class="fw-bold mb-4 d-none d-md-block"><?= isset($grupo) ? 'Editar Grupo' : 'Nuevo Grupo' ?></h2>

        <?= view('partials/_validation_errors') ?>
        <?= view('partials/_feedback') ?>

        <?php if (!isset($grupo) || ($rol ?? '') === 'admin'): ?>
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
                                        <label class="initial-member-option" for="miembro_<?= $u['id'] ?>">
                                            <input class="form-check-input" type="checkbox" name="miembros[]"
                                                   value="<?= $u['id'] ?>" id="miembro_<?= $u['id'] ?>">
                                            <?= view('components/avatar', [
                                                'interactive' => false,
                                                'userId' => $u['id'],
                                                'name' => $u['name'],
                                                'avatarFilename' => $u['avatar_filename'] ?? null,
                                                'avatarUpdatedAt' => $u['avatar_updated_at'] ?? null,
                                                'size' => 40,
                                            ]) ?>
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
        <?php endif; ?>

            <?php if (isset($grupo) && isset($miembros)): ?>
            <?php if (($rol ?? '') === 'admin'): ?>
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
                                    data-confirm-msg="Se cerrara el grupo. No se podran crear ni editar gastitos, solo registrar pagos para saldar deudas."
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
                                        data-confirm-msg="Se reabrira el grupo. Los miembros podran crear y editar gastitos nuevamente."
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
                <?php if ($permisos['puede_agregar_miembro'] ?? false): ?>
                <div class="card-body border-bottom">
                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/miembros') ?>" method="post" class="row g-2 align-items-end">
                        <?= csrf_field() ?>
                        <div class="col-8 col-md-10">
                            <select name="user_id" class="form-select" required <?= empty($usuariosDisponibles) ? 'disabled' : '' ?>>
                                <option value=""><?= empty($usuariosDisponibles) ? 'No hay amigos disponibles' : 'Agregar amigo...' ?></option>
                                <?php foreach ($usuariosDisponibles as $u): ?>
                                    <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?><?= !empty($u['username']) ? ' (@' . esc($u['username']) . ')' : '' ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-4 col-md-2">
                            <button type="submit" class="btn btn-primary w-100" <?= empty($usuariosDisponibles) ? 'disabled' : '' ?>>Agregar</button>
                        </div>
                        <?php if (empty($usuariosDisponibles)): ?>
                            <div class="col-12"><span class="text-muted small">Primero agreg&aacute; y acept&aacute; una amistad desde <a href="<?= base_url('amigos') ?>">Amigos</a>.</span></div>
                        <?php endif; ?>
                    </form>
                    <hr>
                    <form action="<?= base_url('grupos/' . $grupo['id'] . '/invitaciones') ?>" method="post" class="row g-2 align-items-end">
                        <?= csrf_field() ?>
                        <div class="col-8 col-md-10">
                            <label for="invitation-email" class="form-label small fw-bold">Invitar a alguien que no usa Gastito</label>
                            <input type="email" id="invitation-email" name="email" class="form-control" required placeholder="persona@ejemplo.com">
                        </div>
                        <div class="col-4 col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Invitar</button></div>
                    </form>
                    <?php if ($devLink = session()->getFlashdata('dev_invitation_link')): ?>
                        <div class="alert alert-info mt-3 mb-0"><strong>Enlace de desarrollo:</strong> <a href="<?= esc($devLink) ?>"><?= esc($devLink) ?></a></div>
                    <?php endif; ?>
                    <?php if (!empty($invitacionesPendientes)): ?>
                        <div class="mt-3"><div class="small fw-bold mb-2">Invitaciones pendientes</div>
                        <?php foreach ($invitacionesPendientes as $invite): ?>
                            <div class="group-invitation-row">
                                <div><strong><?= esc($invite['email']) ?></strong><small>Vence <?= date('d/m/Y H:i', strtotime($invite['expires_at'])) ?></small></div>
                                <div class="d-flex gap-1">
                                    <form method="post" action="<?= base_url('grupos/'.$grupo['id'].'/invitaciones/'.$invite['id'].'/reenviar') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-primary">Reenviar</button></form>
                                    <form method="post" action="<?= base_url('grupos/'.$grupo['id'].'/invitaciones/'.$invite['id'].'/cancelar') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Cancelar</button></form>
                                </div>
                            </div>
                        <?php endforeach; ?></div>
                    <?php endif; ?>
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
                                    <td class="fw-medium">
                                        <div class="d-flex align-items-center gap-2">
                                            <?= view('components/avatar', [
                                                'interactive' => false,
                                                'userId' => $m['user_id'], 'name' => $m['name'],
                                                'avatarFilename' => $m['avatar_filename'] ?? null,
                                                'avatarUpdatedAt' => $m['avatar_updated_at'] ?? null, 'size' => 36,
                                            ]) ?>
                                            <span><?= esc($m['name']) ?><?php if (!empty($m['username'])): ?> <small class="text-muted">@<?= esc($m['username']) ?></small><?php endif; ?></span>
                                        </div>
                                    </td>
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
                                                <?= view('components/forms/delete_form', [
                                                    'action' => base_url('grupos/' . $grupo['id'] . '/miembros/' . $m['user_id']),
                                                    'formId' => 'quitar-miembro-' . $m['user_id'],
                                                    'buttonClass' => 'btn btn-danger btn-sm',
                                                    'confirmTitle' => 'Quitar miembro',
                                                    'confirmMsg' => 'Se quitará este usuario del grupo.',
                                                    'confirmBtn' => 'Quitar miembro',
                                                ]) ?>
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

            <div class="card border-0 shadow-sm mt-4" id="colores">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">Colores por usuario</h5>
                    <small class="text-muted d-block mt-1">Estos colores se aplican solo a tu vista. Si los quit&aacute;s, cada gastito vuelve al color global que esa persona eligi&oacute; en su perfil.</small>
                </div>
                <div class="card-body">
                    <?php if (empty($miembros)): ?>
                        <p class="text-muted mb-0">El grupo todav&iacute;a no tiene miembros.</p>
                    <?php else: ?>
                        <div class="d-grid gap-3">
                            <?php foreach ($miembros as $miembro):
                                $tid = (int) $miembro['user_id'];
                                $effectiveKey = ($colorMap['miembros'] ?? [])[$tid] ?? \App\Services\UserColor::DEFAULT_KEY;
                                $effective = \App\Services\UserColor::get($effectiveKey);
                                $effBg = $effective['bg'] ?? \App\Services\UserColor::RESERVED['system']['bg'];
                                $effBorder = $effective['border'] ?? \App\Services\UserColor::RESERVED['system']['border'];
                                $effLabel = $effective['label'] ?? 'Autom&aacute;tico';
                            ?>
                                <form action="<?= base_url('grupos/' . (int) $grupo['id'] . '/colores/' . $tid) ?>" method="post" class="user-color-row">
                                    <?= csrf_field() ?>
                                    <div class="user-color-row-layout">
                                        <div class="user-color-current">
                                            <span class="user-color-swatch-circle flex-shrink-0" style="background: <?= esc($effBg) ?>; border-color: <?= esc($effBorder) ?>;"></span>
                                            <div class="min-width-0">
                                                <div class="fw-medium text-break"><?= esc($miembro['name']) ?></div>
                                                <div class="text-muted small">Color guardado: <?= $effLabel ?></div>
                                            </div>
                                        </div>
                                        <div class="user-color-swatches user-color-swatches-inline" role="radiogroup" aria-label="Color para <?= esc($miembro['name']) ?>">
                                            <?php foreach (($colorPalette ?? \App\Services\UserColor::PALETTE) as $key => $info): ?>
                                                <label class="user-color-swatch <?= $effectiveKey === $key ? 'is-selected' : '' ?>" title="<?= esc($info['label']) ?>">
                                                    <input type="radio" name="color" value="<?= esc($key) ?>" <?= $effectiveKey === $key ? 'checked' : '' ?> aria-label="<?= esc($info['label']) ?>">
                                                    <span class="user-color-swatch-circle" style="background: <?= esc($info['bg']) ?>; border-color: <?= esc($info['border']) ?>;"></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="user-color-actions">
                                            <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                                            <button type="submit" name="action" value="reset" class="btn btn-sm btn-outline-secondary" title="Volver al color global">Global</button>
                                        </div>
                                    </div>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
    </div>

<?php if (isset($grupo)): ?>
<script>
(function () {
    var storageKey = 'gastito:group-colors-changed:<?= (int) $grupo['id'] ?>';
    var currentUrl = new URL(window.location.href);

    if (currentUrl.searchParams.delete('_color_refresh')) {
        window.history.replaceState(null, '', currentUrl.pathname + currentUrl.search + currentUrl.hash);
    }

    document.querySelectorAll('#colores .user-color-row').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            var submitter = event.submitter;
            var formData = new FormData(form);
            if (submitter && submitter.name) {
                formData.set(submitter.name, submitter.value);
            }

            var buttons = form.querySelectorAll('button[type="submit"]');
            buttons.forEach(function (button) { button.disabled = true; });

            try {
                var actionUrl = form.getAttribute('action');
                var response = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                var contentType = response.headers.get('content-type') || '';

                if (!contentType.includes('application/json')) {
                    if (response.redirected && response.url) {
                        window.location.replace(response.url);
                    } else {
                        window.location.reload();
                    }
                    return;
                }

                var payload = await response.json();
                if (payload.ok) {
                    sessionStorage.setItem(storageKey, '1');
                }

                var redirectUrl = new URL(payload.redirect || window.location.href, window.location.origin);
                redirectUrl.searchParams.set('_color_refresh', Date.now().toString());
                window.location.replace(redirectUrl.toString());
            } catch (error) {
                buttons.forEach(function (button) { button.disabled = false; });
                GastitoFeedback.show('error', 'No se pudo guardar el color. Intentá nuevamente.');
            }
        });
    });
})();
</script>
<?php endif; ?>

<?= view('partials/_confirm_modal') ?>
<?= view('partials/_footer') ?>
