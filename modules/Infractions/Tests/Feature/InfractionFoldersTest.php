<?php

namespace Modules\Infractions\Tests\Feature;

use App\Enums\InfractionCaseStatus;
use App\Models\EvidenceItem;
use App\Models\InfractionCase;
use App\Models\InfractionType;
use App\Models\Seal;
use App\Models\UpheldInfraction;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Infractions\Support\CaseFolders;
use Tests\TestCase;

class InfractionFoldersTest extends TestCase
{
    use RefreshDatabase;

    private function caseWith(InfractionCaseStatus $status, string $reference, string $vesselName = 'NAVIRE DOSSIER'): InfractionCase
    {
        return InfractionCase::factory()->create([
            'status' => $status,
            'reference' => $reference,
            'vessel_id' => Vessel::factory()->create(['name' => $vesselName])->id,
        ]);
    }

    public function test_the_folder_view_groups_cases_by_status_and_opens_the_selected_case(): void
    {
        $this->signInAs(Role::Director);
        $open = $this->caseWith(InfractionCaseStatus::Open, 'DI-2026-0001');
        $this->caseWith(InfractionCaseStatus::Instruction, 'DI-2026-0002');
        $this->caseWith(InfractionCaseStatus::Instruction, 'DI-2026-0003');

        $this->get(route('infractions.index', ['view' => 'folders', 'case' => $open->id]))
            ->assertOk()
            ->assertViewHas('tree', fn ($tree) => $tree['open']->count() === 1 && $tree['instruction']->count() === 2 && $tree['decision']->isEmpty())
            ->assertViewHas('selected', fn ($selected) => $selected->id === $open->id)
            ->assertSee('DI-2026-0001')
            ->assertSee(__('infractions::infractions.folders.upheld'))
            ->assertSee(__('infractions::infractions.folders.follow_ups'));
    }

    public function test_the_case_content_is_listed_by_folder(): void
    {
        $case = $this->caseWith(InfractionCaseStatus::Open, 'DI-2026-0010');
        $type = InfractionType::factory()->create(['label' => 'Pêche en zone interdite', 'code' => 'ZI-01']);
        UpheldInfraction::factory()->create(['infraction_case_id' => $case->id, 'infraction_type_id' => $type->id]);
        EvidenceItem::factory()->create(['infraction_case_id' => $case->id, 'description' => 'Photo du filet', 'document_id' => null]);
        Seal::factory()->create(['infraction_case_id' => $case->id, 'number' => 'SC-77']);

        $folders = CaseFolders::for($case);

        $this->assertSame('Pêche en zone interdite', $folders['upheld']->first()['label']);
        $this->assertSame('ZI-01', $folders['upheld']->first()['detail']);
        $this->assertSame('Photo du filet', $folders['evidence']->first()['label']);
        $this->assertSame('SC-77', $folders['seals']->first()['label']);
        $this->assertCount(0, $folders['seizures']);
    }

    public function test_a_chosen_folder_shows_its_rows(): void
    {
        $this->signInAs(Role::Director);
        $case = $this->caseWith(InfractionCaseStatus::Open, 'DI-2026-0020');
        Seal::factory()->create(['infraction_case_id' => $case->id, 'number' => 'SCELLE-ABC']);

        $this->get(route('infractions.index', ['view' => 'folders', 'case' => $case->id, 'folder' => 'seals']))
            ->assertOk()
            ->assertSee('SCELLE-ABC')
            ->assertSee(route('infractions.show', $case), false);
    }

    public function test_filters_apply_to_the_folder_view_and_bad_input_is_rejected(): void
    {
        $this->signInAs(Role::Director);
        $this->caseWith(InfractionCaseStatus::Open, 'DI-2026-0030', 'ALPHA NAVIRE');
        $this->caseWith(InfractionCaseStatus::Open, 'DI-2026-0031', 'BETA NAVIRE');

        $this->get(route('infractions.index', ['view' => 'folders', 'search' => 'ALPHA']))
            ->assertViewHas('cases', fn ($cases) => $cases->count() === 1 && $cases->first()->reference === 'DI-2026-0030');

        $this->get(route('infractions.index', ['view' => 'folders', 'folder' => 'passwords']))->assertSessionHasErrors('folder');
        $this->get(route('infractions.index', ['view' => 'cards']))->assertSessionHasErrors('view');
    }

    public function test_the_list_view_stays_the_default_and_the_empty_folder_view_is_graceful(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('infractions.index'))->assertOk()->assertViewHas('view', 'list');
        $this->get(route('infractions.index', ['view' => 'folders']))->assertOk()->assertSee(__('ui.no_results'));
    }
}
