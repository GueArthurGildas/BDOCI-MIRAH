<?php

namespace Modules\Infractions\Tests\Feature;

use App\Enums\InfractionCaseStatus;
use App\Models\InfractionCase;
use App\Models\InfractionType;
use App\Models\UpheldInfraction;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class InfractionPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_and_users_without_permission_are_forbidden(): void
    {
        $this->get('/infractions')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/infractions')->assertForbidden();
    }

    #[Covers('UC16', partial: true)]
    public function test_cases_can_be_filtered_by_status_and_upheld_type(): void
    {
        $this->signInAs(Role::Agent);
        $type = InfractionType::factory()->create(['label' => 'Pêche en zone interdite']);
        $open = InfractionCase::factory()->create(['reference' => 'DI-OPEN', 'status' => InfractionCaseStatus::Open]);
        UpheldInfraction::factory()->create(['infraction_case_id' => $open->id, 'infraction_type_id' => $type->id]);
        InfractionCase::factory()->create(['reference' => 'DI-CLOSED', 'status' => InfractionCaseStatus::Closed]);

        $this->get('/infractions?status=open')->assertOk()->assertSee('DI-OPEN')->assertDontSee('DI-CLOSED');
        $this->get('/infractions?type='.$type->id)->assertOk()->assertSee('DI-OPEN')->assertDontSee('DI-CLOSED');
    }

    public function test_the_list_has_a_toolbar_with_a_page_size_and_column_filters(): void
    {
        $this->signInAs(Role::Agent);
        InfractionCase::factory()->count(12)->create();

        $response = $this->get('/infractions?per_page=10')->assertOk()
            ->assertSee('id="list-filters"', false)
            ->assertSee('name="status" form="list-filters"', false)
            ->assertSee('name="type" form="list-filters"', false);

        $this->assertCount(10, $response->viewData('cases')->items());
        $this->assertCount(12, $this->get('/infractions?per_page=100')->viewData('cases')->items());
    }

    #[Covers('UC16', 'RG09', partial: true)]
    public function test_the_case_page_shows_the_whole_file(): void
    {
        $this->signInAs(Role::Director);
        $case = InfractionCase::factory()->create(['reference' => 'DI-2026-0042', 'facts' => 'Engin prohibé trouvé à bord.']);
        UpheldInfraction::factory()->create(['infraction_case_id' => $case->id, 'infraction_type_id' => InfractionType::factory()->create(['label' => 'Engin prohibé'])->id]);

        $this->get(route('infractions.show', $case))->assertOk()->assertSee('DI-2026-0042')->assertSee('Engin prohibé trouvé à bord.')->assertSee('Engin prohibé');
    }

    public function test_agents_cannot_open_or_update_cases(): void
    {
        $this->signInAs(Role::Agent);
        $case = InfractionCase::factory()->create();

        $this->get(route('infractions.create'))->assertForbidden();
        $this->patch(route('infractions.update', $case), ['facts' => 'x', 'qualification' => 'presumed'])->assertForbidden();
    }

    #[Covers('UC16', partial: true)]
    public function test_the_creation_form_is_prefilled_from_the_origin(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create(['name' => 'ATLANTIQUE 9']);

        $this->get(route('infractions.create', ['vessel' => $vessel->id, 'facts' => 'Constat à quai']))->assertOk()->assertSee('Constat à quai')->assertSee('ATLANTIQUE 9');
    }
}
