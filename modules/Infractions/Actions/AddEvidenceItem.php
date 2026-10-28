<?php

namespace Modules\Infractions\Actions;

use App\Enums\InfractionCaseStatus;
use App\Models\Document;
use App\Models\EvidenceItem;
use App\Models\InfractionCase;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use Modules\Infractions\Support\CaseWorkflow;

class AddEvidenceItem extends Action
{
    public function __construct(private readonly DocumentStore $documents) {}

    public function code(): string
    {
        return 'infractions.add_evidence';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManage;
    }

    public function handle(ActionContext $context, array $input): EvidenceItem
    {
        $case = InfractionCase::lockForUpdate()->findOrFail($input['infraction_case_id']);
        $document = isset($input['document_id']) ? Document::findOrFail($input['document_id']) : null;

        $item = $case->evidence()->create(['description' => $input['description'], 'document_id' => $document?->id]);

        if ($document) {
            $this->documents->attach($document, $case);
        }

        CaseWorkflow::advanceTo($case, InfractionCaseStatus::Instruction);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.evidence_added',
            subject: $item,
            summaryKey: 'infractions::events.evidence_added',
            params: ['reference' => $case->reference],
        );

        return $item;
    }
}
