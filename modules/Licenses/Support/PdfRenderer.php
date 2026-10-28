<?php

namespace Modules\Licenses\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

class PdfRenderer
{
    public const LOCALE = 'fr';

    /** @param array<string, mixed> $data */
    public function render(string $view, array $data): string
    {
        $previous = App::getLocale();
        App::setLocale(self::LOCALE);

        try {
            return Pdf::loadView($view, $data)->setPaper('a4', 'landscape')->output();
        } finally {
            App::setLocale($previous);
        }
    }
}
