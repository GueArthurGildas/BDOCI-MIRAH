<?php

namespace Modules\Indicators\Tests\Feature;

use App\Enums\InfractionStatus;
use App\Models\Infraction;
use App\Models\License;
use App\Models\Position;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use App\Support\Events\VesselStateBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Assumes('H-8-3')]
class FleetViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_agents_cannot_open_the_fleet_view(): void
    {
        $this->signInAs(Role::Agent);

        $this->get('/fleet')->assertForbidden();
        $this->get('/indicators/silent_beacons')->assertForbidden();
    }

    #[Assumes('H-8-4')]
    public function test_directors_land_on_the_fleet_view_and_others_on_the_dashboard(): void
    {
        $this->signInAs(Role::Director);
        $this->get('/')->assertRedirect(route('fleet.index'));

        $this->signInAs(Role::Agent);
        $this->get('/')->assertRedirect(route('dashboard'));
    }

    #[Covers('UC25', partial: true)]
    public function test_every_tile_count_equals_the_length_of_the_list_it_opens(): void
    {
        $this->signInAs(Role::Director);
        $device = TrackingDevice::factory()->create();
        Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours(10)]);
        License::factory()->for(Vessel::factory()->create())->expiringIn(10)->create();
        Infraction::factory()->create(['status' => InfractionStatus::Reported]);

        $response = $this->get('/fleet')->assertOk();

        $response->viewData('tiles')->each(function (array $tile) {
            $this->get(route('indicators.show', $tile['key']))
                ->assertOk()
                ->assertViewHas('rows', fn ($rows) => $rows->total() === $tile['count']);
        });
        $this->assertSame(1, $response->viewData('tiles')->firstWhere('key', 'silent_beacons')['count']);
        $this->assertSame(1, $response->viewData('tiles')->firstWhere('key', 'expiring_licenses')['count']);
        $this->assertSame(1, $response->viewData('tiles')->firstWhere('key', 'open_infractions')['count']);
    }

    #[Covers('UC25', partial: true)]
    public function test_the_fleet_view_ranks_vessels_by_vigilance_score(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->expired()->create();
        app(VesselStateBuilder::class)->refresh($vessel->id);

        $this->get('/fleet')->assertOk()->assertSee($vessel->name)
            ->assertViewHas('ranking', fn ($ranking) => $ranking->first()['vessel']->is($vessel) && $ranking->first()['score']['total'] > 0);
    }
}
