<?php

namespace Modules\Infractions\Actions;

use App\Enums\InfractionCaseStatus;
use App\Models\InfractionCase;
use App\Models\ViolationReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Documents\DocumentStore;
use App\Support\Documents\PdfRenderer;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Modules\Infractions\Support\CaseWorkflow;
use Modules\Infractions\Support\Numbering;

class RecordViolationReport extends Action
{
    public function __construct(
        private readonly Numbering $numbering,
        private readonly DocumentStore $documents,
        private readonly PdfRenderer $pdf,
    ) {}

    public function code(): string
    {
        return 'infractions.record_violation_report';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsManage;
    }

    public function handle(ActionContext $context, array $input): ViolationReport
    {
        $case = InfractionCase::lockForUpdate()->findOrFail($input['infraction_case_id']);

        if ($case->violationReport()->exists()) {
            throw ValidationException::withMessages(['violation_report' => __('infractions::infractions.errors.violation_report_exists')]);
        }

        $notifiedAt = filled($input['notified_at'] ?? null) ? Carbon::parse($input['notified_at']) : null;
        $number = filled($input['number'] ?? null) ? $input['number'] : $this->numbering->violationReportNumber(now()->year);
        $isSignatureRefused = (bool) ($input['is_signature_refused'] ?? false);

        $case->load(['vessel', 'captain', 'shipowner', 'upheld.type', 'evidence', 'seals', 'measures', 'seizures']);

        $document = $this->documents->storeContent(
            "{$number}.pdf",
            $this->pdf->render('infractions::pdf.violation-report', compact('case', 'number', 'notifiedAt', 'isSignatureRefused')),
            'application/pdf',
            $context->actor,
            freeze: true,
        );
        $this->documents->attach($document, $case);

        $report = $case->violationReport()->create([
            'number' => $number,
            'notified_at' => $notifiedAt,
            'is_signature_refused' => $isSignatureRefused,
            'document_id' => $document->id,
        ]);

        CaseWorkflow::advanceTo($case, InfractionCaseStatus::Instruction);

        $context->publish(
            vessel: $case->vessel_id,
            module: 'infractions',
            type: 'infraction_case.violation_report_recorded',
            subject: $report,
            summaryKey: 'infractions::events.violation_report_recorded',
            params: ['reference' => $case->reference, 'number' => $number],
        );

        return $report;
    }
}
