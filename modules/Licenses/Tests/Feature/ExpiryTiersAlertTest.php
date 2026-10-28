<?php

namespace Modules\Licenses\Tests\Feature;

use App\Models\License;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('RG07', 'UC03', partial: true)]
class ExpiryTiersAlertTest extends TestCase
{
    use RefreshDatabase;

    #[Assumes('H-4a-6')]
    public function test_one_alert_is_sent_per_tier_with_the_licenses_of_that_band(): void
    {
        $agent = $this->signInAs(Role::Agent);
        License::factory()->expiringIn(10)->create();
        License::factory()->expiringIn(30)->create();
        License::factory()->expiringIn(45)->create();
        License::factory()->expiringIn(80)->count(2)->create();
        License::factory()->expiringIn(200)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $counts = $agent->notifications()->get()->mapWithKeys(fn ($notification) => [$notification->data['params']['days'] => $notification->data['params']['count']])->all();
        $this->assertSame([30 => 2, 60 => 1, 90 => 2], $counts);
    }

    public function test_the_tiers_follow_the_administrator_settings(): void
    {
        $agent = $this->signInAs(Role::Agent);
        config(['surveillance.license_expiry_warning_days' => 15, 'surveillance.license_expiry_second_warning_days' => 120, 'surveillance.license_expiry_third_warning_days' => 180]);
        License::factory()->expiringIn(100)->create();

        $this->artisan('licenses:notify-expiring')->assertSuccessful();

        $this->assertSame([120], $agent->notifications()->get()->map(fn ($notification) => $notification->data['params']['days'])->all());
    }

    public function test_the_list_page_filters_a_tier(): void
    {
        $this->signInAs(Role::Agent);
        License::factory()->expiringIn(10)->create(['number' => 'LIC-NEAR']);
        License::factory()->expiringIn(45)->create(['number' => 'LIC-MID']);

        $this->get(route('licenses.index', ['expiring_after' => 31, 'expiring_within' => 60]))
            ->assertOk()
            ->assertSee('LIC-MID')
            ->assertDontSee('LIC-NEAR');
    }
}
