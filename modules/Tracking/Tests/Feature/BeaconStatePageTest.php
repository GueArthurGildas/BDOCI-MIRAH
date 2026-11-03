<?php

namespace Modules\Tracking\Tests\Feature;

use App\Models\Position;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Queries\BeaconStates;
use App\Queries\SilentBeacons;
use App\Support\Authorization\Role;
use App\Support\Vms\EmissionGap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeaconStatePageTest extends TestCase
{
    use RefreshDatabase;

    private function deviceEmittingHoursAgo(?int $hours): TrackingDevice
    {
        $device = TrackingDevice::factory()->create();

        if ($hours !== null) {
            Position::factory()->for($device->vessel)->create(['recorded_at' => now()->subHours($hours)]);
        }

        return $device;
    }

    public function test_counts_cover_every_state(): void
    {
        $this->signInAs(Role::Agent);
        $this->deviceEmittingHoursAgo(0);
        $this->deviceEmittingHoursAgo(30);
        $this->deviceEmittingHoursAgo(60);
        $this->deviceEmittingHoursAgo(null);

        $this->get('/tracking/beacons')
            ->assertOk()
            ->assertViewHas('countsByStatus', fn ($counts) => $counts->get('emitting') === 1 && $counts->get('silent') === 2 && $counts->get('no_data') === 1);
    }

    public function test_sql_state_matches_the_vessel_beacon_status(): void
    {
        $devices = collect([0, 3, 30, 200, null])->map(fn (?int $hours) => $this->deviceEmittingHoursAgo($hours));

        $states = BeaconStates::query()->get()->pluck('beacon_state', 'id');

        foreach ($devices as $device) {
            $this->assertSame($device->vessel->fresh()->beaconStatus()->value, $states[$device->id]);
        }
    }

    public function test_silent_for_filter_keeps_only_longer_silences(): void
    {
        $this->signInAs(Role::Agent);
        $threeDays = $this->deviceEmittingHoursAgo(80);
        $oneDay = $this->deviceEmittingHoursAgo(26);

        $this->get('/tracking/beacons?silent_for=3_days')
            ->assertOk()
            ->assertSee($threeDays->identifier)
            ->assertDontSee($oneDay->identifier);

        $this->get('/tracking/beacons?silent_for=1_month')->assertDontSee($threeDays->identifier);
    }

    public function test_period_filter_applies_to_the_last_emission(): void
    {
        $this->signInAs(Role::Agent);
        $recent = $this->deviceEmittingHoursAgo(2);
        $old = $this->deviceEmittingHoursAgo(24 * 20);

        $this->get('/tracking/beacons?from='.now()->subDays(3)->toDateString())
            ->assertSee($recent->identifier)
            ->assertDontSee($old->identifier);

        $this->get('/tracking/beacons?to='.now()->subDays(10)->toDateString())
            ->assertSee($old->identifier)
            ->assertDontSee($recent->identifier);
    }

    public function test_search_finds_a_beacon_by_vessel_name_or_identifier(): void
    {
        $this->signInAs(Role::Agent);
        $target = TrackingDevice::factory()->for(Vessel::factory()->state(['name' => 'ATLANTIS ZETA']))->create();
        $other = $this->deviceEmittingHoursAgo(1);

        $this->get('/tracking/beacons?search=ATLANTIS')->assertSee($target->identifier)->assertDontSee($other->identifier);
        $this->get('/tracking/beacons?search='.$other->identifier)->assertSee($other->identifier)->assertDontSee($target->identifier);
    }

    public function test_the_list_is_paginated_by_ten(): void
    {
        $this->signInAs(Role::Agent);
        TrackingDevice::factory()->count(12)->create();

        $this->get('/tracking/beacons')
            ->assertViewHas('devices', fn ($devices) => $devices->perPage() === 10 && $devices->count() === 10 && $devices->total() === 12);
    }

    public function test_silent_beacons_are_listed_first_with_the_longest_silence_on_top(): void
    {
        $this->signInAs(Role::Agent);
        $emitting = $this->deviceEmittingHoursAgo(0);
        $short = $this->deviceEmittingHoursAgo(30);
        $long = $this->deviceEmittingHoursAgo(100);

        $this->get('/tracking/beacons')
            ->assertViewHas('devices', fn ($devices) => $devices->pluck('id')->all() === [$long->id, $short->id, $emitting->id]);
    }

    public function test_gap_is_shown_in_bold_and_states_have_their_own_icon(): void
    {
        $this->signInAs(Role::Agent);
        $this->deviceEmittingHoursAgo(55);

        $this->get('/tracking/beacons')
            ->assertSee('<strong class="gs-gap is-danger">'.__('ui.emission_gap.days_hours', ['days' => 2, 'hours' => 7]).'</strong>', escape: false)
            ->assertSee('icon-wifi-off')
            ->assertSee(__('tracking::tracking.beacons.how_title'));
    }

    public function test_invalid_preset_is_rejected(): void
    {
        $this->signInAs(Role::Agent);

        $this->get('/tracking/beacons?silent_for=forever')->assertSessionHasErrors('silent_for');
    }

    public function test_silent_beacons_query_ignores_emitting_and_never_seen_vessels(): void
    {
        $silent = $this->deviceEmittingHoursAgo(40);
        $this->deviceEmittingHoursAgo(1);
        $this->deviceEmittingHoursAgo(null);

        $this->assertSame([$silent->vessel_id], SilentBeacons::vessels()->pluck('id')->all());
    }

    public function test_gap_formatting(): void
    {
        $this->assertSame('—', EmissionGap::format(null));
        $this->assertSame(__('ui.emission_gap.minutes', ['minutes' => 15]), EmissionGap::format(now()->subMinutes(15)));
        $this->assertSame(__('ui.emission_gap.hours', ['hours' => 5]), EmissionGap::format(now()->subHours(5)));
        $this->assertSame(__('ui.emission_gap.days', ['days' => 3]), EmissionGap::format(now()->subDays(3)));
        $this->assertSame(__('ui.emission_gap.days_hours', ['days' => 1, 'hours' => 2]), EmissionGap::format(now()->subHours(26)));
    }
}
