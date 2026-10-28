<?php

namespace Modules\Tracking\Tests\Feature;

use App\Enums\BeaconStatus;
use App\Models\Position;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class TrackingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrators_cannot_access_tracking(): void
    {
        $this->signInAs(Role::Admin);

        $this->get('/tracking/map')->assertForbidden();
    }

    #[Covers('UC05', partial: true)]
    public function test_map_shows_the_last_position_of_each_vessel(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create(['name' => 'TIAGBA 9']);
        Position::factory()->for($vessel)->create(['recorded_at' => now()->subHours(3)]);
        Position::factory()->for($vessel)->create(['recorded_at' => now()->subMinutes(10)]);

        $this->get('/tracking/map')
            ->assertOk()
            ->assertViewHas('positionsCount', 1)
            ->assertSee('TIAGBA 9');
    }

    #[Covers('RG06', 'UC05', partial: true)]
    public function test_zone_situation_counts_vessels_present_at_a_given_time(): void
    {
        $this->signInAs(Role::Agent);
        $abidjan = Zone::factory()->create(['name' => 'Abidjan']);
        $sanPedro = Zone::factory()->create(['name' => 'San Pedro']);
        $vessel = Vessel::factory()->create();
        Position::factory()->for($vessel)->create(['zone_id' => $sanPedro->id, 'recorded_at' => now()->subDays(2)]);
        Position::factory()->for($vessel)->create(['zone_id' => $abidjan->id, 'recorded_at' => now()->subMinutes(5)]);

        $this->get('/tracking/zones')
            ->assertOk()
            ->assertViewHas('vesselCountByZone', fn ($counts) => $counts->get($abidjan->id) === 1 && ! $counts->has($sanPedro->id));

        $this->get('/tracking/zones?at='.now()->subDays(2)->addMinute()->format('Y-m-d H:i'))
            ->assertViewHas('vesselCountByZone', fn ($counts) => $counts->get($sanPedro->id) === 1 && ! $counts->has($abidjan->id));
    }

    #[Covers('RG04', 'UC06', partial: true)]
    public function test_silent_beacons_are_detected(): void
    {
        $this->signInAs(Role::Agent);
        $silent = TrackingDevice::factory()->create();
        Position::factory()->for($silent->vessel)->create(['recorded_at' => now()->subHours(10)]);
        $emitting = TrackingDevice::factory()->create();
        Position::factory()->for($emitting->vessel)->create(['recorded_at' => now()->subMinutes(10)]);

        $this->get('/tracking/beacons?status='.BeaconStatus::Silent->value)
            ->assertOk()
            ->assertSee($silent->identifier)
            ->assertDontSee($emitting->identifier);
    }
}
