<?php

namespace Modules\Administration\Tests\Feature;

use App\Models\User;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC24', partial: true)]
class UserAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_administrators_manage_users(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->get('/administration/users')->assertForbidden();
    }

    public function test_administrators_create_users_with_a_role(): void
    {
        $this->signInAs(Role::Admin);

        $this->post('/administration/users', [
            'name' => 'Awa Koné',
            'email' => 'awa.kone@example.com',
            'role' => Role::Agent->value,
            'is_active' => '1',
            'password' => 'Str0ng-passw0rd!',
            'password_confirmation' => 'Str0ng-passw0rd!',
        ])->assertRedirect(route('administration.users.index'));

        $user = User::where('email', 'awa.kone@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole(Role::Agent->value));
        $this->assertTrue(Activity::where('event', 'role_changed')->where('subject_id', $user->id)->exists());
    }

    public function test_a_deactivated_user_is_signed_out_immediately(): void
    {
        $this->signInAs(Role::Admin);
        $agent = User::factory()->create()->assignRole(Role::Agent->value);

        $this->put(route('administration.users.update', $agent), [
            'name' => $agent->name,
            'email' => $agent->email,
            'role' => Role::Agent->value,
            'is_active' => '0',
        ])->assertRedirect();

        $this->actingAs($agent->fresh())->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_administrators_cannot_deactivate_themselves(): void
    {
        $admin = $this->signInAs(Role::Admin);

        $this->put(route('administration.users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => Role::Admin->value,
            'is_active' => '0',
        ])->assertSessionHasErrors('is_active');
    }

    public function test_roles_matrix_lists_every_permission_label(): void
    {
        $this->signInAs(Role::Admin);

        $this->get('/administration/roles')
            ->assertOk()
            ->assertDontSee('administration::')
            ->assertSee(__('administration::administration.permissions.audit.view'));
    }
}
