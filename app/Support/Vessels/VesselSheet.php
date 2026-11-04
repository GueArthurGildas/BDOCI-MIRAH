<?php

namespace App\Support\Vessels;

use App\Models\Document;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Documents\DocumentStore;
use App\Support\Documents\GeneratedDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class VesselSheet
{
    public function filename(Vessel $vessel): string
    {
        return 'fiche-'.Str::slug($vessel->registration_number ?: $vessel->name).'.pdf';
    }

    public function render(Vessel $vessel, ?User $generatedBy = null): string
    {
        $vessel->load(['currentLicense', 'latestPosition.zone', 'flagCountry', 'homePort', 'identifiers', 'actors.legalEntity', 'actors.person', 'trackingDevices', 'currentState']);
        $indicators = VesselIndicators::for($vessel);

        return Pdf::loadView('vessels::sheet', [
            'vessel' => $vessel,
            'assessment' => StatusLight::assess($indicators->facts),
            'photo' => $this->photo($vessel),
            'generatedAt' => now(),
            'generatedBy' => $generatedBy ?? auth()->user(),
        ])->output();
    }

    public function document(Vessel $vessel, ?User $generatedBy = null): GeneratedDocument
    {
        return new GeneratedDocument($this->filename($vessel), $this->render($vessel, $generatedBy), 'application/pdf');
    }

    private function photo(Vessel $vessel): ?string
    {
        $document = $vessel->primary_photo_id ? Document::find($vessel->primary_photo_id) : null;

        if ($document === null || ! Storage::disk(DocumentStore::DISK)->exists($document->path)) {
            return null;
        }

        return 'data:'.$document->mime_type.';base64,'.base64_encode(Storage::disk(DocumentStore::DISK)->get($document->path));
    }
}
