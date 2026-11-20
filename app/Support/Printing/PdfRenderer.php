<?php

namespace App\Support\Printing;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

final class PdfRenderer
{
    public const LOCALE = 'fr';

    public function render(PrintedDocument $document): string
    {
        return Pdf::loadView('printing.document', ['document' => $document])
            ->setOption('isFontSubsettingEnabled', true)
            ->setPaper('a4', 'portrait')
            ->output();
    }

    /**
     * @template T
     *
     * @param  callable(): T  $build
     * @return T
     */
    public static function inPrintLocale(callable $build): mixed
    {
        $previous = App::getLocale();
        App::setLocale(self::LOCALE);

        try {
            return $build();
        } finally {
            App::setLocale($previous);
        }
    }
}
