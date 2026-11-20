<?php

namespace App\Support\Logbook;

use App\Models\LogbookDay;
use App\Support\Printing\PdfRenderer;
use App\Support\Printing\WordRenderer;

final class LogbookDayWord
{
    public const MIME_TYPE = WordRenderer::MIME_TYPE;

    public function __construct(private readonly LogbookDayDocument $document, private readonly WordRenderer $word) {}

    public function filename(LogbookDay $day): string
    {
        return LogbookDayDocument::basename($day).'.docx';
    }

    public function render(LogbookDay $day): string
    {
        return PdfRenderer::inPrintLocale(fn () => $this->word->render($this->document->build($day)));
    }
}
