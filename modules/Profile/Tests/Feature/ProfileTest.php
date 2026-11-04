<?php

namespace Modules\Profile\Tests\Feature;

use App\Enums\AlertRuleType;
use App\Enums\ObservationStatus;
use App\Models\Alert;
use App\Models\InternalNote;
use App\Models\Observation;
use App\Models\Team;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_the_profile_shows_name_first_name_function_role_and_team(): void
    {
        $team = Team::factory()->create(['name' => 'Équipe Abidjan']);
        $user = $this->signInAs(Role::TeamLead, ['name' => 'Koné', 'first_name' => 'Mamadou', 'job_title' => 'Chef du quart', 'team_id' => $team->id]);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Mamadou')
            ->assertSee('Koné')
            ->assertSee('Chef du quart')
            ->assertSee(Role::labelFor(Role::TeamLead->value))
            ->assertSee('Équipe Abidjan')
            ->assertSee($user->email);
    }

    public function test_the_full_name_puts_the_first_name_before_the_name(): void
    {
        $this->assertSame('Mamadou Koné', (new User(['first_name' => 'Mamadou', 'name' => 'Koné']))->fullName());
        $this->assertSame('Koné Mamadou', (new User(['name' => 'Koné Mamadou']))->fullName());
    }

    public function test_the_header_links_to_the_profile_with_the_full_name(): void
    {
        $this->signInAs(Role::Agent, ['name' => 'Diallo', 'first_name' => 'Awa']);

        $this->get(route('dashboard'))->assertSee(route('profile.show'), false)->assertSee('Awa Diallo');
    }

    public function test_a_user_changes_and_removes_their_photo(): void
    {
        Storage::fake(DocumentStore::DISK);
        $user = $this->signInAs(Role::Agent);

        $this->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->image('me.jpg', 300, 300)])->assertRedirect(route('profile.show'));

        $user->refresh();
        $this->assertNotNull($user->avatar_document_id);
        $this->assertSame(route('profile.avatar', $user), $user->avatarUrl());
        $this->get($user->avatarUrl())->assertOk()->assertHeader('Cache-Control');
        $this->get(route('profile.show'))->assertSee(route('profile.avatar', $user), false);

        $this->delete(route('profile.avatar.destroy'))->assertRedirect(route('profile.show'));
        $this->assertNull($user->fresh()->avatar_document_id);
        $this->get(route('profile.avatar', $user))->assertNotFound();
    }

    public function test_only_images_are_accepted_as_a_photo(): void
    {
        $user = $this->signInAs(Role::Agent);

        $this->post(route('profile.avatar.update'), ['avatar' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf')])->assertSessionHasErrors('avatar');
        $this->assertNull($user->fresh()->avatar_document_id);
    }

    public function test_the_personal_space_lists_the_messages_addressed_to_the_user_with_their_body(): void
    {
        $user = $this->signInAs(Role::Agent);
        InternalNote::factory()->create(['recipient_user_id' => $user->id, 'recipient_team_id' => null, 'free_text' => 'Contrôler la balise du AKWABA', 'is_read' => false]);
        InternalNote::factory()->create(['recipient_user_id' => User::factory()->create()->id, 'free_text' => 'Message pour un autre']);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Contrôler la balise du AKWABA')
            ->assertDontSee('Message pour un autre');
    }

    public function test_urgent_alerts_and_urgent_messages_appear_under_urgencies(): void
    {
        $user = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        VesselCurrentState::updateOrCreate(['vessel_id' => $vessel->id], ['status_light' => 'in_violation']);
        Alert::factory()->create(['vessel_id' => $vessel->id]);
        InternalNote::factory()->create(['recipient_user_id' => $user->id, 'recipient_team_id' => null, 'urgency' => 'high', 'free_text' => 'Intervention immédiate', 'is_read' => false]);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertViewHas('space', fn (array $space) => $space['urgentAlerts']->count() === 1 && $space['urgentNotes']->count() === 1)
            ->assertSee('Intervention immédiate');
    }

    public function test_pending_activities_gather_the_users_open_observations_and_due_messages(): void
    {
        $user = $this->signInAs(Role::Agent);
        Observation::factory()->create(['author_id' => $user->id, 'status' => ObservationStatus::Open, 'description' => 'Observation à suivre XYZ']);
        Observation::factory()->create(['author_id' => User::factory()->create()->id, 'team_id' => null, 'status' => ObservationStatus::Open, 'description' => 'Observation étrangère QRS']);
        InternalNote::factory()->create(['recipient_user_id' => $user->id, 'recipient_team_id' => null, 'due_date' => now()->addDays(2), 'is_read' => false, 'free_text' => 'À traiter avant vendredi']);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Observation à suivre XYZ')
            ->assertDontSee('Observation étrangère QRS')
            ->assertSee('À traiter avant vendredi');
    }

    public function test_the_alert_types_label_is_used_not_a_raw_value(): void
    {
        $this->signInAs(Role::Agent);
        Alert::factory()->create(['type' => AlertRuleType::Speed]);

        $this->get(route('profile.show'))->assertDontSee('enums.alert_rule_type');
    }
}
