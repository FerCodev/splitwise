<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - Nueva contrase&ntilde;a</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-1">SplitWise</h3>
                        <p class="text-center text-muted small mb-4">Nueva contrase&ntilde;a</p>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                        <?php endif; ?>

                        <form action="<?= base_url('password/reset/' . $token) ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="password" class="form-label">Nueva contrase&ntilde;a</label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6" autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirmar contrase&ntilde;a</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Cambiar contrase&ntilde;a</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
