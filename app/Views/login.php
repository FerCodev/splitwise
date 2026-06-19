<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SplitWise - Iniciar Sesi&oacute;n</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/app.css') ?>" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100dvh;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 16px;
        }
        .login-brand {
            text-align: center;
            margin-bottom: 24px;
        }
        .login-brand h1 {
            font-size: 28px;
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -0.02em;
            margin: 0;
        }
        .login-brand p {
            color: var(--muted);
            font-size: 14px;
            margin: 4px 0 0;
        }
        .login-card {
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            background: var(--bg);
        }
        .login-card-body {
            padding: 20px;
        }
        .login-footer {
            text-align: center;
            margin-top: 16px;
        }
        .login-footer a {
            font-size: 13px;
            color: var(--muted);
            text-decoration: none;
        }
        @media (max-width: 575.98px) {
            .login-brand {
                margin-bottom: 16px;
            }
            .login-brand h1 {
                font-size: 24px;
            }
            .login-card-body {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-brand">
            <h1>SplitWise</h1>
            <p>Gestion&aacute; tus gastos compartidos</p>
        </div>
        <div class="login-card">
            <div class="login-card-body">

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
            </div>
        </div>
        <div class="login-footer">
            <a href="<?= base_url('password/olvidada') ?>">Olvid&eacute; mi contrase&ntilde;a</a>
        </div>
    </div>
</body>
</html>
