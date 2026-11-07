<?php

namespace Modules\Vessels\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Vessels\Actions\AddVesselPhoto;
use Modules\Vessels\Actions\RemoveVesselPhoto;
use Modules\Vessels\Http\Requests\StoreVesselPhotoRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VesselPhotoController extends Controller
{
    public function show(Vessel $vessel, Document $document): StreamedResponse
    {
        abort_unless($this->isPhotoOf($vessel, $document), 404);

        return Storage::disk(DocumentStore::DISK)->response($document->path, $document->name, [
            'Cache-Control' => 'private, max-age=3600',
            'Content-Type' => $document->mime_type,
        ]);
    }

    public function store(StoreVesselPhotoRequest $request, Vessel $vessel, ActionRunner $actions, DocumentStore $documents): RedirectResponse
    {
        foreach ($request->file('photos') as $file) {
            $document = $documents->store($file, $request->user());
            $actions->run(AddVesselPhoto::class, $request->user(), ['vessel_id' => $vessel->id, 'document_id' => $document->id]);
        }

        return to_route('vessels.show', $vessel)->with('status', trans_choice('vessels::vessels.photos.added', count($request->file('photos'))));
    }

    public function destroy(Vessel $vessel, Document $document, ActionRunner $actions): RedirectResponse
    {
        abort_unless($this->isPhotoOf($vessel, $document), 404);

        $actions->run(RemoveVesselPhoto::class, request()->user(), ['vessel_id' => $vessel->id, 'document_id' => $document->id]);

        return to_route('vessels.show', $vessel)->with('status', __('vessels::vessels.photos.removed'));
    }

    private function isPhotoOf(Vessel $vessel, Document $document): bool
    {
        return str_starts_with((string) $document->mime_type, 'image/')
            && DB::table('document_links')
                ->where('document_id', $document->id)
                ->where('object_type', $vessel->getMorphClass())
                ->where('object_id', $vessel->id)
                ->exists();
    }
}
