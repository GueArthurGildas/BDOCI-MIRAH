<?php

namespace Modules\Watch\Tests\Feature;

use App\Models\Rotation;
use App\Models\ShiftHandover;
use App\Models\StandingInstruction;
use App\Models\Team;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class RotationAndInstructionTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC05', partial: true)]
    #[Assumes('H-3a-1')]
    public function test_rotations_cycle_through_the_configured_number_of_teams(): void
    {
        config(['surveillance.watch.teams_count' => 2]);
        $teams = Team::factory(3)->create();
        $this->signInAs(Role::TeamLead);

        $this->post(route('watch.rotations.plan'), ['start_on' => '2026-11-02', 'days_count' => 5])->assertRedirect(route('watch.rotations.index'));

        $this->assertSame(
            [$teams[0]->id, $teams[1]->id, $teams[0]->id, $teams[1]->id, $teams[0]->id],
            Rotation::orderBy('day')->pluck('team_id')->all(),
        );
        $this->assertSame('08:00', substr(Rotation::first()->start_time, 0, 5));
        $this->assertSame('18:00', substr(Rotation::first()->end_time, 0, 5));
    }

    #[Covers('UC05', partial: true)]
    #[Assumes('H-3a-1')]
    public function test_non_working_days_are_skipped_and_existing_days_are_kept(): void
    {
        config(['surveillance.watch.working_days' => [1, 2, 3, 4, 5]]);
        Team::factory(2)->create();
        $this->signInAs(Role::TeamLead);

        $this->post(route('watch.rotations.plan'), ['start_on' => '2026-11-06', 'days_count' => 4]);
        $this->assertSame(['2026-11-06', '2026-11-09'], Rotation::orderBy('day')->get()->map(fn (Rotation $rotation) => $rotation->day->toDateString())->all());

        $this->post(route('watch.rotations.plan'), ['start_on' => '2026-11-06', 'days_count' => 4]);
        $this->assertSame(2, Rotation::count());
    }

    #[Covers('UC05', partial: true)]
    public function test_planning_is_reserved_to_those_who_manage_the_watch(): void
    {
        Team::factory()->create();
        $this->signInAs(Role::Agent);

        $this->post(route('watch.rotations.plan'), ['start_on' => '2026-11-02', 'days_count' => 3])->assertForbidden();

        $this->assertSame(0, Rotation::count());
    }

    #[Covers('UC05', 'RG10', partial: true)]
    public function test_the_team_on_duty_starts_its_handover_and_others_cannot(): void
    {
        $team = Team::factory()->create();
        $rotation = Rotation::factory()->create(['team_id' => $team->id, 'day' => today()]);

        $this->signInAs(Role::Agent, ['team_id' => Team::factory()->create()->id]);
        $this->post(route('watch.rotations.handover', $rotation))->assertForbidden();
        $this->assertSame(0, ShiftHandover::count());

        $agent = $this->signInAs(Role::Agent, ['team_id' => $team->id]);
        $this->post(route('watch.rotations.handover', $rotation))->assertRedirect(route('watch.board.index'));
        $this->post(route('watch.rotations.handover', $rotation));

        $this->assertSame(1, ShiftHandover::count());
        $this->assertNotNull(ShiftHandover::sole()->started_at);
        $this->assertSame($agent->id, Activity::where('event', 'watch.start_handover')->first()->causer_id);
        $this->get(route('watch.rotations.index'))->assertOk()->assertSee($team->name);
    }

    #[Covers('RG10', 'UC12', partial: true)]
    public function test_the_team_lead_creates_and_lifts_standing_instructions(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();

        $this->post(route('watch.instructions.store'), ['vessel_id' => $vessel->id, 'body' => 'Contacter le Chef avant toute sortie.'])->assertRedirect(route('watch.instructions.index'));
        $this->post(route('watch.instructions.store'), ['body' => 'Vérifier la liste des navires autorisés.']);

        $this->assertSame(2, StandingInstruction::count());
        $this->assertSame(today()->toDateString(), StandingInstruction::first()->started_on->toDateString());
        $this->assertSame('standing_instruction.created', $vessel->events()->sole()->type);

        $instruction = StandingInstruction::first();
        $this->post(route('watch.instructions.end', $instruction))->assertRedirect();
        $this->assertSame(today()->toDateString(), $instruction->fresh()->ended_on->toDateString());

        $this->get(route('watch.instructions.index'))->assertOk()->assertSee('Vérifier la liste des navires autorisés.');
    }

    #[Covers('RG10', partial: true)]
    public function test_agents_read_standing_instructions_but_cannot_write_them(): void
    {
        $this->signInAs(Role::Agent);
        StandingInstruction::factory()->create(['vessel_id' => null, 'body' => 'Consigne lisible']);

        $this->get(route('watch.instructions.index'))->assertOk()->assertSee('Consigne lisible');
        $this->post(route('watch.instructions.store'), ['body' => 'Interdit'])->assertForbidden();
        $this->post(route('watch.instructions.end', StandingInstruction::sole()))->assertForbidden();

        $this->assertNull(StandingInstruction::sole()->ended_on);
    }

    public function test_an_instruction_end_cannot_precede_its_start(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->post(route('watch.instructions.store'), ['body' => 'Incohérente', 'started_on' => '2026-11-10', 'ended_on' => '2026-11-01'])
            ->assertSessionHasErrors('ended_on');

        $this->assertSame(0, StandingInstruction::count());
    }

    public function test_user_without_team_still_sees_the_boards(): void
    {
        $this->signInAs(Role::Agent, ['team_id' => null]);
        User::factory()->create();

        $this->get(route('watch.rotations.index'))->assertOk();
        $this->get(route('watch.board.index'))->assertOk();
    }
}
