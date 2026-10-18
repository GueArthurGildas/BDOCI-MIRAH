<?php

namespace Modules\Dashboard\Tests\Feature;

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

    public function test_authorized_users_can_see_the_dashboard(): void
    {
        $this->signInAs(Role::Inspector, ['name' => 'Jane Inspector']);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Jane Inspector');
    }

    public function test_dashboard_is_registered_in_the_sidebar(): void
    {
        $this->signInAs(Role::Inspector);

        $this->get('/dashboard')
            ->assertSee('<li class="active">', escape: false)
            ->assertSee(route('dashboard'));
    }
}
