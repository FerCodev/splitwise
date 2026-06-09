<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - Recuperar contrase&ntilde;a</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-1">SplitWise</h3>
                        <p class="text-center text-muted small mb-4">Recuperar contrase&ntilde;a</p>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('dev_reset_link')): ?>
                            <div class="alert alert-info small">
                                <strong>Modo desarrollo:</strong>
                                <a href="<?= session()->getFlashdata('dev_reset_link') ?>">Hac&eacute; clic ac&aacute; para restablecer tu contrase&ntilde;a</a>
                                <br>Este enlace se enviar&iacute;a por email en producci&oacute;n.
                            </div>
                        <?php endif; ?>

                        <form action="<?= base_url('password/olvidada') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Enviar enlace</button>
                        </form>

                        <div class="text-center mt-3">
                            <a href="<?= base_url('login') ?>" class="text-decoration-none small">Volver al inicio de sesi&oacute;n</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
