<?php

namespace Modules\Licenses\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\LicenseDocument;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Licenses\Actions\AttachLicenseDocument;
use Modules\Licenses\Http\Requests\AttachDocumentRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LicenseDocumentController extends Controller
{
    public function store(License $license, AttachDocumentRequest $request, ActionRunner $actions, DocumentStore $documents): RedirectResponse
    {
        $actions->run(AttachLicenseDocument::class, $request->user(), [
            'license_id' => $license->id,
            'document_id' => $documents->store($request->file('file'), $request->user())->id,
            'vessel_ids' => array_map('intval', $request->validated('vessel_ids') ?? []),
        ]);

        return to_route('licenses.show', $license)->with('status', __('licenses::licenses.documents.attached'));
    }

    public function download(LicenseDocument $licenseDocument): StreamedResponse
    {
        $document = $licenseDocument->document;

        return Storage::disk(DocumentStore::DISK)->download($document->path, $document->name);
    }
}
