<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta - Gastito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/app.css') ?>" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh">
<div class="container py-4"><div class="row justify-content-center"><div class="col-md-5">
    <div class="card shadow border-0"><div class="card-body p-4">
        <h2 class="text-center fw-bold mb-2">Te invitaron a Gastito</h2>
        <p class="text-center text-muted mb-4"><strong><?= esc($invitation['inviter_name']) ?></strong> te invita a participar de <strong><?= esc($invitation['group_name']) ?></strong>.</p>
        <?= view('partials/_feedback') ?>
        <form method="post" action="<?= base_url('invitaciones/registro/' . $token) ?>">
            <?= csrf_field() ?>
            <div class="mb-3"><label class="form-label">Email</label><input class="form-control" value="<?= esc($invitation['email']) ?>" disabled></div>
            <div class="mb-3"><label for="name" class="form-label">Tu nombre</label><input id="name" name="name" class="form-control" required minlength="2" maxlength="255" value="<?= esc(old('name')) ?>" autofocus></div>
            <div class="mb-3"><label for="password" class="form-label">Contrase&ntilde;a</label><input id="password" name="password" type="password" class="form-control" required minlength="8" autocomplete="new-password"><div class="form-text">M&iacute;nimo 8 caracteres.</div></div>
            <div class="mb-3"><label for="password_confirm" class="form-label">Confirmar contrase&ntilde;a</label><input id="password_confirm" name="password_confirm" type="password" class="form-control" required minlength="8" autocomplete="new-password"></div>
            <button class="btn btn-primary w-100">Crear cuenta y entrar al grupo</button>
        </form>
        <p class="text-muted small text-center mt-3 mb-0">Este enlace vence el <?= date('d/m/Y H:i', strtotime($invitation['expires_at'])) ?> y solo puede usarse una vez.</p>
    </div></div>
</div></div></div>
</body></html>
