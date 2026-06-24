<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitWise — Documentaci&oacute;n</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/app.css') ?>" rel="stylesheet">
    <style>
        :root {
            --doc-sidebar: 260px;
        }
        .doc-layout { display: flex; min-height: 100vh; }
        .doc-sidebar {
            width: var(--doc-sidebar);
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            padding: 1.25rem 0;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
        }
        .doc-sidebar-brand {
            font-weight: 700;
            font-size: 1.1rem;
            padding: 0 1.25rem 1rem;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }
        .doc-sidebar-brand a { color: var(--bs-primary); text-decoration: none; }
        .doc-sidebar-section {
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6c757d;
            padding: .5rem 1.25rem 0;
        }
        .doc-sidebar-link {
            display: block;
            padding: .35rem 1.25rem .35rem 1.5rem;
            color: #212529;
            text-decoration: none;
            font-size: .9rem;
        }
        .doc-sidebar-link:hover { background: #e9ecef; }
        .doc-sidebar-link.active { background: var(--bs-primary); color: #fff; border-radius: 0 .25rem .25rem 0; }
        .doc-main {
            margin-left: var(--doc-sidebar);
            flex: 1;
            padding: 2rem;
            max-width: 900px;
        }
        .doc-main h1 { font-size: 1.75rem; font-weight: 700; margin-top: 0; }
        .doc-main h2 { font-size: 1.35rem; font-weight: 600; margin-top: 2rem; border-bottom: 1px solid #eee; padding-bottom: .35rem; }
        .doc-main h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1.5rem; }
        .doc-main p { line-height: 1.7; margin-bottom: 1rem; }
        .doc-main ul { margin-bottom: 1rem; padding-left: 1.25rem; }
        .doc-main li { margin-bottom: .35rem; }
        .doc-main code {
            background: #f0f0f0;
            padding: .1rem .35rem;
            border-radius: 3px;
            font-size: .85em;
        }
        .doc-main pre {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 1rem;
            border-radius: 6px;
            overflow-x: auto;
            font-size: .85rem;
            margin-bottom: 1rem;
        }
        .doc-main pre code {
            background: transparent;
            padding: 0;
            color: inherit;
        }
        .doc-main strong { font-weight: 700; }
        .doc-back { margin-bottom: 1rem; }
        .doc-html-content { max-width: 100%; }
        .doc-html-content table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        .doc-html-content th, .doc-html-content td { border: 1px solid #dee2e6; padding: .5rem; }
        .doc-html-content th { background: #f8f9fa; }
        @media (max-width: 768px) {
            .doc-sidebar { display: none; }
            .doc-main { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>
<div class="doc-layout">
    <aside class="doc-sidebar">
        <div class="doc-sidebar-brand"><a href="<?= base_url('documentacion') ?>">SplitWise</a></div>
        <div class="doc-sidebar-section">Documentos</div>
        <?php foreach ($docs as $slug => $title): ?>
            <a class="doc-sidebar-link <?= $slug === $currentSlug ? 'active' : '' ?>"
               href="<?= base_url('documentacion/' . rawurlencode($slug)) ?>"><?= $title ?></a>
        <?php endforeach; ?>
    </aside>
    <main class="doc-main">
        <?php if ($currentSlug === 'inicio' || $contentHtml === null): ?>
            <h1>Documentaci&oacute;n del proyecto</h1>
            <p>Esta es la documentaci&oacute;n interna del proyecto SplitWise.</p>
            <p>Us&aacute; el men&uacute; lateral para navegar entre los documentos disponibles.</p>
            <h2>Documentos disponibles</h2>
            <ul>
                <?php foreach ($docs as $slug => $title): ?>
                    <?php if ($slug === 'inicio') continue; ?>
                    <li><a href="<?= base_url('documentacion/' . rawurlencode($slug)) ?>"><?= $title ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <?php if ($currentSlug === 'roadmap-html'): ?>
                <div class="doc-html-content"><?= $contentHtml ?></div>
            <?php else: ?>
                <?= $contentHtml ?>
            <?php endif; ?>
        <?php endif; ?>
        <hr class="mt-4">
        <p class="text-muted small">SplitWise &mdash; Documentaci&oacute;n interna del proyecto</p>
    </main>
</div>
</body>
</html>
