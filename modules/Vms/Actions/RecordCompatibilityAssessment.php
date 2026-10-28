<?php

namespace Modules\Vms\Actions;

use App\Enums\VmsRequestStatus;
use App\Models\CompatibilityAssessment;
use App\Models\VmsAssessmentRequest;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RecordCompatibilityAssessment extends Action
{
    public function code(): string
    {
        return 'vms.record_assessment';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): CompatibilityAssessment
    {
        $request = VmsAssessmentRequest::findOrFail($input['assessment_request_id']);

        $assessment = CompatibilityAssessment::create([
            'assessment_request_id' => $request->id,
            'result' => $input['result'],
            'assessed_on' => $input['assessed_on'] ?? today(),
        ]);

        $request->update(['status' => VmsRequestStatus::Assessed]);

        $context->publish(
            vessel: $request->vessel_id,
            module: 'vms',
            type: 'assessment.recorded',
            subject: $assessment,
            summaryKey: 'vms::events.assessment_recorded',
            params: ['result' => $assessment->result->value],
        );

        return $assessment;
    }
}
