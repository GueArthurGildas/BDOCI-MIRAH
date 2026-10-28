<?php

namespace Modules\Licenses\Tests\Feature;

use App\Enums\FnpRegistration;
use App\Models\ExternalAuthorization;
use App\Models\FnpStatus;
use App\Models\Institution;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\VmsCompatibilityCertificate;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC03', partial: true)]
class VesselComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_fnp_status_is_registered_then_deregistered_with_a_reason(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();

        $this->post(route('licenses.vessels.fnp', $vessel), ['status' => 'registered'])->assertRedirect();
        $this->assertSame(FnpRegistration::Registered, FnpStatus::sole()->status);

        $this->post(route('licenses.vessels.fnp', $vessel), ['status' => 'deregistered'])->assertSessionHasErrors('deregistration_reason');
        $this->post(route('licenses.vessels.fnp', $vessel), ['status' => 'deregistered', 'deregistration_reason' => 'flag_change'])->assertRedirect();

        $fnp = FnpStatus::sole();
        $this->assertSame(FnpRegistration::Deregistered, $fnp->status);
        $this->assertSame('flag_change', $fnp->deregistration_reason->value);
        $this->assertSame(2, VesselEvent::where('type', 'fnp.status_set')->count());

        $this->post(route('licenses.vessels.fnp', $vessel), ['status' => 'registered']);
        $this->assertNull(FnpStatus::sole()->deregistration_reason);
    }

    public function test_certificates_and_external_authorizations_are_recorded_with_their_validity(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $institution = Institution::factory()->create(['name' => 'ICCAT']);

        $this->post(route('licenses.vessels.certificates', $vessel), ['issued_on' => today()->subMonths(2)->toDateString(), 'expires_on' => today()->addMonths(10)->toDateString()])->assertRedirect();
        $this->post(route('licenses.vessels.certificates', $vessel), ['issued_on' => today()->toDateString(), 'expires_on' => today()->subDay()->toDateString()])->assertSessionHasErrors('expires_on');
        $this->post(route('licenses.vessels.authorizations', $vessel), ['institution_id' => $institution->id, 'number' => 'AUT-1', 'valid_to' => today()->addYear()->toDateString()])->assertRedirect();

        $this->assertSame(1, VmsCompatibilityCertificate::count());
        $this->assertSame('AUT-1', ExternalAuthorization::sole()->number);
        $this->get(route('licenses.vessels.show', $vessel))->assertOk()->assertSee('ICCAT')->assertSee('AUT-1');
    }

    public function test_agents_can_read_the_page_but_not_write(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->get(route('licenses.vessels.show', $vessel))->assertOk();
        $this->post(route('licenses.vessels.fnp', $vessel), ['status' => 'registered'])->assertForbidden();
        $this->assertSame(0, FnpStatus::count());
    }
}
