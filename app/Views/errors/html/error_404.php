<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitWise - P&aacute;gina no encontrada</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            height: 100vh;
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
            color: #555;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 3rem 2rem;
            text-align: center;
            max-width: 480px;
            width: 90%;
        }
        h1 {
            font-size: 4rem;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 0.5rem;
        }
        p {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            color: #666;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: #0d6efd;
            color: #fff;
            text-decoration: none;
            border-radius: 0.375rem;
            font-size: 1rem;
        }
        .btn:hover { background: #0b5ed7; }
    </style>
</head>
<body>
    <div class="card">
        <h1>404</h1>
        <p>P&aacute;gina no encontrada.</p>
        <?php $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\') ?: '/'; ?>
        <a href="<?= $basePath ?>" class="btn">Volver al inicio</a>
    </div>
</body>
</html>
