<?php

namespace Modules\Vessels\Tests\Feature;

use App\Enums\IdentifierType;
use App\Enums\MatchingStatus;
use App\Enums\SourceSystem;
use App\Models\ExternalList;
use App\Models\Institution;
use App\Models\LegalEntity;
use App\Models\SourcePriority;
use App\Models\Vessel;
use App\Models\VesselFieldProvenance;
use App\Models\VesselSource;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Vessels\Actions\IngestVesselSource;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class VesselRegistryTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('RG02', partial: true)]
    public function test_changing_a_legacy_identifier_closes_the_previous_one_and_keeps_the_history(): void
    {
        $vessel = Vessel::factory()->create(['imo_number' => '9500001']);
        $vessel->update(['imo_number' => '9500002']);

        $this->assertSame('9500002', $vessel->identifierValue(IdentifierType::Imo));
        $this->assertSame(2, $vessel->identifiers()->where('type', 'imo')->count());
        $this->assertSame(1, $vessel->identifiers()->where('type', 'imo')->whereNull('valid_to')->count());
    }

    #[Covers('RG02', partial: true)]
    public function test_an_active_identifier_is_unique_across_vessels(): void
    {
        $this->signInAs(Role::TeamLead);
        Vessel::factory()->create()->identifiers()->create(['type' => 'mmsi', 'value' => '600000001']);
        $other = Vessel::factory()->create();

        $this->post(route('vessels.identifiers.store', $other), ['type' => 'mmsi', 'value' => '600000001'])->assertSessionHasErrors('value');
        $this->post(route('vessels.identifiers.store', $other), ['type' => 'mmsi', 'value' => '600000002'])->assertRedirect(route('vessels.show', $other));

        $this->assertSame('600000002', $other->fresh()->identifierValue(IdentifierType::Mmsi));
        $this->assertSame(1, Activity::where('event', 'vessels.add_identifier')->count());
    }

    public function test_renaming_a_vessel_keeps_the_former_name_and_flag(): void
    {
        $vessel = Vessel::factory()->create(['name' => 'ANCIEN NOM', 'flag' => 'GH']);
        $vessel->update(['name' => 'NOUVEAU NOM', 'flag' => 'CI']);

        $entry = $vessel->history()->sole();
        $this->assertSame('ANCIEN NOM', $entry->former_name);
        $this->assertSame('GH', $entry->formerFlagCountry->iso2_code);
    }

    public function test_the_owner_is_a_dated_relation_and_a_new_owner_closes_the_previous_one(): void
    {
        $vessel = Vessel::factory()->create(['owner' => 'Armement Un']);
        $vessel->update(['owner' => 'Armement Deux']);

        $this->assertSame('Armement Deux', $vessel->fresh()->owner);
        $this->assertSame(2, $vessel->actors()->where('role', 'owner')->count());
        $this->assertSame(1, $vessel->actors()->where('role', 'owner')->whereNull('ended_on')->count());
    }

    public function test_actors_are_added_through_an_action_with_a_journal_entry(): void
    {
        $actor = $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $entity = LegalEntity::factory()->create(['name' => 'Agence Test']);

        $this->post(route('vessels.actors.store', $vessel), ['role' => 'local_agent', 'legal_entity_id' => $entity->id, 'started_on' => '2026-01-10'])
            ->assertRedirect(route('vessels.show', $vessel));
        $this->post(route('vessels.actors.store', $vessel), ['role' => 'captain'])->assertSessionHasErrors(['legal_entity_id', 'person_id']);

        $this->assertSame('Agence Test', $vessel->actors()->where('role', 'local_agent')->sole()->holderName());
        $this->assertSame($actor->id, Activity::where('event', 'vessels.add_actor')->sole()->causer_id);
    }

    public function test_a_vessel_can_be_listed_on_an_external_list(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $list = ExternalList::factory()->for(Institution::factory())->create(['label' => 'Registre ICCAT']);

        $this->post(route('vessels.external-lists.store', $vessel), ['external_list_id' => $list->id, 'listed_from' => '2026-02-01'])
            ->assertRedirect(route('vessels.show', $vessel));

        $this->assertSame('Registre ICCAT', $vessel->externalLists()->sole()->label);
        $this->get(route('vessels.show', $vessel))->assertOk()->assertSee('Registre ICCAT');
    }

    #[Assumes('H-2-1')]
    #[Assumes('H-2-6')]
    public function test_a_lower_priority_source_does_not_overwrite_a_field_set_by_a_higher_one(): void
    {
        $actor = $this->signInAs(Role::TeamLead);
        $runner = app(ActionRunner::class);
        $base = ['external_id' => 'X-1', 'name' => 'PRIORITY ONE', 'flag' => 'FR', 'identifiers' => ['imo' => '9600001']];

        $runner->run(IngestVesselSource::class, $actor, [...$base, 'source' => 'themis', 'attributes' => ['length_m' => 40]]);
        $runner->run(IngestVesselSource::class, $actor, [...$base, 'source' => 'mrep', 'external_id' => 'M-1', 'attributes' => ['length_m' => 99, 'beam_m' => 9]]);

        $vessel = Vessel::sole();
        $this->assertSame(40.0, $vessel->length_m);
        $this->assertSame(9.0, $vessel->beam_m);
        $this->assertSame(SourceSystem::Themis, VesselFieldProvenance::where('field', 'length_m')->sole()->source);
        $this->assertSame(SourceSystem::Mrep, VesselFieldProvenance::where('field', 'beam_m')->sole()->source);

        SourcePriority::create(['field' => 'length_m', 'source' => 'mrep', 'rank' => 0]);
        $runner->run(IngestVesselSource::class, $actor, [...$base, 'source' => 'mrep', 'external_id' => 'M-1', 'attributes' => ['length_m' => 99]]);

        $this->assertSame(99.0, $vessel->fresh()->length_m);
    }

    public function test_the_vessel_page_shows_identifiers_actors_and_sources(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create(['name' => 'PAGE TEST', 'owner' => 'Propriétaire Page']);
        VesselSource::factory()->create(['vessel_id' => $vessel->id, 'source' => 'themis', 'external_id' => 'TH-42', 'matching_status' => MatchingStatus::Matched]);

        $this->get(route('vessels.show', $vessel))->assertOk()->assertSee('Propriétaire Page')->assertSee('TH-42')->assertSee($vessel->imo_number);
    }

    #[Covers('UC01', partial: true)]
    public function test_the_ingest_form_creates_a_vessel_and_reports_a_duplicate(): void
    {
        $this->signInAs(Role::TeamLead);
        $this->get(route('vessels.ingest.create'))->assertOk();

        $payload = ['source' => 'themis', 'external_id' => 'F-1', 'name' => 'FORM VESSEL', 'flag' => 'CI', 'imo' => '9700001'];
        $this->post(route('vessels.ingest.store'), $payload)->assertRedirect();
        $this->assertSame('FORM VESSEL', Vessel::sole()->name);

        $this->post(route('vessels.ingest.store'), [...$payload, 'external_id' => 'F-2', 'imo' => null, 'name' => 'form vessel'])->assertRedirect(route('vessels.duplicates.index'));
        $this->post(route('vessels.ingest.store'), ['source' => 'bogus'])->assertSessionHasErrors(['source', 'name']);
    }
}
