<?php

namespace Modules\Tracking\Actions;

use App\Models\AlertRule;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Vms\SilenceThreshold;

class SaveAlertRule extends Action
{
    public function code(): string
    {
        return 'tracking.save_alert_rule';
    }

    public function permission(): Permission
    {
        return Permission::SettingsManage;
    }

    public function handle(ActionContext $context, array $input): AlertRule
    {
        $values = [
            'type' => $input['type'],
            'threshold_hours' => $input['threshold_hours'] ?? null,
            'is_active' => (bool) ($input['is_active'] ?? false),
        ];

        $rule = isset($input['alert_rule_id'])
            ? tap(AlertRule::findOrFail($input['alert_rule_id']))->update($values)
            : AlertRule::create(['code' => $input['code'], ...$values]);

        app(SilenceThreshold::class)->forget();

        return $rule;
    }
}
