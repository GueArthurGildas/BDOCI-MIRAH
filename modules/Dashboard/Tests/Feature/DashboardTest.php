<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Models\User;
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

    public function test_authenticated_users_can_see_the_dashboard(): void
    {
        $user = User::factory()->create(['name' => 'Jane Customer']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Jane Customer');
    }

    public function test_dashboard_is_registered_in_the_sidebar(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertSee('<li class="active">', escape: false)
            ->assertSee(route('dashboard'));
    }
}
