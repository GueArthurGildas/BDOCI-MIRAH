<?php

namespace Modules\Licenses\Tests\Feature;

use App\Enums\CurrentLicenseStatus;
use App\Models\License;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC02', partial: true)]
class RecordLicenseTest extends TestCase
{
    use RefreshDatabase;

    private function payload(Vessel $vessel, array $overrides = []): array
    {
        return array_merge([
            'vessel_id' => $vessel->id,
            'number' => 'LIC-2026-0100',
            'segment' => 'industrial',
            'issued_on' => today()->subMonth()->toDateString(),
            'expires_on' => today()->addMonths(11)->toDateString(),
        ], $overrides);
    }

    public function test_a_team_lead_records_a_license_that_updates_the_journal_the_state_and_the_audit_log(): void
    {
        $actor = $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();

        $this->post(route('licenses.store'), $this->payload($vessel))->assertRedirect(route('licenses.show', License::sole()));

        $license = License::sole();
        $this->assertSame($vessel->id, $license->vessel_id);

        $event = VesselEvent::sole();
        $this->assertSame('license.recorded', $event->type);
        $this->assertSame('licenses', $event->module->code);
        $this->assertSame($license->id, $event->object_id);
        $this->assertSame($actor->id, $event->user_id);

        $state = $vessel->fresh()->currentState;
        $this->assertSame(CurrentLicenseStatus::Active, $state->license_status);
        $this->assertSame($license->expires_on->toDateString(), $state->license_expires_on->toDateString());

        $this->assertSame($actor->id, Activity::where('event', 'licenses.record')->sole()->causer_id);
    }

    public function test_the_event_summary_is_translated_for_each_reader(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        $this->post(route('licenses.store'), $this->payload($vessel));
        $event = VesselEvent::sole();

        app()->setLocale('fr');
        $french = $event->summary();
        app()->setLocale('en');

        $this->assertStringContainsString('enregistrée', $french);
        $this->assertStringContainsString('recorded', $event->summary());
    }

    public function test_agents_cannot_record_licenses(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->get(route('licenses.create'))->assertForbidden();
        $this->post(route('licenses.store'), $this->payload($vessel))->assertForbidden();

        $this->assertSame(0, License::count());
        $this->assertSame(0, VesselEvent::count());
    }

    public function test_the_form_lists_the_vessels_and_the_button_is_only_shown_to_managers(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create(['name' => 'ALBACORA']);

        $this->get(route('licenses.create'))->assertOk()->assertSee('ALBACORA');
        $this->get(route('licenses.index'))->assertSee(route('licenses.create'));

        $this->signInAs(Role::Agent);
        $this->get(route('licenses.index'))->assertOk()->assertDontSee(route('licenses.create'));
    }

    public function test_the_input_is_validated(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();
        License::factory()->create(['number' => 'LIC-TAKEN']);

        $this->post(route('licenses.store'), $this->payload($vessel, ['number' => 'LIC-TAKEN']))->assertSessionHasErrors('number');
        $this->post(route('licenses.store'), $this->payload($vessel, ['expires_on' => today()->subYears(2)->toDateString()]))->assertSessionHasErrors('expires_on');
        $this->post(route('licenses.store'), $this->payload($vessel, ['vessel_id' => 999999]))->assertSessionHasErrors('vessel_id');
        $this->post(route('licenses.store'), $this->payload($vessel, ['segment' => 'nonsense']))->assertSessionHasErrors('segment');

        $this->assertSame(1, License::count());
        $this->assertSame(0, VesselEvent::count());
    }
}
