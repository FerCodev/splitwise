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
        .doc-html-content th,         .doc-html-content td { border: 1px solid #dee2e6; padding: .5rem; }
        .doc-html-content th { background: #f8f9fa; }
        .cmd-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: .75rem 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }
        .cmd-box-label {
            font-size: .75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .04em;
            min-width: 100px;
            flex-shrink: 0;
        }
        .cmd-box-code {
            flex: 1;
            min-width: 0;
            overflow-x: auto;
            white-space: nowrap;
        }
        .cmd-box-code code {
            background: #e9ecef;
            padding: .35rem .6rem;
            border-radius: 4px;
            font-size: .85rem;
            color: #212529;
        }
        .cmd-box-btn {
            flex-shrink: 0;
            white-space: nowrap;
        }
        .cmd-box-btn.copied {
            background: #198754;
            color: #fff;
            border-color: #198754;
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
            <?php if ($isCommands): ?>
                <?php if ($contentHtml !== null && $contentHtml !== ''): ?>
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-outline-primary btn-sm" onclick="copiarTodos()">Copiar todos</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
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
<script>
function copiarComando(btn) {
    var code = btn.parentElement.querySelector('.cmd-box-code code');
    if (!code) return;
    var texto = code.textContent || code.innerText;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(function() {
            feedbackCopiado(btn);
        }).catch(function() {
            copiarFallback(texto, btn);
        });
    } else {
        copiarFallback(texto, btn);
    }
}
function copiarTodos() {
    var btns = document.querySelectorAll('.cmd-box-btn');
    var textos = [];
    btns.forEach(function(b) {
        var code = b.parentElement.querySelector('.cmd-box-code code');
        if (code) textos.push(code.textContent || code.innerText);
    });
    var texto = textos.join('\n');
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(texto).then(function() {
            var primerBtn = btns[0];
            if (primerBtn) feedbackCopiado(primerBtn, 'Todos copiados');
        }).catch(function() {
            copiarFallback(texto, btns[0]);
        });
    } else {
        copiarFallback(texto, btns[0]);
    }
}
function copiarFallback(texto, btn) {
    var ta = document.createElement('textarea');
    ta.value = texto;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try { document.execCommand('copy'); feedbackCopiado(btn); } catch(e) {}
    document.body.removeChild(ta);
}
function feedbackCopiado(btn, msg) {
    if (!btn) return;
    var original = btn.textContent;
    btn.textContent = msg || 'Copiado';
    btn.className = btn.className.replace(/ ?copied/, '') + ' copied';
    setTimeout(function() {
        btn.textContent = original;
        btn.className = btn.className.replace(/ ?copied/, '');
    }, 1500);
}
</script>
</body>
</html>
