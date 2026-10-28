<?php

namespace Modules\Reports\Support;

use App\Enums\ExportFormat;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportRenderer
{
    private const CSV_DELIMITER = ';';

    private const UTF8_BYTE_ORDER_MARK = "\xEF\xBB\xBF";

    public function render(ExportDataset $dataset, ExportFormat $format): RenderedFile
    {
        return match ($format) {
            ExportFormat::Csv => new RenderedFile($this->csv($dataset), 'text/csv; charset=UTF-8', 'csv'),
            ExportFormat::Pdf => new RenderedFile($this->pdf($dataset), 'application/pdf', 'pdf'),
        };
    }

    private function csv(ExportDataset $dataset): string
    {
        $stream = fopen('php://temp', 'r+');

        fputcsv($stream, $dataset->headings, self::CSV_DELIMITER);

        foreach ($dataset->rows as $row) {
            fputcsv($stream, $row, self::CSV_DELIMITER);
        }

        rewind($stream);

        return self::UTF8_BYTE_ORDER_MARK.stream_get_contents($stream);
    }

    private function pdf(ExportDataset $dataset): string
    {
        return Pdf::loadView('reports::pdf.table', ['dataset' => $dataset])
            ->setPaper('a4', 'landscape')
            ->output();
    }
}
