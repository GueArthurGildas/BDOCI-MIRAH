<?php

namespace Modules\Infractions\Actions;

use App\Enums\InfractionCaseStatus;
use App\Enums\InfractionQualification;
use App\Models\InfractionCase;
use App\Models\InfractionType;
use App\Models\Vessel;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use App\Support\Infractions\CaseParties;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Modules\Infractions\Support\Numbering;

class OpenInfractionCase extends Action
{
    public function __construct(private readonly Numbering $numbering) {}

    public function code(): string
    {
        return 'infractions.open';
    }

    public function permission(): Permission
    {
        return Permission::InfractionsCreate;
    }

    public function handle(ActionContext $context, array $input): InfractionCase
    {
        $vessel = Vessel::findOrFail($input['vessel_id']);
        $openedAt = isset($input['opened_at']) ? Carbon::parse($input['opened_at']) : now();
        $parties = CaseParties::forVessel($vessel);

        $case = InfractionCase::create([
            ...Arr::only($input, ['operation_id', 'inspection_id', 'mission_id', 'alert_id', 'witness_report_id', 'facts', 'captain_observations']),
            'reference' => $this->numbering->caseReference($openedAt->year),
            'vessel_id' => $vessel->id,
            'captain_id' => $input['captain_id'] ?? $parties['captain_id'],
            'shipowner_id' => $input['shipowner_id'] ?? $parties['shipowner_id'],
            'qualification' => InfractionQualification::from($input['qualification'] ?? InfractionQualification::Presumed->value),
            'status' => InfractionCaseStatus::Open,
            'opened_by' => $context->actor->id,
            'opened_at' => $openedAt,
        ]);

        foreach (array_unique($input['infraction_type_ids'] ?? []) as $typeId) {
            $case->upheld()->create(['infraction_type_id' => InfractionType::findOrFail($typeId)->id, 'upheld_at' => $openedAt]);
        }

        $context->publish(
            vessel: $vessel,
            module: 'infractions',
            type: 'infraction_case.opened',
            subject: $case,
            summaryKey: 'infractions::events.opened',
            params: ['reference' => $case->reference],
            occurredAt: $openedAt,
        );

        return $case;
    }
}
