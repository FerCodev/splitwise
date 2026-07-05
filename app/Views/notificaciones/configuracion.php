<?php
    $pushConfigured = $pushConfigured ?? false;
    $publicKey = $publicKey ?? '';
    $prefs = $prefs ?? [];
?>
<?= view('partials/_head', ['title' => 'Configuración de notificaciones - Gastito']) ?>
<?= view('partials/_navbar', ['pageTitle' => 'Configuración']) ?>
<div class="container py-4">
    <h2 class="mb-4 d-none d-md-block">Configuraci&oacute;n de notificaciones</h2>

    <?= view('partials/_feedback') ?>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Preferencias</h5>
            <form method="post" action="<?= base_url('notificaciones/configuracion') ?>">
                <?= csrf_field() ?>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="push_enabled" name="push_enabled" value="1" <?= !empty($prefs['push_enabled']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="push_enabled">Notificaciones push</label>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="expense_created" name="expense_created" value="1" <?= !empty($prefs['expense_created']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="expense_created">Notificar nuevos gastitos</label>
                </div>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="show_amounts" name="show_amounts" value="1" <?= !empty($prefs['show_amounts']) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_amounts">Mostrar importes en notificaciones</label>
                </div>
                <button type="submit" class="btn btn-primary">Guardar preferencias</button>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Notificaciones en este dispositivo</h5>

            <?php if (!$pushConfigured): ?>
            <div class="alert alert-warning mb-3">
                <strong>Web Push no est&aacute; configurado.</strong><br>
                El administrador debe configurar las claves VAPID en el servidor para habilitar las notificaciones push.
            </div>
            <?php endif; ?>

            <div id="push-status" class="mb-3">
                <div id="push-unsupported" class="alert alert-danger d-none">
                    Tu navegador no soporta notificaciones push.
                </div>
                <div id="push-insecure" class="alert alert-warning d-none">
                    Las notificaciones push requieren HTTPS o localhost.
                </div>
                <div id="push-status-text" class="text-muted small"></div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <button id="btn-activate-push" class="btn btn-primary d-none">Activar notificaciones en este dispositivo</button>
                <button id="btn-deactivate-push" class="btn btn-outline-danger d-none">Desactivar en este dispositivo</button>
                <?php if ($pushConfigured): ?>
                <button id="btn-test-push" class="btn btn-outline-secondary d-none">Enviar prueba</button>
                <?php endif; ?>
            </div>

            <div id="push-result" class="mt-2 small"></div>

            <div class="alert alert-info mt-3">
                <strong>iPhone / iPad:</strong> Para recibir notificaciones, la app debe estar agregada a la pantalla de inicio (PWA instalada). El permiso debe solicitarse desde una acci&oacute;n directa del usuario.
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="<?= base_url('notificaciones') ?>" class="btn btn-outline-secondary">Volver a notificaciones</a>
    </div>
</div>

<script>window.Gastito = {baseUrl: '<?= base_url() ?>'};</script>
<script src="<?= base_url('assets/push-subscription.js') ?>"></script>
<?= view('partials/_footer') ?>
