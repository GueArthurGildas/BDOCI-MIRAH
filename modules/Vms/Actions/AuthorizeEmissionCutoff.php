<?php

namespace Modules\Vms\Actions;

use App\Models\VmsCutoffAuthorization;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class AuthorizeEmissionCutoff extends Action
{
    public function code(): string
    {
        return 'vms.authorize_cutoff';
    }

    public function permission(): Permission
    {
        return Permission::VmsManage;
    }

    public function handle(ActionContext $context, array $input): VmsCutoffAuthorization
    {
        $authorization = VmsCutoffAuthorization::create([
            'vessel_id' => $input['vessel_id'],
            'started_at' => Carbon::parse($input['started_at']),
            'ended_at' => Carbon::parse($input['ended_at']),
            'reason' => $input['reason'] ?? null,
        ]);

        $context->publish(vessel: $authorization->vessel_id, module: 'vms', type: 'cutoff.authorized', subject: $authorization, summaryKey: 'vms::events.cutoff_authorized');

        return $authorization;
    }
}
