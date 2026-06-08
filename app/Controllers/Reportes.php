<?php

namespace App\Controllers;

use App\Models\Grupo;
use App\Models\Categoria;
use App\Services\Reportes as ReportesService;

class Reportes extends BaseController
{
    public function index()
    {
        $userId = session()->get('userId');

        $filters = $this->request->getGet(array_filter([
            'grupo_id',
            'categoria_id',
            'fecha_desde',
            'fecha_hasta',
        ]));

        $resumen = ReportesService::resumenGlobal($userId);
        $porCategoria = ReportesService::gastosPorCategoria($userId, $filters);
        $porGrupo = ReportesService::gastosPorGrupo($userId, $filters);
        $movimientos = ReportesService::ultimosMovimientos($userId, 10);

        $grupoModel = new Grupo();
        $grupos = $grupoModel->getGruposByUser($userId);
        $categorias = model(Categoria::class)->getActivas();

        return view('reportes/index', [
            'resumen' => $resumen,
            'porCategoria' => $porCategoria,
            'porGrupo' => $porGrupo,
            'movimientos' => $movimientos,
            'grupos' => $grupos,
            'categorias' => $categorias,
            'filters' => $filters,
        ]);
    }

    public function exportar()
    {
        $userId = session()->get('userId');

        $filters = $this->request->getGet(array_filter([
            'grupo_id',
            'categoria_id',
            'fecha_desde',
            'fecha_hasta',
        ]));

        $rows = ReportesService::gastosParaExportar($userId, $filters);

        $filename = 'gastos_' . date('Y-m-d') . '.csv';

        $this->response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->response->setHeader('Content-Disposition', "attachment; filename=\"{$filename}\"");

        $output = fopen('php://output', 'wb');
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, ['Fecha', 'Grupo', 'Categoria', 'Descripcion', 'Pagador', 'Monto']);

        foreach (ReportesService::formatearFilasCsv($rows) as $linea) {
            fputcsv($output, $linea);
        }

        fclose($output);
        exit;
    }
}
