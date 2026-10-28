<?php

namespace Modules\Licenses\Actions;

use App\Models\Document;
use App\Models\License;
use App\Models\LicenseDocument;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;

class AttachLicenseDocument extends Action
{
    public function __construct(private readonly DocumentStore $documents) {}

    public function code(): string
    {
        return 'licenses.attach_document';
    }

    public function permission(): Permission
    {
        return Permission::LicensesManage;
    }

    public function handle(ActionContext $context, array $input): LicenseDocument
    {
        $license = License::findOrFail($input['license_id']);
        $document = Document::findOrFail($input['document_id']);
        $this->documents->attach($document, $license);

        $link = LicenseDocument::create(['document_id' => $document->id, 'license_id' => $license->id, 'vessel_id' => $license->vessel_id]);

        foreach (array_diff($input['vessel_ids'] ?? [], [$license->vessel_id]) as $vesselId) {
            LicenseDocument::create(['document_id' => $document->id, 'license_id' => null, 'vessel_id' => $vesselId]);
        }

        foreach (array_unique([$license->vessel_id, ...($input['vessel_ids'] ?? [])]) as $vesselId) {
            $context->publish(
                vessel: $vesselId,
                module: 'licenses',
                type: 'license.document_attached',
                subject: $license,
                summaryKey: 'licenses::events.document_attached',
                params: ['number' => $license->number, 'name' => $document->name],
            );
        }

        return $link;
    }
}
