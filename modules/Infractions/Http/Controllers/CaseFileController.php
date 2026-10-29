<?php

namespace Modules\Infractions\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\Decision;
use App\Models\Document;
use App\Models\InfractionCase;
use App\Models\LitigationFollowUp;
use App\Models\PrecautionaryMeasure;
use App\Support\Actions\ActionRunner;
use App\Support\Documents\DocumentStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Infractions\Actions\AddEvidenceItem;
use Modules\Infractions\Actions\CloseInfractionCase;
use Modules\Infractions\Actions\DecidePrecautionaryMeasure;
use Modules\Infractions\Actions\FileAppeal;
use Modules\Infractions\Actions\OpenLitigationFollowUp;
use Modules\Infractions\Actions\ProposePrecautionaryMeasure;
use Modules\Infractions\Actions\RecordAppealOutcome;
use Modules\Infractions\Actions\RecordDecision;
use Modules\Infractions\Actions\RecordPayment;
use Modules\Infractions\Actions\RecordSeal;
use Modules\Infractions\Actions\RecordSeizure;
use Modules\Infractions\Actions\RecordViolationReport;
use Modules\Infractions\Actions\UpholdInfraction;
use Modules\Infractions\Http\Requests\AppealOutcomeRequest;
use Modules\Infractions\Http\Requests\AppealRequest;
use Modules\Infractions\Http\Requests\DecisionRequest;
use Modules\Infractions\Http\Requests\EvidenceRequest;
use Modules\Infractions\Http\Requests\FollowUpRequest;
use Modules\Infractions\Http\Requests\MeasureRequest;
use Modules\Infractions\Http\Requests\PaymentRequest;
use Modules\Infractions\Http\Requests\SealRequest;
use Modules\Infractions\Http\Requests\SeizureRequest;
use Modules\Infractions\Http\Requests\UpholdRequest;
use Modules\Infractions\Http\Requests\ViolationReportRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CaseFileController extends Controller
{
    public function uphold(InfractionCase $case, UpholdRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(UpholdInfraction::class, $request->user(), [...$request->validated(), 'infraction_case_id' => $case->id]);

        return $this->back($case, 'upheld');
    }

    public function evidence(InfractionCase $case, EvidenceRequest $request, ActionRunner $actions, DocumentStore $documents): RedirectResponse
    {
        $actions->run(AddEvidenceItem::class, $request->user(), [
            'infraction_case_id' => $case->id,
            'description' => $request->validated('description'),
            'document_id' => $request->hasFile('file') ? $documents->store($request->file('file'), $request->user())->id : null,
        ]);

        return $this->back($case, 'evidence');
    }

    public function seal(InfractionCase $case, SealRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordSeal::class, $request->user(), [...$request->validated(), 'infraction_case_id' => $case->id]);

        return $this->back($case, 'seal');
    }

    public function seizure(InfractionCase $case, SeizureRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordSeizure::class, $request->user(), [...$request->validated(), 'infraction_case_id' => $case->id]);

        return $this->back($case, 'seizure');
    }

    public function proposeMeasure(InfractionCase $case, MeasureRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(ProposePrecautionaryMeasure::class, $request->user(), [...$request->validated(), 'infraction_case_id' => $case->id]);

        return $this->back($case, 'measure_proposed');
    }

    public function decideMeasure(PrecautionaryMeasure $measure, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(DecidePrecautionaryMeasure::class, $request->user(), ['measure_id' => $measure->id]);

        return $this->back($measure->infractionCase, 'measure_decided');
    }

    public function violationReport(InfractionCase $case, ViolationReportRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordViolationReport::class, $request->user(), [...$request->validated(), 'infraction_case_id' => $case->id]);

        return $this->back($case, 'violation_report');
    }

    public function followUp(InfractionCase $case, FollowUpRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(OpenLitigationFollowUp::class, $request->user(), [...$request->validated(), 'infraction_case_id' => $case->id]);

        return $this->back($case, 'follow_up');
    }

    public function decision(LitigationFollowUp $followUp, DecisionRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordDecision::class, $request->user(), [...$request->validated(), 'follow_up_id' => $followUp->id]);

        return $this->back($followUp->infractionCase, 'decision');
    }

    public function payment(Decision $decision, PaymentRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordPayment::class, $request->user(), [...$request->validated(), 'decision_id' => $decision->id]);

        return $this->back($decision->followUp->infractionCase, 'payment');
    }

    public function appeal(Decision $decision, AppealRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(FileAppeal::class, $request->user(), [...$request->validated(), 'decision_id' => $decision->id]);

        return $this->back($decision->followUp->infractionCase, 'appeal');
    }

    public function appealOutcome(Appeal $appeal, AppealOutcomeRequest $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(RecordAppealOutcome::class, $request->user(), [...$request->validated(), 'appeal_id' => $appeal->id]);

        return $this->back($appeal->decision->followUp->infractionCase, 'appeal_outcome');
    }

    public function close(InfractionCase $case, Request $request, ActionRunner $actions): RedirectResponse
    {
        $actions->run(CloseInfractionCase::class, $request->user(), ['infraction_case_id' => $case->id]);

        return $this->back($case, 'closed')->with('popup', true);
    }

    public function download(InfractionCase $case, Document $document): StreamedResponse
    {
        $linked = DB::table('document_links')->where('document_id', $document->id)->where(fn ($query) => $query
            ->where(['object_type' => $case->getMorphClass(), 'object_id' => $case->id])
            ->orWhere(fn ($query) => $query->where('object_type', (new Decision)->getMorphClass())->whereIn('object_id', $case->followUps()->join('decisions', 'decisions.follow_up_id', '=', 'litigation_follow_ups.id')->select('decisions.id'))))
            ->exists();

        abort_unless($linked, 404);

        return Storage::disk(DocumentStore::DISK)->download($document->path, $document->name);
    }

    private function back(InfractionCase $case, string $key): RedirectResponse
    {
        return to_route('infractions.show', $case)->with('status', __("infractions::infractions.messages.{$key}"));
    }
}
