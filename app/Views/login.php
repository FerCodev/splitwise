<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - Iniciar Sesi&oacute;n</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/app.css') ?>" rel="stylesheet">
</head>
<body style="background:var(--surface);min-height:100vh;display:flex;align-items:center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div style="text-align:center;margin-bottom:32px;">
                    <h1 style="font-size:28px;font-weight:800;color:var(--primary);letter-spacing:-0.02em;">SplitWise</h1>
                    <p style="color:var(--muted);font-size:14px;margin:4px 0 0;">Gestion&aacute; tus gastos compartidos</p>
                </div>
                <div class="card">
                    <div class="card-body" style="padding:24px;">

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
                        <?php endif; ?>

                        <form action="<?= base_url('login') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contrase&ntilde;a</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                        </form>
                        <div style="text-align:center;margin-top:16px;">
                            <a href="<?= base_url('password/olvidada') ?>" style="font-size:13px;color:var(--muted);">Olvid&eacute; mi contrase&ntilde;a</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
