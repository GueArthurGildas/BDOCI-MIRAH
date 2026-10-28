<?php

namespace Modules\Reports\Actions;

use App\Models\ScheduledReport;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;

class SaveScheduledReport extends Action
{
    public function code(): string
    {
        return 'reports.save_scheduled_report';
    }

    public function permission(): Permission
    {
        return Permission::ReportsManage;
    }

    public function handle(ActionContext $context, array $input): ScheduledReport
    {
        $attributes = Arr::only($input, ['code', 'frequency', 'format', 'recipient_id', 'is_active']);

        if (isset($input['scheduled_report_id'])) {
            return tap(ScheduledReport::findOrFail($input['scheduled_report_id']))->update($attributes);
        }

        return ScheduledReport::create($attributes);
    }
}
