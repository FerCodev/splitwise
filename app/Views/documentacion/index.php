<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SplitWise &mdash; Documentaci&oacute;n</title>
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
        .doc-main h1 { font-size: 1.75rem; font-weight: 700; margin-top: 0; }
        .doc-main h2 { font-size: 1.35rem; font-weight: 600; margin-top: 2rem; border-bottom: 1px solid #eee; padding-bottom: .35rem; }
        .doc-main h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1.5rem; }
        .doc-main p { line-height: 1.7; margin-bottom: 1rem; }
        .doc-main ul { margin-bottom: 1rem; padding-left: 1.25rem; }
        .doc-main li { margin-bottom: .35rem; }
        .doc-main code { background: #f0f0f0; padding: .1rem .35rem; border-radius: 3px; font-size: .85em; }
        .doc-main pre { background: #1e1e1e; color: #d4d4d4; padding: 1rem; border-radius: 6px; overflow-x: auto; font-size: .85rem; margin-bottom: 1rem; line-height: 1.5; }
        .doc-main pre code { background: transparent; padding: 0; color: #d4d4d4; }
        .doc-main strong { font-weight: 700; }
        .doc-main hr { margin: 2rem 0; border: 0; border-top: 1px solid #dee2e6; }
        .doc-table-wrap { overflow-x: auto; margin-bottom: 1rem; }
        .doc-main table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .doc-main th, .doc-main td { border: 1px solid #dee2e6; padding: .5rem .75rem; text-align: left; vertical-align: top; }
        .doc-main th { background: #f8f9fa; font-weight: 600; }
        .doc-main td code { font-size: .8rem; white-space: nowrap; }
        .doc-main code { background: #e8e8e8; padding: .15rem .4rem; border-radius: 3px; font-size: .82em; color: #212529; }

        .cmd-list { display: flex; flex-direction: column; gap: 1rem; }
        .cmd-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem 1.25rem;
        }
        .cmd-card-title {
            font-weight: 600;
            font-size: .95rem;
            margin-bottom: .75rem;
            color: #212529;
        }
        .cmd-card-code {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: .75rem 1rem;
            margin-bottom: .75rem;
            overflow-x: auto;
            word-break: break-all;
        }
        .cmd-card-code code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: .85rem;
            color: #212529;
            white-space: pre-wrap;
        }
        .cmd-card-actions { display: flex; gap: .5rem; }
        .cmd-card-btn {
            background: var(--bs-primary);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: .4rem 1rem;
            font-size: .85rem;
            cursor: pointer;
        }
        .cmd-card-btn:hover { opacity: .9; }
        .cmd-card-btn.copied { background: #198754; }

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
        <?php foreach ($sections as $sectionName => $slugs): ?>
            <div class="doc-sidebar-section"><?= $sectionName ?></div>
            <?php foreach ($slugs as $slug): ?>
                <a class="doc-sidebar-link <?= $slug === $currentSlug ? 'active' : '' ?>"
                   href="<?= base_url('doc/' . rawurlencode($slug)) ?>"><?= $docs[$slug] ?? $slug ?></a>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </aside>
    <main class="doc-main">
        <?php if ($currentSlug === 'inicio' || $contentHtml === null): ?>
            <h1>Documentaci&oacute;n del proyecto</h1>
            <p>Esta es la documentaci&oacute;n interna del proyecto SplitWise.</p>
            <p>Us&aacute; el men&uacute; lateral para navegar entre los documentos disponibles.</p>
            <h2>Documentos disponibles</h2>
            <?php foreach ($sections as $sectionName => $slugs): ?>
                <h3><?= $sectionName ?></h3>
                <ul>
                    <?php foreach ($slugs as $slug): ?>
                        <li><a href="<?= base_url('doc/' . rawurlencode($slug)) ?>"><?= $docs[$slug] ?? $slug ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        <?php elseif ($isCommands): ?>
            <?php if ($contentHtml !== null && $contentHtml !== ''): ?>
                <div class="d-flex justify-content-end mb-3">
                    <button class="btn btn-outline-primary btn-sm" onclick="copiarTodos()">Copiar todos</button>
                </div>
                <div class="cmd-list"><?= $contentHtml ?></div>
            <?php endif; ?>
        <?php else: ?>
            <?= $contentHtml ?>
        <?php endif; ?>
        <hr class="mt-4">
        <p class="text-muted small">SplitWise &mdash; Documentaci&oacute;n interna del proyecto</p>
    </main>
</div>
<script>
function copiarComando(btn) {
    var code = btn.parentElement.parentElement.querySelector('.cmd-card-code code');
    if (!code) code = btn.parentElement.querySelector('.cmd-card-code code');
    if (!code) return;
    var texto = code.textContent || code.innerText;
    ejecutarCopia(texto, btn);
}
function copiarTodos() {
    var items = document.querySelectorAll('.cmd-card');
    var textos = [];
    items.forEach(function(card) {
        var code = card.querySelector('.cmd-card-code code');
        if (code) textos.push(code.textContent || code.innerText);
    });
    var texto = textos.join('\n');
    var primerBtn = items.length > 0 ? items[0].querySelector('.cmd-card-btn') : null;
    ejecutarCopia(texto, primerBtn, 'Todos copiados');
}
function ejecutarCopia(texto, btn, msg) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(function() {
            if (btn) feedbackCopiado(btn, msg);
        }).catch(function() {
            copiarFallback(texto, btn, msg);
        });
    } else {
        copiarFallback(texto, btn, msg);
    }
}
function copiarFallback(texto, btn, msg) {
    var ta = document.createElement('textarea');
    ta.value = texto;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); if (btn) feedbackCopiado(btn, msg); } catch(e) {}
    document.body.removeChild(ta);
}
function feedbackCopiado(btn, msg) {
    if (!btn) return;
    var original = btn.textContent;
    btn.textContent = msg || 'Copiado';
    btn.className = 'cmd-card-btn copied';
    setTimeout(function() {
        btn.textContent = original;
        btn.className = 'cmd-card-btn';
    }, 1500);
}
</script>
</body>
</html>
