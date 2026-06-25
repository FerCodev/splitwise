<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitWise &mdash; Roadmap completo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/app.css') ?>" rel="stylesheet">
    <style>
        :root { --doc-sidebar: 260px; }
        .doc-layout { display: flex; min-height: 100vh; }
        .doc-sidebar {
            width: var(--doc-sidebar);
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            padding: 1.25rem 0;
            position: fixed; top: 0; left: 0; bottom: 0; overflow-y: auto;
        }
        .doc-sidebar-brand {
            font-weight: 700; font-size: 1.1rem;
            padding: 0 1.25rem 1rem;
            border-bottom: 1px solid #dee2e6; margin-bottom: 1rem;
        }
        .doc-sidebar-brand a { color: var(--bs-primary); text-decoration: none; }
        .doc-sidebar-section {
            font-size: .75rem; text-transform: uppercase; letter-spacing: .05em;
            color: #6c757d; padding: .5rem 1.25rem 0;
        }
        .doc-sidebar-link {
            display: block; padding: .35rem 1.25rem .35rem 1.5rem;
            color: #212529; text-decoration: none; font-size: .9rem;
        }
        .doc-sidebar-link:hover { background: #e9ecef; }
        .doc-sidebar-link.active { background: var(--bs-primary); color: #fff; border-radius: 0 .25rem .25rem 0; }
        .doc-main {
            margin-left: var(--doc-sidebar); flex: 1; padding: 2rem; max-width: 960px;
        }
        @media (max-width: 768px) {
            .doc-sidebar { display: none; }
            .doc-main { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="doc-layout">
    <aside class="doc-sidebar">
        <div class="doc-sidebar-brand"><a href="<?= base_url('doc/inicio') ?>">SplitWise</a></div>
        <div class="doc-sidebar-section">Documentos</div>
        <?php foreach ($docs as $slug => $title): ?>
            <a class="doc-sidebar-link <?= $slug === $currentSlug ? 'active' : '' ?>"
               href="<?= base_url('doc/' . rawurlencode($slug)) ?>"><?= $title ?></a>
        <?php endforeach; ?>
    </aside>
    <main class="doc-main">
        <?= $htmlContent ?>
        <hr class="mt-4">
        <p class="text-muted small">SplitWise &mdash; Documentaci&oacute;n interna del proyecto</p>
    </main>
</div>
</body>
</html>
