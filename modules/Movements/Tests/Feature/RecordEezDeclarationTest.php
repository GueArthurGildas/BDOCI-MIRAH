<?php

namespace Modules\Movements\Tests\Feature;

use App\Enums\EezCheckResult;
use App\Enums\EezCheckType;
use App\Enums\EezDeclarationStatus;
use App\Enums\EezDeclarationType;
use App\Enums\EntryExitAlertStatus;
use App\Models\EezDeclaration;
use App\Models\EntryExitAlert;
use App\Models\EntryExitLink;
use App\Models\License;
use App\Models\Position;
use App\Models\Species;
use App\Models\TrackingDevice;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Movements\Actions\RecordEezDeclaration;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC10')]
class RecordEezDeclarationTest extends TestCase
{
    use RefreshDatabase;

    private function foreignVessel(array $attributes = []): Vessel
    {
        return Vessel::factory()->create(['flag' => 'ES', 'name' => 'ALBACORA UNO', 'imo_number' => 'IMO9000001', 'call_sign' => 'EAXY1', ...$attributes]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'entry',
            'received_at' => now()->subHour()->format('Y-m-d H:i'),
            'declared_name' => 'ALBACORA UNO',
        ], $overrides);
    }

    public function test_an_agent_records_an_entry_that_updates_declaration_alert_journal_and_audit_log(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = $this->foreignVessel();
        $species = Species::factory()->create();

        $this->post(route('movements.eez.store'), $this->payload([
            'vessel_id' => $vessel->id,
            'reason' => 'eez_fishing',
            'catches' => [['species_id' => $species->id, 'weight_kg' => 12000], ['species_id' => '', 'weight_kg' => '']],
        ]))->assertRedirect(route('movements.eez.show', EezDeclaration::sole()));

        $declaration = EezDeclaration::sole();
        $this->assertSame($agent->id, $declaration->entered_by);
        $this->assertSame('manual', $declaration->entry_origin->value);
        $this->assertSame(EezDeclarationStatus::Verified, $declaration->status);
        $this->assertSame(1, $declaration->catches()->count());
        $this->assertCount(count(EezCheckType::cases()), $declaration->checks);

        $alert = EntryExitAlert::sole();
        $this->assertSame($vessel->id, $alert->vessel_id);
        $this->assertSame($declaration->id, $alert->eez_declaration_id);
        $this->assertSame(EezDeclarationType::Entry, $alert->direction);
        $this->assertSame(EntryExitAlertStatus::Open, $alert->status);

        $event = VesselEvent::sole();
        $this->assertSame('eez_declaration.recorded', $event->type);
        $this->assertSame('movements', $event->module->code);
        $this->assertSame($agent->id, $event->user_id);

        $this->assertSame($agent->id, Activity::where('event', 'movements.record_eez_declaration')->sole()->causer_id);
    }

    #[Assumes('H-4b-1')]
    public function test_the_vessel_is_identified_from_the_declared_identity_by_imo_then_call_sign_then_name(): void
    {
        $this->signInAs(Role::Agent);
        $byImo = $this->foreignVessel();
        $byCallSign = $this->foreignVessel(['name' => 'OTHER ONE', 'imo_number' => 'IMO9000002', 'call_sign' => 'EAXY2']);
        $byName = $this->foreignVessel(['name' => 'THIRD ONE', 'imo_number' => 'IMO9000003', 'call_sign' => 'EAXY3']);

        $this->post(route('movements.eez.store'), $this->payload(['declared_name' => 'WRONG', 'declared_imo' => 'imo9000001']));
        $this->post(route('movements.eez.store'), $this->payload(['declared_name' => 'WRONG', 'declared_ircs' => 'EAXY2']));
        $this->post(route('movements.eez.store'), $this->payload(['declared_name' => 'third one']));

        $this->assertSame([$byImo->id, $byCallSign->id, $byName->id], EezDeclaration::orderBy('id')->pluck('vessel_id')->all());
    }

    public function test_an_unidentified_vessel_is_kept_without_alert_or_journal_entry(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('movements.eez.store'), $this->payload(['declared_name' => 'UNKNOWN STAR']))->assertRedirect();

        $declaration = EezDeclaration::sole();
        $this->assertNull($declaration->vessel_id);
        $this->assertSame(0, EntryExitAlert::count());
        $this->assertSame(0, VesselEvent::count());
        $this->assertSame(EezCheckResult::NotApplicable, $declaration->checks->firstWhere('check_type', EezCheckType::Consistency)->result);
    }

    #[Assumes('H-4b-4')]
    public function test_checks_reflect_beacon_license_and_declared_identity(): void
    {
        $this->signInAs(Role::Agent);
        $compliant = $this->foreignVessel();
        TrackingDevice::factory()->for($compliant)->create();
        Position::factory()->create(['vessel_id' => $compliant->id, 'recorded_at' => now()->subMinutes(10)]);
        License::factory()->for($compliant)->create();
        $other = $this->foreignVessel(['name' => 'SECOND', 'imo_number' => 'IMO9000009', 'call_sign' => 'EAXY9']);

        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $compliant->id]));
        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $other->id, 'declared_name' => 'ALBACORA UNO']));

        [$first, $second] = EezDeclaration::orderBy('id')->get();
        $result = fn (EezDeclaration $declaration, EezCheckType $type) => $declaration->checks->firstWhere('check_type', $type);

        $this->assertSame(EezCheckResult::Ok, $result($first, EezCheckType::Vms)->result);
        $this->assertSame(EezCheckResult::Ok, $result($first, EezCheckType::License)->result);
        $this->assertSame(EezCheckResult::Ok, $result($first, EezCheckType::Consistency)->result);
        $this->assertSame(EezCheckResult::Ko, $result($second, EezCheckType::Vms)->result);
        $this->assertSame('no_beacon', $result($second, EezCheckType::Vms)->comment);
        $this->assertSame(EezCheckResult::Ko, $result($second, EezCheckType::License)->result);
        $this->assertSame(EezCheckResult::Ko, $result($second, EezCheckType::Consistency)->result);
        $this->assertSame(EezCheckResult::NotApplicable, $result($first, EezCheckType::InnLists)->result);
    }

    #[Assumes('H-4b-4')]
    public function test_a_transit_to_a_port_checks_that_the_destination_is_designated(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = $this->foreignVessel();

        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $vessel->id, 'reason' => 'ivorian_port_transit', 'destination' => 'Port de San Pedro']));
        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $vessel->id, 'reason' => 'ivorian_port_transit', 'destination' => 'Tabou']));

        [$designated, $other] = EezDeclaration::orderBy('id')->get();
        $this->assertSame(EezCheckResult::Ok, $designated->checks->firstWhere('check_type', EezCheckType::DesignatedPort)->result);
        $this->assertSame(EezCheckResult::Ko, $other->checks->firstWhere('check_type', EezCheckType::DesignatedPort)->result);
    }

    #[Assumes('H-4b-2', 'H-4b-3')]
    public function test_an_exit_is_paired_with_the_latest_unpaired_entry_and_flags_a_variance_beyond_the_tolerance(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = $this->foreignVessel();
        $species = Species::factory()->create();
        $entry = EezDeclaration::factory()->for($vessel)->create(['received_at' => now()->subDays(3)]);
        $entry->catches()->create(['species_id' => $species->id, 'weight_kg' => 10000]);

        $this->post(route('movements.eez.store'), $this->payload([
            'vessel_id' => $vessel->id,
            'type' => 'exit',
            'catches' => [['species_id' => $species->id, 'weight_kg' => 15000]],
        ]));

        $exit = EezDeclaration::where('type', 'exit')->sole();
        $link = EntryExitLink::sole();
        $this->assertSame($entry->id, $link->entry_declaration_id);
        $this->assertSame($exit->id, $link->exit_declaration_id);
        $this->assertSame(5000.0, $link->variance_kg);
        $this->assertSame('unexplained', $link->explanation->value);
        $this->assertSame(EezDeclarationStatus::Variance, $exit->status);
    }

    #[Assumes('H-4b-3')]
    public function test_a_variance_within_the_tolerance_is_not_flagged(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = $this->foreignVessel();
        $species = Species::factory()->create();
        $entry = EezDeclaration::factory()->for($vessel)->create(['received_at' => now()->subDays(3)]);
        $entry->catches()->create(['species_id' => $species->id, 'weight_kg' => 10000]);

        $this->post(route('movements.eez.store'), $this->payload([
            'vessel_id' => $vessel->id,
            'type' => 'exit',
            'catches' => [['species_id' => $species->id, 'weight_kg' => 10500]],
        ]));

        $this->assertNull(EntryExitLink::sole()->explanation);
        $this->assertSame(EezDeclarationStatus::Verified, EezDeclaration::where('type', 'exit')->sole()->status);
    }

    #[Assumes('H-4b-2')]
    public function test_an_entry_is_paired_only_once(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = $this->foreignVessel();
        EezDeclaration::factory()->for($vessel)->create(['received_at' => now()->subDays(3)]);

        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $vessel->id, 'type' => 'exit']));
        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $vessel->id, 'type' => 'exit']));

        $this->assertSame(1, EntryExitLink::count());
    }

    public function test_an_exit_without_entry_is_kept_unpaired(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = $this->foreignVessel();

        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $vessel->id, 'type' => 'exit']))->assertRedirect();

        $this->assertSame(1, EezDeclaration::count());
        $this->assertSame(0, EntryExitLink::count());
    }

    public function test_the_event_summary_is_translated_for_each_reader(): void
    {
        $this->signInAs(Role::Agent);
        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $this->foreignVessel()->id]));
        $event = VesselEvent::sole();

        app()->setLocale('fr');
        $french = $event->summary();
        app()->setLocale('en');

        $this->assertStringContainsString('Déclaration ZEE', $french);
        $this->assertStringContainsString('EEZ', $event->summary());
    }

    public function test_the_administrator_cannot_record_declarations(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('movements.eez.create'))->assertForbidden();
        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $this->foreignVessel()->id]))->assertForbidden();

        $this->assertSame(0, EezDeclaration::count());
        $this->assertSame(0, VesselEvent::count());
    }

    public function test_the_input_is_validated_and_nothing_is_written_on_failure(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = $this->foreignVessel();

        $this->post(route('movements.eez.store'), $this->payload(['type' => 'nonsense']))->assertSessionHasErrors('type');
        $this->post(route('movements.eez.store'), $this->payload(['received_at' => now()->addDay()->format('Y-m-d H:i')]))->assertSessionHasErrors('received_at');
        $this->post(route('movements.eez.store'), ['type' => 'entry', 'received_at' => now()->subHour()->format('Y-m-d H:i')])->assertSessionHasErrors('declared_name');
        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $vessel->id, 'latitude' => 120]))->assertSessionHasErrors('latitude');
        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $vessel->id, 'catches' => [['species_id' => 999999, 'weight_kg' => 10]]]))->assertSessionHasErrors('catches.0.species_id');
        $this->post(route('movements.eez.store'), $this->payload(['vessel_id' => $vessel->id, 'catches' => [['species_id' => Species::factory()->create()->id, 'weight_kg' => -5]]]))->assertSessionHasErrors('catches.0.weight_kg');

        $this->assertSame(0, EezDeclaration::count());
        $this->assertSame(0, VesselEvent::count());
    }

    public function test_the_action_rolls_back_everything_when_it_fails(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = $this->foreignVessel();

        try {
            app(ActionRunner::class)->run(RecordEezDeclaration::class, $agent, $this->payload(['vessel_id' => $vessel->id, 'catches' => [['species_id' => 999999, 'weight_kg' => 10]]]));
            $this->fail('The action should have failed.');
        } catch (\Throwable $exception) {
            $this->assertNotInstanceOf(AuthorizationException::class, $exception);
        }

        $this->assertSame(0, EezDeclaration::count());
        $this->assertSame(0, EntryExitAlert::count());
        $this->assertSame(0, VesselEvent::count());
    }
}
