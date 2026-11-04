<?php

namespace Modules\Administration\Tests\Feature;

use App\Enums\UserType;
use App\Models\Team;
use App\Models\User;
use App\Support\Authorization\Role;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_details_are_stored_when_an_account_is_created(): void
    {
        $this->signInAs(Role::Admin);

        $this->post(route('administration.users.store'), [
            'name' => 'Marie Kouadio',
            'email' => 'marie@example.com',
            'job_title' => 'Operator',
            'phone' => '+225 01 02 03 04',
            'whatsapp_number' => '+225 05 06 07 08',
            'role' => Role::Agent->value,
            'is_active' => 1,
            'password' => 'a-strong-passphrase-42',
            'password_confirmation' => 'a-strong-passphrase-42',
        ])->assertRedirect(route('administration.users.index'));

        $user = User::where('email', 'marie@example.com')->sole();
        $this->assertSame('Operator', $user->job_title);
        $this->assertSame('+225 05 06 07 08', $user->whatsapp_number);
        $this->assertSame(UserType::Human, $user->type);
    }

    public function test_the_first_name_is_stored_and_edited_with_the_account(): void
    {
        $admin = $this->signInAs(Role::Admin);

        $this->post(route('administration.users.store'), [
            'name' => 'Kouadio',
            'first_name' => 'Marie',
            'email' => 'marie.k@example.com',
            'role' => Role::Agent->value,
            'is_active' => 1,
            'password' => 'a-strong-passphrase-42',
            'password_confirmation' => 'a-strong-passphrase-42',
        ])->assertRedirect(route('administration.users.index'));

        $user = User::where('email', 'marie.k@example.com')->sole();
        $this->assertSame('Marie', $user->first_name);
        $this->assertSame('Marie Kouadio', $user->fullName());

        $this->put(route('administration.users.update', $user), [
            'name' => 'Kouadio',
            'first_name' => 'Marie-Claire',
            'email' => 'marie.k@example.com',
            'role' => Role::Agent->value,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame('Marie-Claire', $user->fresh()->first_name);
        $this->get(route('administration.users.edit', $user))->assertSee('name="first_name"', false)->assertSee('Marie-Claire');
        $this->assertTrue($admin->isNot($user));
    }

    public function test_the_last_sign_in_is_recorded(): void
    {
        $user = User::factory()->create(['email' => 'agent@example.com']);
        $this->assertNull($user->last_login_at);

        $this->post('/login', ['email' => 'agent@example.com', 'password' => 'password']);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_accounts_have_a_type_and_cannot_hold_an_unknown_one(): void
    {
        $user = User::factory()->create();
        $this->assertSame(UserType::Human, $user->fresh()->type);

        $this->expectException(QueryException::class);
        DB::table('users')->where('id', $user->id)->update(['type' => 'robot']);
    }

    public function test_a_team_has_a_lead(): void
    {
        $lead = User::factory()->create();
        $team = Team::factory()->create(['lead_user_id' => $lead->id]);

        $this->assertTrue($team->lead->is($lead));
    }
}
