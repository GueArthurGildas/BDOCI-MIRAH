<?php

namespace Modules\Watch\Tests\Feature;

use App\Enums\WatchOrigin;
use App\Enums\WatchStatus;
use App\Models\CheckCounter;
use App\Models\StandingInstruction;
use App\Models\SurveillanceObservation;
use App\Models\Vessel;
use App\Models\WatchEntry;
use App\Queries\MorningBoard;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class MorningBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_and_administrators_are_forbidden(): void
    {
        $this->get(route('watch.board.index'))->assertRedirect(route('login'));

        $this->signInAs(Role::Admin);
        $this->get(route('watch.board.index'))->assertForbidden();
    }

    #[Covers('UC26')]
    public function test_the_board_lists_open_watch_entries_by_origin_then_by_age(): void
    {
        $this->signInAs(Role::Agent);
        $vessels = Vessel::factory(4)->create();
        WatchEntry::factory()->create(['vessel_id' => $vessels[0]->id, 'origin' => WatchOrigin::EnhancedMonitoring, 'started_at' => now()->subDays(5)]);
        WatchEntry::factory()->create(['vessel_id' => $vessels[1]->id, 'origin' => WatchOrigin::DirectorListManual, 'started_at' => now()->subHour()]);
        WatchEntry::factory()->create(['vessel_id' => $vessels[2]->id, 'origin' => WatchOrigin::DirectorListManual, 'started_at' => now()->subDays(2)]);
        WatchEntry::factory()->create(['vessel_id' => $vessels[3]->id, 'origin' => WatchOrigin::Logbook, 'status' => WatchStatus::Closed, 'closed_at' => now()]);

        $this->assertSame(
            [$vessels[2]->id, $vessels[1]->id, $vessels[0]->id],
            MorningBoard::watchEntries()->pluck('vessel_id')->all(),
        );

        $this->get(route('watch.board.index'))->assertOk()
            ->assertSee(route('vessels.show', $vessels[0]), false)
            ->assertDontSee(route('vessels.show', $vessels[3]), false);
    }

    #[Covers('UC26', 'RG10', partial: true)]
    public function test_the_board_shows_standing_instructions_in_force_and_open_observations(): void
    {
        $this->signInAs(Role::Agent);
        StandingInstruction::factory()->create(['vessel_id' => null, 'body' => 'Consigne générale en vigueur']);
        StandingInstruction::factory()->create(['vessel_id' => null, 'body' => 'Consigne levée hier', 'started_on' => today()->subWeek(), 'ended_on' => today()->subDay()]);
        StandingInstruction::factory()->create(['vessel_id' => null, 'body' => 'Consigne future', 'started_on' => today()->addDay()]);
        SurveillanceObservation::factory()->create(['body' => 'Observation encore ouverte']);
        SurveillanceObservation::factory()->create(['body' => 'Observation déjà clôturée', 'status' => 'closed']);

        $this->get(route('watch.board.index'))->assertOk()
            ->assertSee('Consigne générale en vigueur')
            ->assertDontSee('Consigne levée hier')
            ->assertDontSee('Consigne future')
            ->assertSee('Observation encore ouverte')
            ->assertDontSee('Observation déjà clôturée');
    }

    #[Covers('UC26', 'UC09', partial: true)]
    public function test_the_team_lead_adds_a_vessel_to_the_directors_list_and_closes_it(): void
    {
        $lead = $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();

        $this->post(route('watch.board.director-list'), ['vessel_id' => $vessel->id])->assertRedirect(route('watch.board.index'));

        $entry = WatchEntry::sole();
        $this->assertSame(WatchOrigin::DirectorListManual, $entry->origin);
        $this->assertSame($lead->id, $entry->decided_by);

        $this->post(route('watch.board.director-list'), ['vessel_id' => $vessel->id]);
        $this->assertSame(1, WatchEntry::count());

        $this->post(route('watch.board.close', $entry))->assertRedirect();
        $this->assertSame(WatchStatus::Closed, $entry->fresh()->status);
        $this->assertNotNull($entry->fresh()->closed_at);
    }

    #[Covers('UC26', partial: true)]
    public function test_agents_cannot_edit_the_directors_list_nor_close_watch_entries(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        $entry = WatchEntry::factory()->create();

        $this->post(route('watch.board.director-list'), ['vessel_id' => $vessel->id])->assertForbidden();
        $this->post(route('watch.board.close', $entry))->assertForbidden();

        $this->assertSame(1, WatchEntry::count());
        $this->assertSame(WatchStatus::Open, $entry->fresh()->status);
    }

    #[Covers('UC09')]
    public function test_an_agent_places_a_vessel_under_enhanced_monitoring_with_origin_and_decider(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post(route('watch.board.enhanced-monitoring'), ['vessel_id' => $vessel->id])->assertRedirect();

        $entry = WatchEntry::sole();
        $this->assertSame(WatchOrigin::EnhancedMonitoring, $entry->origin);
        $this->assertSame($agent->id, $entry->decided_by);
        $this->assertSame('watch.enhanced_monitoring_started', $vessel->events()->sole()->type);
    }

    #[Covers('UC09', 'UC26', partial: true)]
    public function test_a_vessel_checked_too_often_is_proposed_then_confirmed_by_the_agent(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        CheckCounter::factory()->create(['vessel_id' => $vessel->id, 'checks_count' => 4, 'is_alert_raised' => true]);

        $this->assertSame([$vessel->id], MorningBoard::checkAlerts()->pluck('vessel_id')->all());
        $this->get(route('watch.board.index'))->assertOk()->assertSee(__('watch::watch.board.confirm_enhanced_monitoring'));

        $this->post(route('watch.board.enhanced-monitoring'), ['vessel_id' => $vessel->id, 'origin' => 'more_than_3_checks']);

        $this->assertSame(WatchOrigin::MoreThan3Checks, WatchEntry::sole()->origin);
        $this->assertCount(0, MorningBoard::checkAlerts());
    }

    #[Covers('UC09', partial: true)]
    #[Assumes('H-3a-2')]
    public function test_closing_the_proposal_watch_entry_restarts_the_check_counter(): void
    {
        $lead = $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        CheckCounter::factory()->create(['vessel_id' => $vessel->id, 'checks_count' => 5, 'is_alert_raised' => true]);
        $this->post(route('watch.board.enhanced-monitoring'), ['vessel_id' => $vessel->id, 'origin' => 'more_than_3_checks']);

        $this->post(route('watch.board.close', WatchEntry::sole()));

        $counter = CheckCounter::find($vessel->id);
        $this->assertSame(0, $counter->checks_count);
        $this->assertFalse($counter->is_alert_raised);
        $this->assertSame($lead->id, WatchEntry::sole()->decided_by);
    }
}
