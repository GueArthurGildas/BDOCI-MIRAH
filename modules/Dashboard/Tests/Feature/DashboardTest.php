<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Enums\ObservationStatus;
use App\Models\DataSource;
use App\Models\Observation;
use App\Models\User;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_users_without_permission_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertForbidden();
    }

    public function test_dashboard_shows_key_indicators(): void
    {
        $this->signInAs(Role::Inspector);
        DataSource::factory()->create(['name' => 'VMS / THEMIS']);
        Observation::factory()->create(['description' => 'Rupture d\'émission à surveiller', 'status' => ObservationStatus::Open]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('VMS / THEMIS')
            ->assertSee('Rupture d\'émission à surveiller', escape: true)
            ->assertViewHas('stats', fn (array $stats) => collect($stats)->firstWhere('label', 'pending_observations')['value'] === 1);
    }

    public function test_dashboard_is_highlighted_in_the_sidebar(): void
    {
        $this->signInAs(Role::Inspector);

        $this->get('/dashboard')
            ->assertSee('<li class="active">', escape: false)
            ->assertSee(route('dashboard'));
    }
}
