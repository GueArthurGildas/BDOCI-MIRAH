<?php

namespace Modules\Vessels\Tests\Feature;

use App\Enums\IdentifierType;
use App\Enums\MatchingStatus;
use App\Enums\RecordStatus;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\VesselSource;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Vessels\Actions\IngestVesselSource;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC01', 'RG02')]
class DuplicateQueueTest extends TestCase
{
    use RefreshDatabase;

    private function flaggedDuplicate(): VesselSource
    {
        $actor = $this->signInAs(Role::TeamLead);
        Vessel::factory()->create(['name' => 'SOPERKA 7', 'flag' => 'SN', 'imo_number' => '9400001', 'length_meters' => null]);

        return app(ActionRunner::class)->run(IngestVesselSource::class, $actor, [
            'source' => 'dpep', 'external_id' => 'DPEP-1', 'name' => 'Soperka 7', 'flag' => 'SN',
            'identifiers' => ['mmsi' => '663000001'], 'attributes' => ['length_m' => 33],
        ]);
    }

    public function test_the_team_lead_sees_the_queue_and_agents_do_not(): void
    {
        $link = $this->flaggedDuplicate();

        $this->get(route('vessels.duplicates.index'))->assertOk()->assertSee('Soperka 7')->assertSee('SOPERKA 7');

        $this->signInAs(Role::Agent);
        $this->get(route('vessels.duplicates.index'))->assertForbidden();
        $this->post(route('vessels.duplicates.settle', $link), ['decision' => 'merge'])->assertForbidden();
    }

    #[Assumes('H-2-5')]
    public function test_merging_adds_the_information_to_the_existing_vessel_and_retires_the_duplicate(): void
    {
        $link = $this->flaggedDuplicate();
        $duplicateId = $link->vessel_id;
        $targetId = $link->candidate_vessel_id;

        $this->post(route('vessels.duplicates.settle', $link), ['decision' => 'merge'])->assertRedirect(route('vessels.duplicates.index'));

        $target = Vessel::findOrFail($targetId);
        $this->assertTrue(Vessel::withTrashed()->findOrFail($duplicateId)->trashed());
        $this->assertSame(MatchingStatus::Matched, $link->fresh()->matching_status);
        $this->assertSame($targetId, $link->fresh()->vessel_id);
        $this->assertNotNull($link->fresh()->settled_at);
        $this->assertSame('663000001', $target->identifierValue(IdentifierType::Mmsi));
        $this->assertSame(33.0, $target->fresh()->length_m);
        $this->assertSame(1, Vessel::count());
        $this->assertSame('vessel.duplicate_merged', VesselEvent::where('vessel_id', $targetId)->latest('id')->first()->type);
        $this->assertSame(1, Activity::where('event', 'vessels.settle_duplicate')->count());
    }

    public function test_keeping_the_records_separate_validates_the_new_vessel(): void
    {
        $link = $this->flaggedDuplicate();

        $this->post(route('vessels.duplicates.settle', $link), ['decision' => 'keep_separate']);

        $this->assertSame(2, Vessel::count());
        $this->assertSame(RecordStatus::Validated, $link->vessel->fresh()->record_status);
        $this->assertSame(MatchingStatus::New, $link->fresh()->matching_status);
    }

    public function test_rejecting_retires_the_new_vessel_and_marks_the_source_rejected(): void
    {
        $link = $this->flaggedDuplicate();

        $this->post(route('vessels.duplicates.settle', $link), ['decision' => 'reject']);

        $this->assertSame(1, Vessel::count());
        $this->assertSame(MatchingStatus::Rejected, $link->fresh()->matching_status);
        $this->assertNull($link->fresh()->vessel_id);
    }

    public function test_a_settled_duplicate_leaves_the_queue_and_cannot_be_settled_twice(): void
    {
        $link = $this->flaggedDuplicate();
        $this->post(route('vessels.duplicates.settle', $link), ['decision' => 'merge']);

        $this->get(route('vessels.duplicates.index'))->assertDontSee('Soperka 7');
        $this->post(route('vessels.duplicates.settle', $link), ['decision' => 'merge'])->assertNotFound();
    }

    public function test_an_unknown_decision_is_rejected(): void
    {
        $link = $this->flaggedDuplicate();

        $this->post(route('vessels.duplicates.settle', $link), ['decision' => 'burn'])->assertSessionHasErrors('decision');
        $this->assertSame(MatchingStatus::PossibleDuplicate, $link->fresh()->matching_status);
    }
}
