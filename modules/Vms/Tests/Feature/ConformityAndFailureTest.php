<?php

namespace Modules\Vms\Tests\Feature;

use App\Enums\AssessmentResult;
use App\Enums\BeaconRequestStatus;
use App\Enums\VmsRequestStatus;
use App\Models\ApprovedInstaller;
use App\Models\Beacon;
use App\Models\BeaconAssignment;
use App\Models\CessationOrder;
use App\Models\CompatibilityAssessment;
use App\Models\ErsDeclaration;
use App\Models\ManualPositionReport;
use App\Models\NewBeaconRequest;
use App\Models\Species;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\VmsAssessmentRequest;
use App\Models\VmsCutoffAuthorization;
use App\Models\VmsFailure;
use App\Models\WorkingOrderAttestation;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class ConformityAndFailureTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC04')]
    #[Assumes('H-3b-8')]
    public function test_case_one_an_existing_beacon_is_assessed_for_compatibility(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post(route('vms.requests.assessment'), ['vessel_id' => $vessel->id])->assertRedirect();
        $request = VmsAssessmentRequest::first();
        $this->assertSame(VmsRequestStatus::Requested, $request->status);

        $this->post(route('vms.requests.assess', $request), ['result' => 'compatible'])->assertRedirect();

        $this->assertSame(VmsRequestStatus::Assessed, $request->fresh()->status);
        $this->assertSame(AssessmentResult::Compatible, CompatibilityAssessment::first()->result);
        $this->assertTrue(VesselEvent::where('vessel_id', $vessel->id)->where('type', 'assessment.recorded')->exists());
    }

    #[Covers('UC04', 'RG03', 'RG05')]
    #[Assumes('H-3b-8')]
    public function test_case_two_a_new_beacon_is_installed_by_an_approved_installer_and_assigned(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        $installer = ApprovedInstaller::factory()->create();
        $beacon = Beacon::factory()->create();

        $this->post(route('vms.requests.new-beacon'), ['vessel_id' => $vessel->id])->assertRedirect();
        $request = NewBeaconRequest::first();
        $this->post(route('vms.requests.install', $request), ['installer_id' => $installer->id, 'beacon_id' => $beacon->id, 'installed_on' => '2026-10-01'])->assertRedirect();

        $this->assertSame(BeaconRequestStatus::Installed, $request->fresh()->status);
        $this->assertSame($installer->id, $request->installations()->first()->installer_id);
        $this->assertSame($beacon->id, BeaconAssignment::active()->where('vessel_id', $vessel->id)->value('beacon_id'));
    }

    #[Covers('UC04')]
    public function test_installers_are_approved_by_managers_only(): void
    {
        $this->signInAs(Role::Agent);
        $this->post(route('vms.requests.installers.store'), ['name' => 'CLS'])->assertForbidden();

        $this->signInAs(Role::Director);
        $this->post(route('vms.requests.installers.store'), ['name' => 'CLS', 'approved_until' => '2027-01-01'])->assertRedirect();

        $this->assertSame('CLS', ApprovedInstaller::first()->legalEntity->name);
        $this->get(route('vms.requests.index'))->assertOk()->assertSee('CLS');
    }

    #[Covers('UC07')]
    #[Assumes('H-3b-6')]
    public function test_a_failure_gets_the_deadline_and_report_frequency_of_its_category(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post(route('vms.failures.store'), ['vessel_id' => $vessel->id, 'category' => 'tuna_vessel', 'started_at' => '2026-10-01 08:00:00'])->assertRedirect();

        $failure = VmsFailure::first();
        $this->assertSame([30, 6], [$failure->repair_deadline_days, $failure->report_frequency_h]);
        $this->assertSame('2026-10-31', $failure->repairDeadlineAt()->toDateString());
        $this->assertTrue(VesselEvent::where('vessel_id', $vessel->id)->where('type', 'failure.declared')->exists());
    }

    #[Covers('UC07')]
    public function test_a_failure_past_its_deadline_is_overdue_and_a_repaired_one_is_not(): void
    {
        $overdue = VmsFailure::factory()->create(['started_at' => now()->subDays(12), 'repair_deadline_days' => 10]);
        $recent = VmsFailure::factory()->create(['started_at' => now()->subDays(2), 'repair_deadline_days' => 10]);
        $repaired = VmsFailure::factory()->create(['started_at' => now()->subDays(30), 'repair_deadline_days' => 10, 'ended_at' => now()->subDays(25)]);

        $this->assertSame([true, false, false], [$overdue->isOverdue(), $recent->isOverdue(), $repaired->isOverdue()]);
        $this->assertSame(2, VmsFailure::ongoing()->count());
    }

    #[Covers('UC07')]
    public function test_manual_position_reports_are_recorded_during_a_failure(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $failure = VmsFailure::factory()->create();

        $this->post(route('vms.failures.report', $failure), ['latitude' => 4.75, 'longitude' => -6.6, 'received_at' => '2026-10-02 12:00:00'])->assertRedirect();
        $this->post(route('vms.failures.report', $failure), ['latitude' => 95, 'longitude' => -6.6])->assertSessionHasErrors('latitude');

        $this->assertSame(1, ManualPositionReport::count());
        $this->assertSame($agent->id, ManualPositionReport::first()->entered_by);
        $this->get(route('vms.failures.show', $failure))->assertOk()->assertSee('4.75');
    }

    #[Covers('UC07')]
    public function test_the_working_order_attestation_closes_the_failure(): void
    {
        $this->signInAs(Role::Agent);
        $failure = VmsFailure::factory()->create(['started_at' => now()->subDays(4)]);

        $this->post(route('vms.failures.attest', $failure), ['attested_on' => today()->toDateString()])->assertRedirect();

        $this->assertNotNull($failure->fresh()->ended_at);
        $this->assertSame(1, WorkingOrderAttestation::where('vms_failure_id', $failure->id)->count());
    }

    #[Covers('UC07')]
    public function test_the_cessation_order_is_given_by_a_manager_with_its_consequences(): void
    {
        $this->signInAs(Role::Agent);
        $failure = VmsFailure::factory()->create();
        $this->post(route('vms.failures.order', $failure), ['requires_port_return' => 1])->assertForbidden();

        $lead = $this->signInAs(Role::TeamLead);
        $this->post(route('vms.failures.order', $failure), ['includes_suspension' => 1, 'requires_port_return' => 1])->assertRedirect();

        $order = CessationOrder::first();
        $this->assertSame([true, true, $lead->id], [$order->includes_suspension, $order->requires_port_return, $order->given_by]);
        $this->assertTrue(VesselEvent::where('vessel_id', $failure->vessel_id)->where('type', 'failure.cessation_ordered')->exists());
    }

    #[Covers('UC07', 'RG04', partial: true)]
    public function test_technical_stops_are_authorized_for_a_period(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        $this->post(route('vms.cutoffs.store'), ['vessel_id' => $vessel->id, 'started_at' => '2026-10-01 08:00:00', 'ended_at' => '2026-10-01 07:00:00'])->assertSessionHasErrors('ended_at');
        $this->post(route('vms.cutoffs.store'), ['vessel_id' => $vessel->id, 'started_at' => '2026-10-01 08:00:00', 'ended_at' => '2026-10-03 08:00:00', 'reason' => 'Port stop'])->assertRedirect();

        $this->assertSame(1, VmsCutoffAuthorization::count());
    }

    #[Covers('UC07')]
    public function test_failures_cannot_be_declared_without_the_create_permission(): void
    {
        $this->signInWithPermissions(Permission::VmsView);
        $vessel = Vessel::factory()->create();

        $this->get(route('vms.failures.index'))->assertOk();
        $this->post(route('vms.failures.store'), ['vessel_id' => $vessel->id, 'category' => 'ivorian'])->assertForbidden();

        $this->assertSame(0, VmsFailure::count());
    }

    public function test_an_ers_declaration_is_recorded_with_its_catch_lines(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        $species = Species::factory()->create();

        $this->post(route('vms.ers.store'), ['vessel_id' => $vessel->id, 'lines' => [['species_id' => $species->id, 'live_weight_kg' => 1250.5, 'fao_zone' => '34.3.5']]])->assertRedirect();

        $declaration = ErsDeclaration::first();
        $this->assertSame('manual_entry', $declaration->source->value);
        $this->assertEquals(1250.5, $declaration->lines()->first()->live_weight_kg);
        $this->get(route('vms.ers.index'))->assertOk()->assertSee($vessel->name);
        $this->get(route('vms.ers.show', $declaration))->assertOk()->assertSee($species->name);
        $this->post(route('vms.ers.store'), ['vessel_id' => $vessel->id, 'lines' => []])->assertSessionHasErrors('lines');
    }
}
