<?php

namespace App\Controllers;

class Documentacion extends BaseController
{
    private const ALLOWED = [
        'inicio' => null,
        'roadmap-activo' => 'Documentacion/roadmaps/actuales/roadmap-personalizacion-componentes-y-colores.md',
        'roadmap-historico-general' => 'Documentacion/roadmaps/historicos/Roadmap.md',
        'roadmap-reportes' => 'Documentacion/roadmaps/historicos/Roadmap_Reportes_Analytics.md',
        'roadmap-visuales' => 'Documentacion/roadmaps/historicos/roadmap-visuales.md',
        'roadmap-ux' => 'Documentacion/roadmaps/historicos/roadmap-ux4-remediacion.md',
        'roadmap-gastos' => 'Documentacion/roadmaps/historicos/roadmap-gasto-division-ux.md',
        'roadmap-grupos' => 'Documentacion/roadmaps/historicos/roadmap-grupo-actividad.md',
        'roadmap-cierres' => 'Documentacion/roadmaps/historicos/roadmap-cierres.md',
        'roadmap-ponytail' => 'Documentacion/roadmaps/historicos/roadmap-ponytail-cleanup.md',
        'skill-comandos' => 'Documentacion/skill/SplitWiseReviewerCommands.md',
        'roadmap-html' => 'Documentacion/roadmaps/pagina/Roadmap.html',
    ];

    public function index(?string $slug = null)
    {
        if ($slug !== null) {
            $slug = strtolower($slug);
        }

        if ($slug === null || !array_key_exists($slug, self::ALLOWED)) {
            return $this->render('inicio', null, false);
        }

        $path = self::ALLOWED[$slug];

        if ($path === null) {
            return $this->render('inicio', null, false);
        }

        $fullPath = ROOTPATH . $path;

        if (!file_exists($fullPath)) {
            return $this->render('inicio', null, false);
        }

        $content = file_get_contents($fullPath);

        if (str_ends_with($path, '.html')) {
            return $this->renderHtml($slug);
        }

        $isCommands = ($slug === 'skill-comandos');
        $html = $isCommands ? $this->extractCommands($content) : $this->markdownToHtml($content);

        return $this->render($slug, $html, $isCommands);
    }

    private function renderHtml(string $slug): string
    {
        $fullPath = ROOTPATH . self::ALLOWED[$slug];
        $content = file_get_contents($fullPath);

        if (preg_match('/<body[^>]*>.*?<\/body>/is', $content, $m)) {
            $body = $m[0];
        } else {
            $body = $content;
        }

        $docs = [];
        foreach (self::ALLOWED as $key => $p) {
            $docs[$key] = $this->docTitle($key);
        }

        return view('documentacion/html', [
            'currentSlug' => $slug,
            'htmlContent' => $body,
            'docs' => $docs,
        ]);
    }

    private function render(string $slug, ?string $contentHtml, bool $isCommands): string
    {
        $docs = [];
        foreach (self::ALLOWED as $key => $p) {
            $docs[$key] = $this->docTitle($key);
        }

        return view('documentacion/index', [
            'currentSlug' => $slug,
            'contentHtml' => $contentHtml,
            'docs' => $docs,
            'isCommands' => $isCommands,
        ]);
    }

    private function extractCommands(string $md): string
    {
        $html = '';
        $lines = explode("\n", $md);
        $inCode = false;
        $codeContent = '';
        $sectionTitle = '';
        $sectionDescription = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^```/', $trimmed)) {
                if ($inCode) {
                    $codeContent = trim($codeContent);
                    if ($codeContent !== '') {
                        $html .= '<div class="cmd-card">';
                        if ($sectionTitle !== '') {
                            $html .= '<div class="cmd-card-title">' . htmlspecialchars($sectionTitle) . '</div>';
                        }
                        $html .= '<div class="cmd-card-code"><code>' . htmlspecialchars($codeContent) . '</code></div>';
                        $html .= '<button class="cmd-card-btn" onclick="copiarComando(this)">Copiar</button>';
                        $html .= '</div>';
                    }
                    $codeContent = '';
                    $sectionTitle = '';
                    $sectionDescription = '';
                    $inCode = false;
                } else {
                    $inCode = true;
                    $codeContent = '';
                }
                continue;
            }

            if ($inCode) {
                $codeContent .= $line . "\n";
                continue;
            }

            if (preg_match('/^### (.+)$/', $trimmed, $m)) {
                $sectionTitle = $m[1];
                continue;
            }

            if (preg_match('/^## (.+)$/', $trimmed, $m)) {
                $sectionTitle = $m[1];
                continue;
            }
        }

        if ($inCode && trim($codeContent) !== '') {
            $codeContent = trim($codeContent);
            $html .= '<div class="cmd-card">';
            if ($sectionTitle !== '') {
                $html .= '<div class="cmd-card-title">' . htmlspecialchars($sectionTitle) . '</div>';
            }
            $html .= '<div class="cmd-card-code"><code>' . htmlspecialchars($codeContent) . '</code></div>';
            $html .= '<button class="cmd-card-btn" onclick="copiarComando(this)">Copiar</button>';
            $html .= '</div>';
        }

        if ($html === '') {
            return $this->markdownToHtml($md);
        }

        return $html;
    }

    private function markdownToHtml(string $md): string
    {
        $html = $md;
        $html = htmlspecialchars($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/((?:<li>.*?<\/li>\n?)+)/s', '<ul>$1</ul>', $html);
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);

        $lines = explode("\n", $html);
        $result = [];
        $inCode = false;
        $codeBuffer = [];

        foreach ($lines as $line) {
            if (preg_match('/^<h[1-3]|<li|<ul/', $line)) {
                if ($inCode) {
                    $result[] = '<pre><code>' . implode("\n", $codeBuffer) . '</code></pre>';
                    $codeBuffer = [];
                    $inCode = false;
                }
                $result[] = $line;
                continue;
            }

            $trimmed = trim($line);

            if ($trimmed === '') {
                if ($inCode) {
                    $codeBuffer[] = '';
                } else {
                    $result[] = '';
                }
                continue;
            }

            if ($inCode) {
                $codeBuffer[] = $line;
                continue;
            }

            $result[] = '<p>' . $line . '</p>';
        }

        if ($inCode && !empty($codeBuffer)) {
            $result[] = '<pre><code>' . implode("\n", $codeBuffer) . '</code></pre>';
        }

        $html = implode("\n", $result);
        $html = preg_replace('/<li><\/li>\n?/', '', $html);
        return $html;
    }

    private function docTitle(string $slug): string
    {
        $titles = [
            'inicio' => 'Inicio',
            'roadmap-activo' => 'Roadmap: Componentes y colores',
            'roadmap-historico-general' => 'Roadmap general (hist&oacute;rico)',
            'roadmap-reportes' => 'Roadmap reportes',
            'roadmap-visuales' => 'Roadmap visual',
            'roadmap-ux' => 'Roadmap UX',
            'roadmap-gastos' => 'Roadmap gastos',
            'roadmap-grupos' => 'Roadmap grupos',
            'roadmap-cierres' => 'Roadmap cierres',
            'roadmap-ponytail' => 'Roadmap deuda t&eacute;cnica',
            'skill-comandos' => 'Comandos Skill SplitWise',
            'roadmap-html' => 'Roadmap completo (HTML)',
        ];
        return $titles[$slug] ?? $slug;
    }
}
