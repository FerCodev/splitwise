<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'SplitWise') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --touch-target: 44px; }
        .btn { min-height: var(--touch-target); display: inline-flex; align-items: center; justify-content: center; }
        .form-control, .form-select { min-height: var(--touch-target); font-size: 16px; }
        .mobile-card-item { padding: 12px 16px; border-bottom: 1px solid #eee; }
        .mobile-card-item:last-child { border-bottom: 0; }
        @media (max-width: 575.98px) {
            .card { border-radius: 12px; }
            .container { padding-left: 12px; padding-right: 12px; }
            h2 { font-size: 1.5rem; }
            h3 { font-size: 1.25rem; }
            h4 { font-size: 1.1rem; }
            h5 { font-size: 1rem; }
            .navbar-brand { font-size: 1.1rem; }
            .table-mobile-hidden { display: none !important; }
        }
        @media (min-width: 576px) {
            .mobile-only { display: none !important; }
        }
    </style>
</head>
<body>
