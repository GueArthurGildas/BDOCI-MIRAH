<?php

namespace Modules\Watch\Tests\Feature;

use App\Enums\SurveillanceObservationStatus;
use App\Models\CheckCounter;
use App\Models\LogbookEntry;
use App\Models\Relay;
use App\Models\ShiftHandover;
use App\Models\SurveillanceObservation;
use App\Models\Team;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Authorization\Role;
use App\Support\Logbook\LogbookCalendar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Watch\Notifications\RelayAlert;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class SurveillanceObservationTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC08', 'RG08')]
    public function test_an_observation_creates_the_logbook_line_the_vessel_record_and_the_check_counter(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post(route('watch.observations.store'), ['vessel_id' => $vessel->id, 'body' => 'Vitesse incohérente au créneau de 10 h.'])
            ->assertRedirect(route('watch.board.index'));

        $observation = SurveillanceObservation::sole();
        $this->assertSame($vessel->id, $observation->vessel_id);
        $this->assertSame($agent->id, $observation->created_by);
        $this->assertSame(SurveillanceObservationStatus::Monitoring, $observation->status);
        $this->assertSame(LogbookEntry::sole()->id, $observation->logbook_entry_id);
        $this->assertSame([$vessel->id], LogbookEntry::sole()->vessels->pluck('id')->all());

        $counter = CheckCounter::find($vessel->id);
        $this->assertSame(1, $counter->checks_count);
        $this->assertFalse($counter->is_alert_raised);
        $this->assertNotNull($counter->last_checked_at);

        $this->assertContains('observation.recorded', VesselEvent::pluck('type')->all());
        $this->assertSame($agent->id, Activity::where('event', 'watch.record_observation')->sole()->causer_id);
        $this->get(route('vessels.show', $vessel))->assertOk();
    }

    #[Covers('UC08', 'UC09')]
    #[Assumes('H-3a-2')]
    public function test_beyond_the_threshold_the_alert_is_raised_and_the_agent_is_told(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        foreach (range(1, 3) as $check) {
            $this->post(route('watch.observations.store'), ['vessel_id' => $vessel->id, 'body' => "Vérification {$check}"]);
        }

        $this->assertFalse(CheckCounter::find($vessel->id)->is_alert_raised);

        $this->post(route('watch.observations.store'), ['vessel_id' => $vessel->id, 'body' => 'Vérification 4'])
            ->assertSessionHas('error');

        $counter = CheckCounter::find($vessel->id);
        $this->assertSame(4, $counter->checks_count);
        $this->assertTrue($counter->is_alert_raised);
    }

    #[Covers('UC09', partial: true)]
    #[Assumes('H-3a-2')]
    public function test_the_threshold_is_a_setting(): void
    {
        config(['surveillance.watch.check_threshold' => 1]);
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post(route('watch.observations.store'), ['vessel_id' => $vessel->id, 'body' => 'Un']);
        $this->assertFalse(CheckCounter::find($vessel->id)->is_alert_raised);

        $this->post(route('watch.observations.store'), ['vessel_id' => $vessel->id, 'body' => 'Deux']);
        $this->assertTrue(CheckCounter::find($vessel->id)->is_alert_raised);
    }

    #[Covers('UC08', 'RG10')]
    #[Assumes('H-3a-5')]
    public function test_a_relayed_observation_creates_a_handover_and_notifies_the_next_team(): void
    {
        $current = Team::factory()->create();
        $next = Team::factory()->create();
        $member = User::factory()->create(['team_id' => $next->id]);
        $lead = User::factory()->create();
        $next->update(['lead_user_id' => $lead->id]);
        $agent = $this->signInAs(Role::Agent, ['team_id' => $current->id]);
        $vessel = Vessel::factory()->create();

        $this->post(route('watch.observations.store'), ['vessel_id' => $vessel->id, 'body' => 'À surveiller de nuit.', 'relay_to_team_id' => $next->id]);

        $relay = Relay::sole();
        $this->assertSame($next->id, $relay->next_team_id);
        $this->assertSame(SurveillanceObservation::sole()->id, $relay->surveillance_observation_id);
        $handover = ShiftHandover::sole();
        $this->assertSame($current->id, $handover->rotation->team_id);
        $this->assertSame(app(LogbookCalendar::class)->today()->day->toDateString(), $handover->rotation->day->toDateString());

        $this->assertCount(1, $member->notifications);
        $this->assertCount(1, $lead->notifications);
        $this->assertSame(RelayAlert::class, $member->notifications->first()->type);
        $this->assertCount(0, $agent->notifications);
    }

    #[Covers('UC08', partial: true)]
    public function test_a_relay_requires_the_author_to_belong_to_a_team_and_writes_nothing_otherwise(): void
    {
        $next = Team::factory()->create();
        $this->signInAs(Role::Agent, ['team_id' => null]);
        $vessel = Vessel::factory()->create();

        $this->post(route('watch.observations.store'), ['vessel_id' => $vessel->id, 'body' => 'Sans équipe', 'relay_to_team_id' => $next->id])
            ->assertSessionHasErrors('relay_to_team_id');

        $this->assertSame(0, SurveillanceObservation::count());
        $this->assertSame(0, LogbookEntry::count());
        $this->assertSame(0, CheckCounter::count());
    }

    #[Covers('UC08', 'RG10')]
    #[Assumes('H-3a-5')]
    public function test_only_the_next_team_acknowledges_a_relay(): void
    {
        $next = Team::factory()->create();
        $relay = Relay::factory()->create(['next_team_id' => $next->id]);

        $this->signInAs(Role::Agent, ['team_id' => Team::factory()->create()->id]);
        $this->post(route('watch.relays.acknowledge', $relay))->assertForbidden();
        $this->assertNull($relay->fresh()->acknowledged_at);

        $reader = $this->signInAs(Role::Agent, ['team_id' => $next->id]);
        $this->get(route('watch.board.index'))->assertOk()->assertSee(__('watch::watch.board.acknowledge'));
        $this->post(route('watch.relays.acknowledge', $relay))->assertRedirect(route('watch.board.index'));

        $this->assertSame($reader->id, $relay->fresh()->read_acknowledged_by);
        $this->assertNotNull($relay->fresh()->acknowledged_at);
    }

    #[Covers('UC08', partial: true)]
    public function test_an_observation_can_be_closed_or_handed_to_an_operation(): void
    {
        $this->signInAs(Role::Agent);
        $observation = SurveillanceObservation::factory()->create();

        $this->patch(route('watch.observations.update', $observation), ['status' => 'operation'])->assertRedirect();
        $this->assertSame(SurveillanceObservationStatus::Operation, $observation->fresh()->status);

        $this->patch(route('watch.observations.update', $observation), ['status' => 'closed']);
        $this->assertSame(SurveillanceObservationStatus::Closed, $observation->fresh()->status);

        $this->patch(route('watch.observations.update', $observation), ['status' => 'bogus'])->assertSessionHasErrors('status');
    }

    public function test_the_observation_pages_render(): void
    {
        $this->signInAs(Role::Agent);
        SurveillanceObservation::factory()->create(['body' => 'Observation listée']);

        $this->get(route('watch.observations.index'))->assertOk()->assertSee('Observation listée');
        $this->get(route('watch.observations.create'))->assertOk();
    }
}
