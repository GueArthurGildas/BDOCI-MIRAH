<?php

namespace Modules\Vessels\Actions;

use App\Models\Document;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use Illuminate\Support\Facades\DB;

class RemoveVesselPhoto extends Action
{
    public function __construct(private readonly DocumentStore $documents) {}

    public function code(): string
    {
        return 'vessels.remove_photo';
    }

    public function permission(): Permission
    {
        return Permission::VesselsManage;
    }

    public function handle(ActionContext $context, array $input): Document
    {
        $vessel = Vessel::findOrFail($input['vessel_id']);
        $document = Document::findOrFail($input['document_id']);

        DB::table('document_links')
            ->where('document_id', $document->id)
            ->where('object_type', $vessel->getMorphClass())
            ->where('object_id', $vessel->id)
            ->delete();

        if ($vessel->primary_photo_id === $document->id) {
            $next = $this->documents->for($vessel)->first(fn (Document $candidate) => str_starts_with((string) $candidate->mime_type, 'image/'));

            Vessel::whereKey($vessel->id)->update(['primary_photo_id' => $next?->id]);
        }

        $context->publish($vessel, 'vessels', 'vessel.photo_removed', $document, 'vessels::events.photo_removed', ['name' => $vessel->name]);

        return $document;
    }
}
