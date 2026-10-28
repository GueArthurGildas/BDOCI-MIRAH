<?php

namespace Tests\Feature\Vigilance;

use App\Models\License;
use App\Models\RegisteredModule;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\WatchEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Attributes\Covers;
use Tests\TestCase;

class CalculatedViewsTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC25', partial: true)]
    public function test_the_authorized_and_expiring_views_follow_the_licenses(): void
    {
        $authorized = Vessel::factory()->create();
        License::factory()->for($authorized)->expiringIn(200)->create();
        $expiring = Vessel::factory()->create();
        License::factory()->for($expiring)->expiringIn(20)->create();
        $expired = Vessel::factory()->create();
        License::factory()->for($expired)->expired()->create();

        $this->assertEqualsCanonicalizing([$authorized->id, $expiring->id], DB::table('v_authorized_vessels')->pluck('vessel_id')->all());
        $this->assertSame([$expiring->id], DB::table('v_expiring_licenses')->pluck('vessel_id')->all());
        $this->assertSame(20, (int) DB::table('v_expiring_licenses')->value('days_left'));
    }

    public function test_the_morning_board_view_lists_open_watch_entries_only(): void
    {
        $open = WatchEntry::factory()->create(['status' => 'open']);
        WatchEntry::factory()->create(['status' => 'closed', 'closed_at' => now()]);

        $this->assertSame([$open->id], DB::table('v_morning_board')->pluck('id')->all());
    }

    public function test_the_timeline_view_joins_events_with_their_module_code(): void
    {
        $vessel = Vessel::factory()->create();
        $module = RegisteredModule::create(['code' => 'licenses', 'label' => 'Licenses']);
        VesselEvent::create([
            'vessel_id' => $vessel->id, 'occurred_at' => now(), 'type' => 'license.registered', 'module_id' => $module->id,
            'object_type' => 'license', 'object_id' => 1, 'summary_key' => 'licenses::events.registered', 'summary_params' => [],
        ]);

        $row = DB::table('v_vessel_timeline')->where('vessel_id', $vessel->id)->first();

        $this->assertSame('licenses', $row->module);
    }
}
