<?php

namespace Modules\Vessels\Actions;

use App\Models\Document;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;

class AddVesselPhoto extends Action
{
    public function __construct(private readonly DocumentStore $documents) {}

    public function code(): string
    {
        return 'vessels.add_photo';
    }

    public function permission(): Permission
    {
        return Permission::VesselsManage;
    }

    public function handle(ActionContext $context, array $input): Document
    {
        $vessel = Vessel::findOrFail($input['vessel_id']);
        $document = Document::findOrFail($input['document_id']);

        $this->documents->attach($document, $vessel);

        if ($vessel->primary_photo_id === null) {
            Vessel::whereKey($vessel->id)->update(['primary_photo_id' => $document->id]);
        }

        $context->publish($vessel, 'vessels', 'vessel.photo_added', $document, 'vessels::events.photo_added', ['name' => $vessel->name]);

        return $document;
    }
}
