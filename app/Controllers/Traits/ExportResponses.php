<?php

namespace App\Controllers\Traits;

trait ExportResponses
{
    private function sumMonto(array $rows): float
    {
        return array_sum(array_map(static fn(array $row): float => (float) ($row['monto'] ?? 0), $rows));
    }

    private function streamPdf(string $view, array $data, string $filenamePrefix): void
    {
        $html = view($view, $data);

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filenamePrefix . '_' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
        exit;
    }

    private function streamExcel(string $view, array $data, string $filenamePrefix): void
    {
        $filename = $filenamePrefix . '_' . date('Y-m-d') . '.xls';

        $this->response->setHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->response->setHeader('Cache-Control', 'max-age=0');

        echo "\xEF\xBB\xBF";
        echo view($view, $data);
        exit;
    }
}
