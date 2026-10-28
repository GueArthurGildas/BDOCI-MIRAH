<?php

namespace Modules\Tracking\Actions;

use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Models\AlertHandling;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;

class HandleAlert extends Action
{
    public const QUESTIONS = ['position_checked', 'beacon_powered', 'technical_stop_declared', 'other_source_confirms'];

    public function code(): string
    {
        return 'tracking.handle_alert';
    }

    public function permission(): Permission
    {
        return Permission::TrackingCreate;
    }

    public function handle(ActionContext $context, array $input): AlertHandling
    {
        $alert = Alert::findOrFail($input['alert_id']);

        $handling = AlertHandling::create([
            'alert_id' => $alert->id,
            'verification_questions' => collect(self::QUESTIONS)->mapWithKeys(fn (string $question) => [$question => (bool) ($input['verification_questions'][$question] ?? false)])->all(),
            'is_vessel_contacted' => (bool) ($input['is_vessel_contacted'] ?? false),
            'measures' => $input['measures'] ?? null,
            'handled_by' => $context->actor->id,
            'handled_at' => now(),
        ]);

        $closed = (bool) ($input['close'] ?? false);

        $alert->update([
            'status' => $closed ? AlertStatus::Closed : AlertStatus::Acknowledged,
            'acknowledged_by' => $alert->acknowledged_by ?? $context->actor->id,
            'operation_id' => $input['operation_id'] ?? $alert->operation_id,
        ]);

        $context->publish(
            vessel: $alert->vessel_id,
            module: 'tracking',
            type: $closed ? 'alert.closed' : 'alert.handled',
            subject: $alert,
            summaryKey: $closed ? 'tracking::events.alert_closed' : 'tracking::events.alert_handled',
            params: ['type' => $alert->type->value],
        );

        return $handling;
    }
}
