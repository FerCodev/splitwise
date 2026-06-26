<?php

namespace App\Controllers;

use App\Models\Gasto;
use App\Models\Grupo;
use App\Models\Categoria;
use App\Services\Reportes as ReportesService;

class Reportes extends BaseController
{
    public function index()
    {
        $userId = session()->get('userId');
        $filters = $this->request->getGet(array_keys($this->filtrosEsperados()));
        $filters = array_intersect_key($filters, $this->filtrosEsperados());

        $resumen = ReportesService::resumenGlobal($userId);
        $mesBase = ReportesService::resumenMensual($userId, $filters['year_month'] ?? '');
        if (empty($filters['year_month'])) {
            $filters['year_month'] = $mesBase['mes'];
        }
        $resumenMensual = ReportesService::resumenFiltrado($userId, $filters);
        $topGrupos = ReportesService::topGrupos($userId, $filters['year_month']);
        $topCategorias = ReportesService::topCategorias($userId, $filters['year_month']);
        $porCategoria = ReportesService::gastosPorCategoria($userId, $filters);
        $porGrupo = ReportesService::gastosPorGrupo($userId, $filters);
        $movimientos = ReportesService::movimientosFiltrados($userId, $filters, 12);
        $deudas = ReportesService::deudasPendientes($userId, 5, $filters);
        $grupos = model(Grupo::class)->getGruposByUser($userId);
        $categorias = model(Categoria::class)->getActivas();

        return view('reportes/index', [
            'resumen' => $resumen,
            'resumenMensual' => $resumenMensual,
            'topGrupos' => $topGrupos,
            'topCategorias' => $topCategorias,
            'porCategoria' => $porCategoria,
            'porGrupo' => $porGrupo,
            'movimientos' => $movimientos,
            'deudas' => $deudas,
            'grupos' => $grupos,
            'categorias' => $categorias,
            'filters' => $filters,
        ]);
    }

    public function grupo(int $grupoId)
    {
        $userId = session()->get('userId');
        $grupoModel = new Grupo();
        $grupo = $grupoModel->find($grupoId);
        if (!$grupo || !$grupoModel->isMiembro($grupoId, $userId)) {
            return redirect()->to('/reportes')->with('error', 'Grupo no encontrado o sin acceso.');
        }

        $miembros = $grupoModel->getMiembros($grupoId);
        $gastoModel = new Gasto();
        $balance = $gastoModel->getBalanceByGrupo($grupoId);
        $deudas = Gasto::computeDeudasFromBalance($balance);
        $resumen = ReportesService::resumenGrupo($grupoId);
        $porCategoria = ReportesService::gastosPorCategoria($userId, ['grupo_id' => $grupoId]);
        $evolucion = ReportesService::evolucionMensualGrupo($grupoId);
        $movimientos = ReportesService::ultimosMovimientosPorGrupo($grupoId, 10);

        return view('reportes/grupo', [
            'grupo' => $grupo,
            'miembros' => $miembros,
            'balance' => $balance,
            'deudas' => $deudas,
            'resumen' => $resumen,
            'porCategoria' => $porCategoria,
            'evolucion' => $evolucion,
            'movimientos' => $movimientos,
        ]);
    }

    public function exportar()
    {
        $userId = session()->get('userId');
        $filters = $this->request->getGet(array_keys($this->filtrosEsperados()));
        $filters = array_intersect_key($filters, $this->filtrosEsperados());

        $rows = ReportesService::gastosParaExportar($userId, $filters);
        $filename = 'gastos_' . date('Y-m-d') . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['Fecha', 'Grupo', 'Categoria', 'Descripcion', 'Pagador', 'Monto']);
        foreach (ReportesService::formatearFilasCsv($rows) as $linea) {
            fputcsv($output, $linea);
        }
        fclose($output);
        exit;
    }

    public function exportarPdf()
    {
        $userId = session()->get('userId');
        $filters = $this->request->getGet(array_keys($this->filtrosEsperados()));
        $filters = array_intersect_key($filters, $this->filtrosEsperados());
        $mesBase = ReportesService::resumenMensual($userId, $filters['year_month'] ?? '');
        if (empty($filters['year_month'])) {
            $filters['year_month'] = $mesBase['mes'];
        }
        $resumenMensual = ReportesService::resumenFiltrado($userId, $filters);
        $topGrupos = ReportesService::topGrupos($userId, $filters['year_month']);
        $topCategorias = ReportesService::topCategorias($userId, $filters['year_month']);
        $movimientos = ReportesService::movimientosFiltrados($userId, $filters, 10);
        $deudas = ReportesService::deudasPendientes($userId, 5, $filters);

        $html = view('reportes/pdf', [
            'resumenMensual' => $resumenMensual,
            'topGrupos' => $topGrupos,
            'topCategorias' => $topCategorias,
            'movimientos' => $movimientos,
            'deudas' => $deudas,
            'fecha' => date('d/m/Y H:i'),
        ]);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('reporte_' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
        exit;
    }

    private function filtrosEsperados(): array
    {
        return [
            'grupo_id' => '',
            'categoria_id' => '',
            'fecha_desde' => '',
            'fecha_hasta' => '',
            'year_month' => '',
        ];
    }
}
