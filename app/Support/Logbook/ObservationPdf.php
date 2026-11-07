<?php

namespace App\Support\Logbook;

use App\Models\Observation;
use App\Support\Documents\GeneratedDocument;
use Barryvdh\DomPDF\Facade\Pdf;

final class ObservationPdf
{
    public function document(Observation $observation): GeneratedDocument
    {
        $observation->loadMissing(['vessel', 'zone', 'author', 'team']);

        return new GeneratedDocument(
            'observation-'.$observation->id.'.pdf',
            Pdf::loadView('logbook::pdf.observation', ['observation' => $observation])->output(),
            'application/pdf',
        );
    }
}
