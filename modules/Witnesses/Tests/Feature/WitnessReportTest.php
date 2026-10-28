<?php

namespace Modules\Witnesses\Tests\Feature;

use App\Enums\OperationCategoryCode;
use App\Enums\WitnessReportStatus;
use App\Models\AuthorizedListPublication;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\Witness;
use App\Models\WitnessReport;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class WitnessReportTest extends TestCase
{
    use RefreshDatabase;

    private function record(array $overrides = [])
    {
        return $this->post(route('witnesses.store'), array_merge([
            'channel' => 'phone',
            'witness_name' => 'Yao Kouassi',
            'witness_category' => 'fisher',
            'latitude' => 4.5,
            'longitude' => -5.9,
            'vessels' => [['name' => 'Palangrier inconnu', 'is_fishing' => 1, 'description' => 'Palangres à l\'eau']],
        ], $overrides));
    }

    public function test_guests_are_redirected_and_users_without_permission_are_forbidden(): void
    {
        $this->get('/witnesses')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/witnesses')->assertForbidden();
    }

    #[Covers('UC15', partial: true)]
    public function test_an_agent_records_a_report_with_its_witness_and_up_to_two_vessels(): void
    {
        $actor = $this->signInAs(Role::Agent);

        $this->record(['vessels' => [
            ['name' => 'Navire un', 'is_fishing' => 1],
            ['name' => 'Navire deux', 'is_transshipping' => 1],
            ['name' => 'Navire trois'],
        ]])->assertSessionHasErrors('vessels');

        $this->record(['vessels' => [['name' => 'Navire un', 'is_fishing' => 1], ['name' => 'Navire deux', 'is_transshipping' => 1]]])->assertRedirect();

        $report = WitnessReport::sole();
        $this->assertSame(WitnessReportStatus::Received, $report->status);
        $this->assertSame($actor->id, $report->entered_by);
        $this->assertSame('Yao Kouassi', $report->witness->name);
        $this->assertSame([1, 2], $report->reportedVessels()->orderBy('rank')->pluck('rank')->all());
        $this->assertSame($actor->id, Activity::where('event', 'witnesses.record')->sole()->causer_id);
    }

    #[Covers('UC15', partial: true)]
    public function test_a_reported_vessel_that_matches_the_register_gets_a_vessel_event(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create(['registration_number' => 'AN-5151']);

        $this->record(['vessels' => [['name' => 'Autre nom', 'registration' => 'AN-5151']]])->assertRedirect();

        $this->assertSame($vessel->id, WitnessReport::sole()->reportedVessels()->sole()->vessel_id);
        $this->assertSame($vessel->id, VesselEvent::sole()->vessel_id);
    }

    #[Covers('UC15', partial: true)]
    #[Assumes('H-6-8')]
    public function test_the_team_lead_qualifies_investigates_and_forwards_a_report(): void
    {
        $this->signInAs(Role::Agent);
        $this->record()->assertRedirect();
        $report = WitnessReport::sole();

        $this->put(route('witnesses.usability.save', $report), ['is_usable' => 1])->assertForbidden();

        $teamLead = $this->signInAs(Role::TeamLead);
        $this->put(route('witnesses.usability.save', $report), ['is_usable' => 1, 'interview_report' => 'Entretien concordant.'])->assertRedirect();
        $this->put(route('witnesses.investigation.save', $report), ['elements' => ['themis', 'vms_extract'], 'notes' => 'Trajectoire suspecte.'])->assertRedirect();

        $this->assertSame(WitnessReportStatus::Verified, $report->fresh()->status);
        $this->assertSame(['themis', 'vms_extract'], $report->investigation->elements);

        $this->post(route('witnesses.decision.store', $report), ['decision' => 'forward_to_instruction'])->assertRedirect();

        $this->assertSame(WitnessReportStatus::Forwarded, $report->fresh()->status);
        $this->assertSame($teamLead->id, $report->decisions()->sole()->decided_by);
        $this->post(route('witnesses.decision.store', $report), ['decision' => 'dismissal'])->assertSessionHasErrors('decision');
        $this->get(route('witnesses.show', $report))->assertOk()->assertSee(route('infractions.create', ['witness_report' => $report->id, 'vessel' => null, 'operation' => null]), false);
    }

    #[Covers('UC15', partial: true)]
    public function test_a_report_can_be_dismissed(): void
    {
        $this->signInAs(Role::TeamLead);
        $this->record()->assertRedirect();
        $report = WitnessReport::sole();

        $this->post(route('witnesses.decision.store', $report), ['decision' => 'dismissal'])->assertRedirect();

        $this->assertSame(WitnessReportStatus::Closed, $report->fresh()->status);
    }

    #[Covers('UC15', partial: true)]
    #[Assumes('H-6-8')]
    public function test_only_an_inn_operation_can_be_linked_to_a_report(): void
    {
        $this->signInAs(Role::TeamLead);
        $this->record()->assertRedirect();
        $report = WitnessReport::sole();
        $inn = Operation::factory()->create(['category_id' => OperationCategory::factory()->create(['code' => OperationCategoryCode::Inn])->id]);
        $vms = Operation::factory()->create(['category_id' => OperationCategory::factory()->create(['code' => OperationCategoryCode::Vms])->id]);

        $this->put(route('witnesses.operation.save', $report), ['operation_id' => $vms->id])->assertSessionHasErrors('operation_id');
        $this->assertNull($report->fresh()->operation_id);

        $this->put(route('witnesses.operation.save', $report), ['operation_id' => $inn->id])->assertRedirect();
        $this->assertSame($inn->id, $report->fresh()->operation_id);
    }

    #[Covers('UC15', partial: true)]
    public function test_reports_can_be_filtered_by_status(): void
    {
        $this->signInAs(Role::Agent);
        WitnessReport::factory()->create(['witness_id' => Witness::factory()->create(['name' => 'Témoin classé'])->id, 'status' => WitnessReportStatus::Closed]);
        WitnessReport::factory()->create(['witness_id' => Witness::factory()->create(['name' => 'Témoin récent'])->id, 'status' => WitnessReportStatus::Received]);

        $this->get('/witnesses?status=closed')->assertOk()->assertSee('Témoin classé')->assertDontSee('Témoin récent');
    }

    public function test_the_recording_form_renders(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('witnesses.create'))->assertOk();
    }

    public function test_the_team_lead_records_the_distribution_of_the_authorized_list(): void
    {
        $this->signInAs(Role::TeamLead);
        $publication = AuthorizedListPublication::factory()->create();

        $this->post(route('witnesses.distributions.store'), ['authorized_list_publication_id' => $publication->id, 'recipient' => 'capitainerie@example.ci'])->assertRedirect();

        $this->assertSame('capitainerie@example.ci', $publication->fresh()->distributions()->sole()->recipient);
        $this->get(route('witnesses.distributions.index'))->assertOk()->assertSee('capitainerie@example.ci');

        $this->signInAs(Role::Agent);
        $this->get(route('witnesses.distributions.index'))->assertForbidden();
    }
}
