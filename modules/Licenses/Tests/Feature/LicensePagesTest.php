<?php

namespace Modules\Licenses\Tests\Feature;

use App\Enums\LicenseStatus;
use App\Models\License;
use App\Models\User;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class LicensePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_permission_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/licenses')->assertForbidden();
    }

    public function test_licenses_are_listed_with_vessel_links(): void
    {
        $this->signInAs(Role::Agent);
        $license = License::factory()->create(['number' => 'LIC-2026-0042']);

        $this->get('/licenses')
            ->assertOk()
            ->assertSee('LIC-2026-0042')
            ->assertSee(route('vessels.show', $license->vessel));
    }

    #[Covers('UC29', partial: true)]
    public function test_licenses_can_be_filtered_by_status(): void
    {
        $this->signInAs(Role::Agent);
        License::factory()->expired()->create(['number' => 'LIC-EXPIRED']);
        License::factory()->expiringIn(5)->create(['number' => 'LIC-EXPIRING']);

        $this->get('/licenses?status='.LicenseStatus::Expiring->value)
            ->assertOk()
            ->assertSee('LIC-EXPIRING')
            ->assertDontSee('LIC-EXPIRED');
    }
}
