<?php

namespace Database\Seeders;

use App\Models\InfractionCase;
use App\Models\Mission;
use App\Models\VesselEvent;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Database\Seeder;

class Phase8DemoSeeder extends Seeder
{
    public function run(): void
    {
        $modules = app(ModuleRegistry::class);

        InfractionCase::query()->get()->each(fn (InfractionCase $infraction) => VesselEvent::firstOrCreate(
            ['vessel_id' => $infraction->vessel_id, 'type' => 'infraction.recorded', 'object_type' => $infraction->getMorphClass(), 'object_id' => $infraction->id],
            [
                'occurred_at' => $infraction->opened_at,
                'module_id' => $modules->idFor('infractions'),
                'summary_key' => 'vessels::events.infraction_recorded',
                'summary_params' => ['reference' => $infraction->reference],
            ],
        ));

        Mission::query()->with('observedUnits.vessel')->get()->each(fn (Mission $mission) => $mission->observedUnits->pluck('vessel')->filter()->unique('id')->each(fn ($vessel) => VesselEvent::firstOrCreate(
            ['vessel_id' => $vessel->id, 'type' => 'mission.assigned', 'object_type' => $mission->getMorphClass(), 'object_id' => $mission->id],
            [
                'occurred_at' => $mission->startsAt() ?? now(),
                'module_id' => $modules->idFor('missions'),
                'summary_key' => 'vessels::events.mission_assigned',
                'summary_params' => ['reference' => $mission->code],
            ],
        )));
    }
}
