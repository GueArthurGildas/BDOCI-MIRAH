<?php

namespace Modules\Vms\Actions;

use App\Enums\VmsRequestStatus;
use App\Models\VmsAssessmentRequest;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class RequestVmsAssessment extends Action
{
    public function code(): string
    {
        return 'vms.request_assessment';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): VmsAssessmentRequest
    {
        $request = VmsAssessmentRequest::create([
            'vessel_id' => $input['vessel_id'],
            'requested_on' => $input['requested_on'] ?? today(),
            'status' => VmsRequestStatus::Requested,
        ]);

        $context->publish(vessel: $request->vessel_id, module: 'vms', type: 'assessment.requested', subject: $request, summaryKey: 'vms::events.assessment_requested');

        return $request;
    }
}
