<?php

namespace Modules\Vessels\Tests\Feature;

use App\Enums\InfractionStatus;
use App\Models\Infraction;
use App\Models\License;
use App\Models\RegisteredModule;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class Vessel360Test extends TestCase
{
    use RefreshDatabase;

    private function publish(Vessel $vessel, string $module, string $key): void
    {
        VesselEvent::create([
            'vessel_id' => $vessel->id, 'occurred_at' => now(), 'type' => $module.'.event',
            'module_id' => RegisteredModule::firstOrCreate(['code' => $module], ['label' => $module])->id,
            'object_type' => 'vessel', 'object_id' => $vessel->id, 'summary_key' => $key, 'summary_params' => ['name' => $vessel->name],
        ]);
    }

    #[Covers('UC25', partial: true)]
    public function test_the_header_shows_the_status_light_and_its_reasons(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expired()->create();
        Infraction::factory()->create(['vessel_id' => $vessel->id, 'status' => InfractionStatus::Reported]);

        $this->get(route('vessels.show', $vessel))
            ->assertOk()
            ->assertSee(__('enums.vessel_status_light.in_violation'))
            ->assertSee(__('vigilance.reasons.license_expired'))
            ->assertViewHas('assessment', fn ($assessment) => $assessment->level->value === 'in_violation');
    }

    #[Covers('UC25', partial: true)]
    public function test_the_indicators_tab_shows_the_vigilance_score_with_its_components(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expired()->create();

        $this->get(route('vessels.show', $vessel))
            ->assertOk()
            ->assertSee(__('vigilance.score.components.license_blocked'))
            ->assertViewHas('score', fn ($score) => $score['total'] === 20);
    }

    #[Covers('UC25', partial: true)]
    public function test_the_timeline_reads_the_vessel_events_and_filters_by_module(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        $this->publish($vessel, 'licenses', 'vessels::events.registered');
        $this->publish($vessel, 'watch', 'vessels::events.updated');

        $this->get(route('vessels.show', $vessel))->assertViewHas('timeline', fn ($timeline) => $timeline->count() === 2);
        $this->get(route('vessels.show', ['vessel' => $vessel, 'module' => 'watch']))
            ->assertViewHas('timeline', fn ($timeline) => $timeline->count() === 1 && $timeline->first()['module'] === 'watch');
    }

    #[Assumes('H-8-4')]
    #[Covers('UC25', partial: true)]
    public function test_tabs_follow_the_permissions_of_the_reader(): void
    {
        $this->signInWithPermissions(Permission::VesselsView);
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.show', $vessel))
            ->assertOk()
            ->assertViewHas('tabs', fn (array $tabs) => in_array('timeline', $tabs) && ! in_array('infractions', $tabs) && ! in_array('indicators', $tabs) && ! in_array('communications', $tabs));
    }
}
