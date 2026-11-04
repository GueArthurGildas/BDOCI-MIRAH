<?php

namespace Modules\Tracking\Tests\Feature;

use App\Models\Position;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ZonePeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_period_counts_each_vessel_once_per_zone(): void
    {
        $this->signInAs(Role::Agent);
        $zone = Zone::factory()->create();
        $other = Zone::factory()->create();
        $first = Vessel::factory()->create();
        $second = Vessel::factory()->create();

        Position::factory()->for($first)->create(['zone_id' => $zone->id, 'recorded_at' => now()->subDays(2)]);
        Position::factory()->for($first)->create(['zone_id' => $zone->id, 'recorded_at' => now()->subDays(1)]);
        Position::factory()->for($second)->create(['zone_id' => $zone->id, 'recorded_at' => now()->subDays(3)]);
        Position::factory()->for($second)->create(['zone_id' => $other->id, 'recorded_at' => now()->subDays(20)]);

        $this->get(route('tracking.zones', ['last' => '7d']))
            ->assertOk()
            ->assertViewHas('vesselCountByZone', fn ($counts) => $counts->get($zone->id) === 2 && ! $counts->has($other->id));

        $this->get(route('tracking.zones', ['last' => '30d']))
            ->assertViewHas('vesselCountByZone', fn ($counts) => $counts->get($zone->id) === 2 && $counts->get($other->id) === 1);
    }

    public function test_a_custom_period_filters_on_its_bounds(): void
    {
        $this->signInAs(Role::Agent);
        $zone = Zone::factory()->create();
        $inside = Vessel::factory()->create();
        $outside = Vessel::factory()->create();

        Position::factory()->for($inside)->create(['zone_id' => $zone->id, 'recorded_at' => now()->subDays(5)]);
        Position::factory()->for($outside)->create(['zone_id' => $zone->id, 'recorded_at' => now()->subDays(15)]);

        $this->get(route('tracking.zones', ['from' => now()->subDays(8)->format('Y-m-d\TH:i'), 'to' => now()->subDays(2)->format('Y-m-d\TH:i')]))
            ->assertOk()
            ->assertViewHas('vesselCountByZone', fn ($counts) => $counts->get($zone->id) === 1)
            ->assertSee(__('tracking::tracking.zones.period_hint'));
    }

    public function test_an_inverted_period_is_rejected(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('tracking.zones', ['from' => '2026-05-10T00:00', 'to' => '2026-05-01T00:00']))->assertSessionHasErrors('to');
        $this->get(route('tracking.zones', ['last' => '1y']))->assertSessionHasErrors('last');
    }

    public function test_without_a_period_the_zone_situation_keeps_its_instant_mode(): void
    {
        $this->signInAs(Role::Agent);
        $zone = Zone::factory()->create();
        Position::factory()->for(Vessel::factory())->create(['zone_id' => $zone->id, 'recorded_at' => now()->subMinutes(5)]);

        $this->get(route('tracking.zones'))
            ->assertOk()
            ->assertViewHas('vesselCountByZone', fn ($counts) => $counts->get($zone->id) === 1)
            ->assertSee(__('tracking::tracking.zones.freshness_hint', ['hours' => 24]));
    }

    public function test_the_situation_map_embeds_thermis_in_its_own_tab(): void
    {
        $this->signInAs(Role::Agent);
        config(['surveillance.vms.themis_url' => 'https://thermis.example/map', 'surveillance.vms.themis_vessel_url' => 'https://thermis.example/vessel/{identifier}']);
        $vessel = Vessel::factory()->create(['imo_number' => 'IMO4444444']);

        $this->get(route('tracking.map'))->assertOk()->assertSee('href="#map-thermis"', false)->assertSee('<iframe src="https://thermis.example/map"', false);

        $this->get(route('tracking.map', ['vessels' => [$vessel->id]]))
            ->assertOk()
            ->assertViewHas('thermisTabActive', true)
            ->assertSee('<iframe src="https://thermis.example/vessel/IMO4444444"', false);
    }
}
