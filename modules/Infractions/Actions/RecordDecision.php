<?php

namespace Modules\Infractions\Actions;

use App\Models\Decision;
use App\Models\LitigationFollowUp;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use App\Support\Documents\PdfRenderer;
use Illuminate\Validation\ValidationException;

class RecordDecision extends Action
{
    public function __construct(
        private readonly DocumentStore $documents,
        private readonly PdfRenderer $pdf,
    ) {}

    public function code(): string
    {
        return 'infractions.record_decision';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsDecide;
    }

    public function handle(ActionContext $context, array $input): Decision
    {
        $followUp = LitigationFollowUp::lockForUpdate()->with('infractionCase.vessel')->findOrFail($input['follow_up_id']);
        $case = $followUp->infractionCase;

        if ($case->isClosed() || $followUp->closed_at !== null) {
            throw ValidationException::withMessages(['follow_up' => __('infractions::infractions.errors.follow_up_closed')]);
        }

        $decision = $followUp->decisions()->create([
            'label' => $input['label'],
            'decided_by' => $context->actor->id,
            'decided_at' => now(),
            'is_penalty' => (bool) ($input['is_penalty'] ?? false),
        ]);

        $document = $this->documents->storeContent(
            "Decision_{$case->reference}_{$decision->id}.pdf",
            $this->pdf->render('infractions::pdf.decision', ['case' => $case, 'followUp' => $followUp, 'decision' => $decision, 'decider' => $context->actor]),
            'application/pdf',
            $context->actor,
            freeze: true,
        );
        $this->documents->attach($document, $decision);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.decision_recorded',
            subject: $decision,
            summaryKey: $decision->is_penalty ? 'infractions::events.penalty_decided' : 'infractions::events.decision_recorded',
            params: ['reference' => $case->reference],
        );

        return $decision;
    }
}
