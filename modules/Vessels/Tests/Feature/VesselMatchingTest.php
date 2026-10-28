<?php

namespace Modules\Vessels\Tests\Feature;

use App\Enums\IdentifierType;
use App\Enums\MatchingCriterion;
use App\Enums\MatchingStatus;
use App\Enums\RecordStatus;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\VesselFieldProvenance;
use App\Models\VesselIdentifier;
use App\Models\VesselSource;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Vessels\Actions\IngestVesselSource;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC01', 'RG02')]
class VesselMatchingTest extends TestCase
{
    use RefreshDatabase;

    private function record(array $overrides = []): array
    {
        return array_merge([
            'source' => 'themis',
            'external_id' => 'THEMIS-1',
            'name' => 'ALBACORA UNO',
            'flag' => 'ES',
            'identifiers' => ['imo' => '9100001', 'mmsi' => '224100001'],
            'attributes' => [],
        ], $overrides);
    }

    private function ingest(array $record, Role $role = Role::TeamLead): VesselSource
    {
        $actor = $this->signInAs($role);

        return app(ActionRunner::class)->run(IngestVesselSource::class, $actor, $record);
    }

    #[Assumes('H-2-7')]
    public function test_a_record_matching_nothing_creates_a_new_validated_vessel_with_its_source(): void
    {
        $link = $this->ingest($this->record());

        $vessel = Vessel::sole();
        $this->assertSame(MatchingStatus::New, $link->matching_status);
        $this->assertSame(RecordStatus::Validated, $vessel->record_status);
        $this->assertSame('ES', $vessel->flag);
        $this->assertSame('9100001', $vessel->imo_number);
        $this->assertSame('224100001', $vessel->identifierValue(IdentifierType::Mmsi));
        $this->assertSame($vessel->id, $link->vessel_id);
        $this->assertSame('themis', VesselFieldProvenance::where('vessel_id', $vessel->id)->where('field', 'name')->sole()->source->value);
    }

    public function test_a_vessel_arriving_through_several_gates_is_created_once(): void
    {
        $this->ingest($this->record());
        $second = app(ActionRunner::class)->run(IngestVesselSource::class, auth()->user(), $this->record([
            'source' => 'e_inspection', 'external_id' => 'EI-77', 'name' => 'Albacora Uno', 'identifiers' => ['imo' => '9100001', 'ircs' => 'EAAB'],
        ]));
        $third = app(ActionRunner::class)->run(IngestVesselSource::class, auth()->user(), $this->record([
            'source' => 'neno', 'external_id' => 'NENO-9', 'identifiers' => ['mmsi' => '224100001'],
        ]));

        $vessel = Vessel::sole();
        $this->assertSame(3, $vessel->sources()->count());
        $this->assertSame(MatchingStatus::Matched, $second->matching_status);
        $this->assertSame(MatchingCriterion::Imo, $second->matching_criterion);
        $this->assertSame(MatchingCriterion::Mmsi, $third->matching_criterion);
        $this->assertSame('EAAB', $vessel->fresh()->call_sign);
        $this->assertSame(0, VesselSource::where('matching_status', MatchingStatus::PossibleDuplicate->value)->count());
    }

    public function test_matching_follows_the_order_imo_mmsi_ircs_registration(): void
    {
        $first = Vessel::factory()->create(['imo_number' => '9200001', 'call_sign' => 'AAAA1', 'registration_number' => 'REG-1']);
        $second = Vessel::factory()->create(['imo_number' => '9200002', 'call_sign' => 'BBBB2', 'registration_number' => 'REG-2']);
        $first->identifiers()->create(['type' => 'mmsi', 'value' => '111111111']);

        $byImo = $this->ingest($this->record(['external_id' => 'A', 'identifiers' => ['imo' => '9200001']]));
        $byMmsi = app(ActionRunner::class)->run(IngestVesselSource::class, auth()->user(), $this->record(['external_id' => 'B', 'identifiers' => ['mmsi' => '111111111']]));
        $byIrcs = app(ActionRunner::class)->run(IngestVesselSource::class, auth()->user(), $this->record(['external_id' => 'C', 'identifiers' => ['ircs' => 'BBBB2']]));
        $byRegistration = app(ActionRunner::class)->run(IngestVesselSource::class, auth()->user(), $this->record(['external_id' => 'D', 'identifiers' => ['registration' => 'REG-2']]));

        $this->assertSame([$first->id, MatchingCriterion::Imo], [$byImo->vessel_id, $byImo->matching_criterion]);
        $this->assertSame([$first->id, MatchingCriterion::Mmsi], [$byMmsi->vessel_id, $byMmsi->matching_criterion]);
        $this->assertSame([$second->id, MatchingCriterion::Ircs], [$byIrcs->vessel_id, $byIrcs->matching_criterion]);
        $this->assertSame([$second->id, MatchingCriterion::Registration], [$byRegistration->vessel_id, $byRegistration->matching_criterion]);
        $this->assertSame(2, Vessel::count());
    }

    #[Assumes('H-2-2')]
    public function test_name_and_flag_alone_never_merge_and_go_to_the_duplicates_queue(): void
    {
        $existing = Vessel::factory()->create(['name' => 'ATLANTIQUE 12', 'flag' => 'CI']);

        $link = $this->ingest($this->record(['external_id' => 'N-1', 'name' => 'Atlantique-12', 'flag' => 'CI', 'identifiers' => []]));

        $this->assertSame(MatchingStatus::PossibleDuplicate, $link->matching_status);
        $this->assertSame(MatchingCriterion::NameFlag, $link->matching_criterion);
        $this->assertSame($existing->id, $link->candidate_vessel_id);
        $this->assertSame(RecordStatus::ToMatch, $link->vessel->record_status);
        $this->assertSame(2, Vessel::count());
    }

    #[Assumes('H-2-3')]
    public function test_an_identifier_hit_with_a_contradicting_identifier_goes_to_the_queue(): void
    {
        $existing = Vessel::factory()->create(['imo_number' => '9300001', 'call_sign' => 'CCCC3']);
        $existing->identifiers()->create(['type' => 'mmsi', 'value' => '333333333']);

        $link = $this->ingest($this->record(['identifiers' => ['imo' => '9300001', 'mmsi' => '444444444']]));

        $this->assertSame(MatchingStatus::PossibleDuplicate, $link->matching_status);
        $this->assertSame(MatchingCriterion::Imo, $link->matching_criterion);
        $this->assertSame($existing->id, $link->candidate_vessel_id);
        $this->assertSame(1, VesselIdentifier::where('type', 'imo')->where('value', '9300001')->whereNull('valid_to')->count());
    }

    public function test_replaying_the_same_external_record_does_not_create_another_vessel(): void
    {
        $this->ingest($this->record());
        app(ActionRunner::class)->run(IngestVesselSource::class, auth()->user(), $this->record(['attributes' => ['length_m' => 61.5]]));

        $this->assertSame(1, Vessel::count());
        $this->assertSame(1, VesselSource::count());
        $this->assertSame(61.5, Vessel::sole()->length_m);
    }

    public function test_the_ingestion_writes_the_journal_and_the_audit_log(): void
    {
        $link = $this->ingest($this->record());

        $event = VesselEvent::sole();
        $this->assertSame('vessel.created_from_source', $event->type);
        $this->assertSame($link->vessel_id, $event->vessel_id);
        $this->assertSame(1, Activity::where('event', 'vessels.ingest_source')->count());
    }

    public function test_agents_cannot_ingest_records(): void
    {
        $this->expectException(AuthorizationException::class);

        $this->ingest($this->record(), Role::Agent);
    }
}
