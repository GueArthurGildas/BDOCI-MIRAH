<?php

namespace Modules\Infractions\Tests\Feature;

use App\Enums\ActorRole;
use App\Enums\InfractionCaseStatus;
use App\Models\Document;
use App\Models\ExternalInspection;
use App\Models\InfractionCase;
use App\Models\InfractionType;
use App\Models\LegalEntity;
use App\Models\License;
use App\Models\Person;
use App\Models\Vessel;
use App\Models\VesselActor;
use App\Models\VesselEvent;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class CaseFileTest extends TestCase
{
    use RefreshDatabase;

    private function open(array $overrides = [])
    {
        return $this->post(route('infractions.store'), array_merge(['vessel_id' => Vessel::factory()->create()->id, 'facts' => 'Pêche en zone interdite.'], $overrides));
    }

    #[Covers('UC16', 'RG09', partial: true)]
    #[Assumes('H-6-9')]
    public function test_opening_a_case_numbers_it_picks_the_parties_and_writes_the_journal_and_audit(): void
    {
        $actor = $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $captain = Person::factory()->create();
        $owner = LegalEntity::factory()->create();
        VesselActor::create(['vessel_id' => $vessel->id, 'role' => ActorRole::Captain, 'person_id' => $captain->id, 'started_on' => today()->subYear()]);
        VesselActor::create(['vessel_id' => $vessel->id, 'role' => ActorRole::Owner, 'legal_entity_id' => $owner->id, 'started_on' => today()]);
        $inspection = ExternalInspection::factory()->create(['vessel_id' => $vessel->id]);
        $type = InfractionType::factory()->create();

        $this->open(['vessel_id' => $vessel->id, 'inspection_id' => $inspection->id, 'infraction_type_ids' => [$type->id]])->assertRedirect();

        $case = InfractionCase::sole();
        $this->assertSame('DI-'.now()->year.'-0001', $case->reference);
        $this->assertSame($captain->id, $case->captain_id);
        $this->assertSame($owner->id, $case->shipowner_id);
        $this->assertSame($inspection->id, $case->inspection_id);
        $this->assertSame($actor->id, $case->opened_by);
        $this->assertSame(1, $case->upheld()->count());
        $this->assertSame('infraction_case.opened', VesselEvent::sole()->type);
        $this->assertSame($actor->id, Activity::where('event', 'infractions.open')->sole()->causer_id);

        $this->open()->assertRedirect();
        $this->assertSame('DI-'.now()->year.'-0002', InfractionCase::latest('id')->first()->reference);
    }

    public function test_opening_a_case_without_facts_writes_nothing(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->open(['facts' => ''])->assertSessionHasErrors('facts');
        $this->assertSame(0, InfractionCase::count());
        $this->assertSame(0, VesselEvent::count());
    }

    #[Covers('UC16', partial: true)]
    #[Assumes('H-6-5')]
    public function test_upholding_evidence_and_seals_move_the_case_to_instruction(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        $case = InfractionCase::factory()->create();
        $type = InfractionType::factory()->create();

        $this->post(route('infractions.upheld.store', $case), ['infraction_type_id' => $type->id])->assertRedirect();
        $this->post(route('infractions.upheld.store', $case), ['infraction_type_id' => $type->id])->assertSessionHasErrors('infraction_type_id');
        $this->post(route('infractions.evidence.store', $case), ['description' => 'Photo des engins', 'file' => UploadedFile::fake()->image('engins.jpg')])->assertRedirect();
        $this->post(route('infractions.seals.store', $case), ['number' => 'SC-2001'])->assertRedirect();
        $this->post(route('infractions.seizures.store', $case), ['item' => 'Filet maillant', 'decision' => 'confiscation'])->assertRedirect();

        $case->refresh();
        $this->assertSame(InfractionCaseStatus::Instruction, $case->status);
        $this->assertSame(1, $case->upheld()->count());
        $this->assertNotNull($case->evidence()->sole()->document_id);
        $this->assertSame('SC-2001', $case->seals()->sole()->number);
        $this->assertSame('confiscation', $case->seizures()->sole()->decision->value);
    }

    #[Covers('UC16', 'UC17', partial: true)]
    #[Assumes('H-6-6')]
    public function test_precautionary_measures_are_proposed_by_the_team_lead_and_decided_by_the_director_only(): void
    {
        $teamLead = $this->signInAs(Role::TeamLead);
        $case = InfractionCase::factory()->create();

        $this->post(route('infractions.measures.store', $case), ['type' => 'immobilization'])->assertRedirect();
        $measure = $case->measures()->sole();
        $this->assertSame($teamLead->id, $measure->proposed_by);
        $this->assertNull($measure->decided_at);

        $this->post(route('infractions.measures.decide', $measure))->assertForbidden();
        $this->assertNull($measure->fresh()->decided_at);

        $director = $this->signInAs(Role::Director);
        $this->post(route('infractions.measures.decide', $measure))->assertRedirect();

        $this->assertSame($director->id, $measure->fresh()->decided_by);
        $this->assertNotNull($measure->fresh()->decided_at);
        $this->post(route('infractions.measures.decide', $measure))->assertSessionHasErrors('measure');
        $this->assertSame(1, VesselEvent::where('type', 'infraction_case.measure_decided')->count());
    }

    #[Covers('UC16', partial: true)]
    public function test_the_violation_report_is_a_frozen_pdf_and_unique(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::TeamLead);
        $case = InfractionCase::factory()->create();

        $this->post(route('infractions.violation-report.store', $case), ['notified_at' => now()->toDateTimeString(), 'is_signature_refused' => 1])->assertRedirect();

        $report = $case->violationReport()->sole();
        $this->assertSame('PV-'.now()->year.'-0001', $report->number);
        $this->assertTrue($report->is_signature_refused);

        $document = Document::findOrFail($report->document_id);
        $this->assertTrue($document->is_frozen);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertTrue(app(DocumentStore::class)->isIntact($document));
        $this->get(route('infractions.documents.download', [$case, $document]))->assertOk();

        $this->post(route('infractions.violation-report.store', $case), [])->assertSessionHasErrors('violation_report');
        $this->assertSame(1, $case->violationReport()->count());
    }

    #[Covers('UC17', partial: true)]
    #[Assumes('H-6-5')]
    public function test_follow_ups_decisions_payments_appeals_and_closing_are_recorded_by_people_who_decide(): void
    {
        Storage::fake(DocumentStore::DISK);
        $director = $this->signInAs(Role::Director);
        $case = InfractionCase::factory()->create();

        $this->post(route('infractions.follow-ups.store', $case), ['type' => 'administrative_penalty'])->assertRedirect();
        $followUp = $case->followUps()->sole();
        $this->assertSame(InfractionCaseStatus::Decision, $case->fresh()->status);

        $this->post(route('infractions.decisions.store', $followUp), ['label' => 'Amende de 5 000 000 FCFA', 'is_penalty' => 1])->assertRedirect();
        $decision = $followUp->decisions()->sole();
        $this->assertSame($director->id, $decision->decided_by);
        $this->assertTrue($decision->is_penalty);
        $document = app(DocumentStore::class)->for($decision)->sole();
        $this->assertTrue($document->is_frozen);
        $this->get(route('infractions.documents.download', [$case, $document]))->assertOk();

        $this->post(route('infractions.payments.store', $decision), ['amount' => 5000000, 'paid_on' => today()->toDateString()])->assertRedirect();
        $this->post(route('infractions.appeals.store', $decision), ['filed_on' => today()->toDateString()])->assertRedirect();
        $appeal = $decision->appeals()->sole();
        $this->patch(route('infractions.appeals.outcome', $appeal), ['outcome' => 'Décision confirmée'])->assertRedirect();
        $this->assertSame('Décision confirmée', $appeal->fresh()->outcome);
        $this->assertSame('5000000.00', $decision->payments()->sole()->amount);

        $this->post(route('infractions.close', $case))->assertRedirect();
        $this->assertSame(InfractionCaseStatus::Closed, $case->fresh()->status);
        $this->assertNotNull($followUp->fresh()->closed_at);
        $this->post(route('infractions.close', $case))->assertSessionHasErrors('case');
    }

    #[Covers('UC17', partial: true)]
    #[Assumes('H-6-6')]
    public function test_decisions_are_never_taken_automatically_and_need_the_decide_permission(): void
    {
        $case = InfractionCase::factory()->create();
        $this->signInAs(Role::Agent);

        $this->post(route('infractions.follow-ups.store', $case), ['type' => 'warning'])->assertForbidden();
        $this->post(route('infractions.close', $case))->assertForbidden();
        $this->assertSame(0, $case->followUps()->count());
        $this->assertSame(InfractionCaseStatus::Open, $case->fresh()->status);
    }

    #[Covers('UC17', partial: true)]
    public function test_a_license_suspension_can_be_tied_to_the_case_and_is_listed_on_it(): void
    {
        $this->signInAs(Role::Director);
        $case = InfractionCase::factory()->create();
        $license = License::factory()->expiringIn(200)->create(['vessel_id' => $case->vessel_id]);

        $this->post(route('licenses.suspensions.store', $license), [
            'reason' => 'art_105',
            'started_on' => today()->toDateString(),
            'planned_end_on' => today()->addMonth()->toDateString(),
            'infraction_case_id' => $case->id,
        ])->assertRedirect();

        $this->assertSame($case->id, $license->suspensions()->sole()->infraction_case_id);
        $this->get(route('infractions.show', $case))->assertOk()->assertSee($license->number);
    }
}
