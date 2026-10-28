<?php

namespace Modules\Infractions\Tests\Feature;

use App\Models\InfractionCase;
use App\Models\LegalEntity;
use App\Models\Person;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use App\Support\Infractions\RepeatOffense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC16', 'UC17', partial: true)]
class RepeatOffenseTest extends TestCase
{
    use RefreshDatabase;

    #[Assumes('H-6-1')]
    public function test_a_second_case_makes_the_vessel_captain_and_shipowner_repeat_offenders(): void
    {
        $vessel = Vessel::factory()->create();
        $captain = Person::factory()->create();
        $owner = LegalEntity::factory()->create();
        $first = InfractionCase::factory()->create(['vessel_id' => $vessel->id, 'captain_id' => $captain->id, 'shipowner_id' => $owner->id]);

        $this->assertFalse(RepeatOffense::for($first)->isRepeat());

        $second = InfractionCase::factory()->create(['vessel_id' => $vessel->id, 'captain_id' => $captain->id, 'shipowner_id' => $owner->id]);
        $repeat = RepeatOffense::for($second);

        $this->assertTrue($repeat->isVesselRepeat());
        $this->assertTrue($repeat->isCaptainRepeat());
        $this->assertTrue($repeat->isShipownerRepeat());
        $this->assertSame(2, $repeat->combinedCases);
    }

    #[Assumes('H-6-1')]
    public function test_the_threshold_is_a_setting(): void
    {
        $vessel = Vessel::factory()->create();
        InfractionCase::factory()->count(2)->create(['vessel_id' => $vessel->id]);
        $case = InfractionCase::where('vessel_id', $vessel->id)->first();

        config(['surveillance.infractions.repeat_threshold' => 3]);

        $this->assertFalse(RepeatOffense::for($case)->isRepeat());
    }

    public function test_a_captain_with_cases_on_other_vessels_is_still_a_repeat_offender(): void
    {
        $captain = Person::factory()->create();
        InfractionCase::factory()->create(['captain_id' => $captain->id]);
        $case = InfractionCase::factory()->create(['captain_id' => $captain->id]);

        $repeat = RepeatOffense::for($case);

        $this->assertFalse($repeat->isVesselRepeat());
        $this->assertTrue($repeat->isCaptainRepeat());
        $this->assertSame(1, $repeat->combinedCases);
    }

    public function test_the_database_view_counts_cases_per_vessel_captain_and_shipowner(): void
    {
        $vessel = Vessel::factory()->create();
        InfractionCase::factory()->count(3)->create(['vessel_id' => $vessel->id]);

        $this->assertSame(3, (int) DB::table('v_repeat_offenses')->where('vessel_id', $vessel->id)->value('cases_count'));
    }

    public function test_the_case_page_flags_repeat_offenders(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        InfractionCase::factory()->create(['vessel_id' => $vessel->id]);
        $case = InfractionCase::factory()->create(['vessel_id' => $vessel->id]);

        $this->get(route('infractions.show', $case))->assertOk()->assertSee(__('infractions::infractions.repeat'));
    }
}
