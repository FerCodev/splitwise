<?php

namespace App\Controllers;

class Documentacion extends BaseController
{
    private const ALLOWED = [
        'inicio' => null,
        'proyecto-actual' => 'Documentacion/proyecto/proyecto-actual.md',
        'funcionalidades' => 'Documentacion/proyecto/funcionalidades.md',
        'flujos' => 'Documentacion/proyecto/flujos-principales.md',
        'arquitectura' => 'Documentacion/proyecto/arquitectura-tecnica.md',
        'rutas' => 'Documentacion/proyecto/rutas.md',
        'base-de-datos' => 'Documentacion/proyecto/base-de-datos.md',
        'ui-catalogo-feedback' => 'Documentacion/proyecto/ui-catalogo-feedback.md',
        'operacion-deploy' => 'Documentacion/proyecto/operacion-deploy.md',
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
    ];

    private const SECTIONS = [
        'Proyecto actual' => [
            'proyecto-actual', 'funcionalidades', 'flujos', 'arquitectura',
            'rutas', 'base-de-datos', 'ui-catalogo-feedback', 'operacion-deploy',
        ],
        'Roadmaps e hist&oacute;rico' => [
            'roadmap-activo', 'roadmap-historico-general', 'roadmap-reportes',
            'roadmap-visuales', 'roadmap-ux', 'roadmap-gastos', 'roadmap-grupos',
            'roadmap-cierres', 'roadmap-ponytail',
        ],
        'Herramientas' => [
            'skill-comandos',
        ],
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

        $isCommands = ($slug === 'skill-comandos');
        $html = $isCommands ? $this->extractCommands($content) : $this->markdownToHtml($content);

        return $this->render($slug, $html, $isCommands);
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
            'sections' => self::SECTIONS,
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
        $cmdIndex = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (preg_match('/^```/', $trimmed)) {
                if ($inCode) {
                    $codeContent = trim($codeContent);
                    if ($codeContent !== '') {
                        $cmdId = 'cmd-src-' . $cmdIndex++;
                        $html .= '<div class="cmd-card">';
                        if ($sectionTitle !== '') {
                            $html .= '<div class="cmd-card-title">' . htmlspecialchars($sectionTitle) . '</div>';
                        }
                        $html .= '<div class="cmd-card-code"><code>' . nl2br(htmlspecialchars($codeContent, ENT_QUOTES | ENT_HTML5, 'UTF-8')) . '</code></div>';
                        $html .= '<textarea class="cmd-source" id="' . $cmdId . '">' . htmlspecialchars($codeContent, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</textarea>';
                        $html .= '<button class="cmd-card-btn" onclick="copiarComando(\'' . $cmdId . '\', this)">Copiar</button>';
                        $html .= '</div>';
                    }
                    $codeContent = '';
                    $sectionTitle = '';
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
            $cmdId = 'cmd-src-' . $cmdIndex++;
            $html .= '<div class="cmd-card">';
            if ($sectionTitle !== '') {
                $html .= '<div class="cmd-card-title">' . htmlspecialchars($sectionTitle) . '</div>';
            }
            $html .= '<div class="cmd-card-code"><code>' . nl2br(htmlspecialchars($codeContent, ENT_QUOTES | ENT_HTML5, 'UTF-8')) . '</code></div>';
            $html .= '<textarea class="cmd-source" id="' . $cmdId . '">' . htmlspecialchars($codeContent, ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</textarea>';
            $html .= '<button class="cmd-card-btn" onclick="copiarComando(\'' . $cmdId . '\', this)">Copiar</button>';
            $html .= '</div>';
        }

        if ($html === '') {
            return $this->markdownToHtml($md);
        }

        return $html;
    }

    private function markdownToHtml(string $md): string
    {
        $html = $this->escapeAndRender($md);
        return $html;
    }

    private function escapeAndRender(string $md): string
    {
        $lines = explode("\n", $md);
        $out = [];
        $inCode = false;
        $codeLines = [];
        $i = 0;

        while ($i < count($lines)) {
            $line = $lines[$i];
            $trimmed = trim($line);

            if (preg_match('/^```/', $trimmed)) {
                if ($inCode) {
                    $escaped = htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $out[] = '<pre><code>' . $escaped . '</code></pre>';
                    $codeLines = [];
                    $inCode = false;
                    $i++;
                    continue;
                }
                $inCode = true;
                $codeLines = [];
                $i++;
                continue;
            }

            if ($inCode) {
                $codeLines[] = $line;
                $i++;
                continue;
            }

            if ($trimmed === '') {
                $out[] = '';
                $i++;
                continue;
            }

            if (preg_match('/^#{1,3}\s/', $trimmed)) {
                $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $escaped = preg_replace('/^### (.+)$/', '<h3>$1</h3>', $escaped);
                $escaped = preg_replace('/^## (.+)$/', '<h2>$1</h2>', $escaped);
                $escaped = preg_replace('/^# (.+)$/', '<h1>$1</h1>', $escaped);
                $out[] = $escaped;
                $i++;
                continue;
            }

            if ($trimmed === '---') {
                $out[] = '<hr>';
                $i++;
                continue;
            }

            if (str_starts_with($trimmed, '|')) {
                $tableRows = [];
                while ($i < count($lines) && str_starts_with(trim($lines[$i]), '|')) {
                    $tableRows[] = trim($lines[$i]);
                    $i++;
                }
                $out[] = $this->renderTable($tableRows);
                continue;
            }

            $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $escaped = preg_replace('/^- (.+)$/', '<li>$1</li>', $escaped);
            $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $escaped);
            $escaped = preg_replace('/`([^`]+)`/', '<code>$1</code>', $escaped);

            $out[] = '<p>' . $escaped . '</p>';
            $i++;
        }

        if ($inCode && !empty($codeLines)) {
            $escaped = htmlspecialchars(implode("\n", $codeLines), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $out[] = '<pre><code>' . $escaped . '</code></pre>';
        }

        $html = implode("\n", $out);
        $html = preg_replace('/<li><\/li>\n?/', '', $html);
        $html = preg_replace('/((?:<li>.*?<\/li>\n?)+)/s', '<ul>$1</ul>', $html);
        return $html;
    }

    private function renderTable(array $rows): string
    {
        if (count($rows) < 2) {
            return '<p>' . htmlspecialchars(implode("\n", $rows), ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</p>';
        }

        $html = '<div class="doc-table-wrap"><table>';

        $headerCells = $this->parseTableRow($rows[0]);
        $isSeparator = preg_match('/^[\s|:-]+$/', trim(str_replace('|', '', $rows[1])));

        if ($isSeparator) {
            $html .= '<thead><tr>';
            foreach ($headerCells as $cell) {
                $html .= '<th>' . htmlspecialchars(trim($cell), ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            for ($i = 2; $i < count($rows); $i++) {
                $cells = $this->parseTableRow($rows[$i]);
                $html .= '<tr>';
                foreach ($cells as $cell) {
                    $v = htmlspecialchars(trim($cell), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $v = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $v);
                    $v = preg_replace('/`([^`]+)`/', '<code>$1</code>', $v);
                    $html .= '<td>' . $v . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        } else {
            $html .= '<tbody>';
            foreach ($rows as $row) {
                $cells = $this->parseTableRow($row);
                $html .= '<tr>';
                foreach ($cells as $cell) {
                    $v = htmlspecialchars(trim($cell), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $html .= '<td>' . $v . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
        }

        $html .= '</table></div>';
        return $html;
    }

    private function parseTableRow(string $line): array
    {
        $line = trim($line);
        $line = trim($line, '|');
        return explode('|', $line);
    }

    private function docTitle(string $slug): string
    {
        $titles = [
            'inicio' => 'Inicio',
            'proyecto-actual' => 'Proyecto actual',
            'funcionalidades' => 'Funcionalidades',
            'flujos' => 'Flujos principales',
            'arquitectura' => 'Arquitectura t&eacute;cnica',
            'rutas' => 'Rutas',
            'base-de-datos' => 'Base de datos',
            'ui-catalogo-feedback' => 'UI, cat&aacute;logo y feedback',
            'operacion-deploy' => 'Operaci&oacute;n y deploy',
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
        ];
        return $titles[$slug] ?? $slug;
    }
}
