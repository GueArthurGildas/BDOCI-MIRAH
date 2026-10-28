<?php

namespace Modules\Indicators\Tests\Feature;

use App\Enums\FleetSegment;
use App\Models\License;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use App\Support\Events\VesselStateBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Assumes('H-8-3')]
class IndicatorDetailTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC29', partial: true)]
    public function test_the_detail_lists_the_vessels_behind_the_indicator(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create(['name' => 'AKWABA 1']);
        License::factory()->for($vessel)->expiringIn(12)->create();
        Vessel::factory()->create(['name' => 'BANDAMA 7']);

        $this->get('/indicators/expiring_licenses')
            ->assertOk()
            ->assertSee('AKWABA 1')
            ->assertDontSee('BANDAMA 7');
    }

    #[Covers('UC29', partial: true)]
    public function test_the_detail_can_be_filtered_by_flag_segment_and_search(): void
    {
        $this->signInAs(Role::Director);
        $ivorian = Vessel::factory()->create(['name' => 'AKWABA 1', 'flag' => 'CI', 'fleet_segment' => FleetSegment::Industrial]);
        $foreign = Vessel::factory()->create(['name' => 'ATLANTIC STAR', 'flag' => 'ES', 'fleet_segment' => FleetSegment::AuthorizedForeign]);
        License::factory()->for($ivorian)->expiringIn(12)->create();
        License::factory()->for($foreign)->expiringIn(12)->create();

        $this->get('/indicators/expiring_licenses?flag=CI')->assertSee('AKWABA 1')->assertDontSee('ATLANTIC STAR');
        $this->get('/indicators/expiring_licenses?segment='.FleetSegment::AuthorizedForeign->value)->assertSee('ATLANTIC STAR')->assertDontSee('AKWABA 1');
        $this->get('/indicators/expiring_licenses?search=atlantic')->assertSee('ATLANTIC STAR')->assertDontSee('AKWABA 1');
    }

    #[Covers('UC29', partial: true)]
    public function test_the_detail_can_be_filtered_by_period(): void
    {
        $this->signInAs(Role::Director);
        $soon = Vessel::factory()->create(['name' => 'AKWABA 1']);
        $later = Vessel::factory()->create(['name' => 'BANDAMA 7']);
        License::factory()->for($soon)->expiringIn(5)->create();
        License::factory()->for($later)->expiringIn(25)->create();

        $this->get('/indicators/expiring_licenses?to='.today()->addDays(10)->toDateString())
            ->assertSee('AKWABA 1')
            ->assertDontSee('BANDAMA 7');
    }

    #[Covers('UC29', partial: true)]
    public function test_status_light_indicators_list_the_vessels_with_the_reasons(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create(['name' => 'AKWABA 1']);
        License::factory()->for($vessel)->expired()->create();
        app(VesselStateBuilder::class)->refresh($vessel->id);

        $this->get('/indicators/status_suspect')
            ->assertOk()
            ->assertSee('AKWABA 1')
            ->assertSee(__('vigilance.reasons.license_expired'));
    }

    public function test_an_unknown_indicator_is_not_found(): void
    {
        $this->signInAs(Role::Director);

        $this->get('/indicators/unknown')->assertNotFound();
    }
}
