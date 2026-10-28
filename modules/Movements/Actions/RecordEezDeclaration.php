<?php

namespace Modules\Movements\Actions;

use App\Enums\EezCheckResult;
use App\Enums\EezDeclarationStatus;
use App\Enums\EezDeclarationType;
use App\Enums\EezEntryOrigin;
use App\Enums\EntryExitAlertStatus;
use App\Models\EezDeclaration;
use App\Models\EntryExitAlert;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Arr;
use Modules\Movements\Services\EezChecker;
use Modules\Movements\Services\EntryExitPairing;
use Modules\Movements\Services\VesselMatcher;

class RecordEezDeclaration extends Action
{
    public function __construct(
        private readonly VesselMatcher $matcher,
        private readonly EezChecker $checker,
        private readonly EntryExitPairing $pairing,
    ) {}

    public function code(): string
    {
        return 'movements.record_eez_declaration';
    }

    public function permission(): Permission
    {
        return Permission::MovementsDeclare;
    }

    public function handle(ActionContext $context, array $input): EezDeclaration
    {
        $declaration = EezDeclaration::create([
            ...Arr::except($input, ['catches']),
            'vessel_id' => $input['vessel_id'] ?? $this->matcher->find($input)?->id,
            'entry_origin' => EezEntryOrigin::Manual,
            'entered_by' => $context->actor->id,
            'status' => EezDeclarationStatus::Received,
        ]);

        foreach ($input['catches'] ?? [] as $catch) {
            $declaration->catches()->create($catch);
        }

        $declaration->load(['vessel', 'catches']);

        foreach ($this->checker->run($declaration) as $check) {
            $declaration->checks()->create($check);
        }

        $declaration->update(['status' => EezDeclarationStatus::Verified]);

        if ($declaration->vessel_id === null) {
            return $declaration;
        }

        EntryExitAlert::create([
            'vessel_id' => $declaration->vessel_id,
            'eez_declaration_id' => $declaration->id,
            'direction' => $declaration->type,
            'alerted_at' => $declaration->received_at,
            'status' => EntryExitAlertStatus::Open,
        ]);

        $this->pairExit($declaration);

        $context->publish(
            vessel: $declaration->vessel_id,
            module: 'movements',
            type: 'eez_declaration.recorded',
            subject: $declaration,
            summaryKey: 'movements::events.eez_declaration_recorded',
            params: [
                'direction' => $declaration->type->value,
                'failed_checks' => $declaration->checks()->where('result', EezCheckResult::Ko)->count(),
            ],
            occurredAt: $declaration->received_at,
        );

        return $declaration->refresh();
    }

    private function pairExit(EezDeclaration $declaration): void
    {
        if ($declaration->type !== EezDeclarationType::Exit) {
            return;
        }

        $entry = $this->pairing->pairingEntryFor($declaration);

        if ($entry === null) {
            return;
        }

        $link = $this->pairing->link($entry, $declaration);

        if ($this->pairing->exceedsTolerance($entry, $link->variance_kg)) {
            $declaration->update(['status' => EezDeclarationStatus::Variance]);
        }
    }
}
