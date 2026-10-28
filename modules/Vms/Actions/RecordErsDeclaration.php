<?php

namespace Modules\Vms\Actions;

use App\Enums\ErsSource;
use App\Models\ErsDeclaration;
use App\Models\Presentation;
use App\Support\Actions\Action;
use App\Support\Actions\ActionContext;
use App\Support\Authorization\Permission;
use Illuminate\Support\Carbon;

class RecordErsDeclaration extends Action
{
    public function code(): string
    {
        return 'vms.record_ers';
    }

    public function permission(): Permission
    {
        return Permission::VmsCreate;
    }

    public function handle(ActionContext $context, array $input): ErsDeclaration
    {
        $declaration = ErsDeclaration::create([
            'vessel_id' => $input['vessel_id'],
            'declared_at' => isset($input['declared_at']) ? Carbon::parse($input['declared_at']) : now(),
            'source' => $input['source'] ?? ErsSource::ManualEntry,
        ]);

        foreach ($input['lines'] as $line) {
            $presentation = isset($line['presentation_id']) ? Presentation::find($line['presentation_id']) : null;

            $declaration->lines()->create([
                'species_id' => $line['species_id'],
                'presentation_id' => $presentation?->id,
                'conversion_factor' => $line['conversion_factor'] ?? $presentation?->conversion_factor,
                'live_weight_kg' => $line['live_weight_kg'],
                'fao_zone' => $line['fao_zone'] ?? null,
                'gear_id' => $line['gear_id'] ?? null,
            ]);
        }

        $context->publish(
            vessel: $declaration->vessel_id,
            module: 'vms',
            type: 'ers.recorded',
            subject: $declaration,
            summaryKey: 'vms::events.ers_recorded',
            params: ['lines' => count($input['lines'])],
        );

        return $declaration;
    }
}
