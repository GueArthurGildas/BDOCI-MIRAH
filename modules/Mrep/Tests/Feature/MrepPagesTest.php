<?php

namespace Modules\Mrep\Tests\Feature;

use App\Enums\EntryDecision;
use App\Models\MrepDpep;
use App\Models\MrepEntryDecision;
use App\Models\MrepGiesRefusal;
use App\Models\MrepNotification;
use App\Models\MrepPortCall;
use App\Models\MrepTargeting;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class MrepPagesTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC13', partial: true)]
    public function test_a_dpep_shows_its_decision_and_port_calls(): void
    {
        $this->signInAs(Role::TeamLead);
        $dpep = MrepDpep::factory()->create(['number' => 'DPEP/ABJ/2026/871']);
        MrepEntryDecision::factory()->for($dpep, 'dpep')->create(['decision' => EntryDecision::Refusal, 'decision_maker' => 'Direction des pêches']);
        MrepPortCall::factory()->for($dpep, 'dpep')->create(['is_inspected' => true]);

        $this->get(route('mrep.dpeps.show', $dpep))
            ->assertOk()
            ->assertSee('DPEP/ABJ/2026/871')
            ->assertSee(__('enums.entry_decision.refusal'))
            ->assertSee('Direction des pêches');
    }

    #[Covers('UC13', partial: true)]
    public function test_dpeps_can_be_filtered_by_the_latest_decision(): void
    {
        $this->signInAs(Role::Director);
        $refused = MrepDpep::factory()->create(['number' => 'DPEP-REFUSED']);
        MrepEntryDecision::factory()->for($refused, 'dpep')->create(['decision' => EntryDecision::Refusal]);
        $authorized = MrepDpep::factory()->create(['number' => 'DPEP-AUTHORIZED']);
        MrepEntryDecision::factory()->for($authorized, 'dpep')->create();

        $this->get('/mrep?decision=refusal')->assertOk()->assertSee('DPEP-REFUSED')->assertDontSee('DPEP-AUTHORIZED');
    }

    #[Covers('UC14', partial: true)]
    public function test_notifications_to_states_are_listed_on_the_dpep(): void
    {
        $this->signInAs(Role::Agent);
        $dpep = MrepDpep::factory()->create();
        MrepNotification::factory()->for($dpep, 'dpep')->create(['recipient' => 'Etat du pavillon (ES)']);

        $this->get(route('mrep.dpeps.show', $dpep))->assertOk()->assertSee('Etat du pavillon (ES)');
    }

    #[Covers('UC13', partial: true)]
    public function test_refusals_and_targetings_are_listed(): void
    {
        $this->signInAs(Role::Agent);
        MrepGiesRefusal::factory()->create(['reference' => 'GIES-2026-014']);
        MrepTargeting::factory()->create(['target' => 'reefer_vessels']);

        $this->get('/mrep/gies-refusals')->assertOk()->assertSee('GIES-2026-014');
        $this->get('/mrep/targetings')->assertOk()->assertSee('reefer_vessels');
    }

    #[Covers('UC13', partial: true)]
    public function test_the_administrator_holds_no_business_access_to_the_mirror(): void
    {
        $this->signInAs(Role::Admin);

        $this->get('/mrep')->assertForbidden();
    }

    #[Covers('UC13', partial: true)]
    public function test_guests_are_redirected(): void
    {
        $this->get('/mrep')->assertRedirect('/login');
    }
}
