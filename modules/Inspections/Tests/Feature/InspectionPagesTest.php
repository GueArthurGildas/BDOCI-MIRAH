<?php

namespace Modules\Inspections\Tests\Feature;

use App\Models\DeclaredObservedCatch;
use App\Models\ExternalInspection;
use App\Models\InspectionInfraction;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class InspectionPagesTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC19', partial: true)]
    #[Assumes('H-7-8')]
    public function test_inspections_can_be_filtered_by_result(): void
    {
        $this->signInAs(Role::Agent);
        ExternalInspection::factory()->create(['reference' => 'EI-COMPLIANT', 'result' => 'compliant']);
        ExternalInspection::factory()->create(['reference' => 'EI-NONCOMPLIANT', 'result' => 'non_compliant']);

        $this->get('/inspections?result=non_compliant')
            ->assertOk()
            ->assertSee('EI-NONCOMPLIANT')
            ->assertDontSee('EI-COMPLIANT');
    }

    #[Covers('UC19', partial: true)]
    public function test_inspection_detail_lists_the_infractions_found(): void
    {
        $this->signInAs(Role::Agent);
        $inspection = ExternalInspection::factory()->create();
        InspectionInfraction::factory()->for($inspection, 'inspection')->create(['provision' => 'Loi n° 2016-554, art. 42', 'stamp' => 'SC-777']);

        $this->get(route('inspections.show', $inspection))->assertOk()->assertSee('SC-777');
    }

    #[Covers('UC19', 'RG09', partial: true)]
    public function test_inspection_detail_shows_the_variance_against_the_csp_threshold(): void
    {
        $this->signInAs(Role::TeamLead);
        $inspection = ExternalInspection::factory()->create();
        DeclaredObservedCatch::factory()->for($inspection, 'inspection')->create(['declared_weight_kg' => 1000, 'observed_weight_kg' => 1300, 'variance_pct' => 30, 'exceeds_csp_threshold' => true]);

        $this->get(route('inspections.show', $inspection))->assertOk()->assertSee('label-danger', false)->assertSee('30.00 %');
    }

    #[Covers('UC19', partial: true)]
    public function test_the_mirror_has_no_write_route(): void
    {
        $this->signInAs(Role::Director);
        $inspection = ExternalInspection::factory()->create();

        $this->post('/inspections', [])->assertStatus(405);
        $this->put(route('inspections.show', $inspection), [])->assertStatus(405);
    }

    #[Covers('UC19', partial: true)]
    public function test_an_unmatched_inspection_is_flagged(): void
    {
        $this->signInAs(Role::Agent);
        ExternalInspection::factory()->create(['vessel_id' => null, 'reference' => 'EI-ORPHAN']);

        $this->get('/inspections')->assertOk()->assertSee('EI-ORPHAN')->assertSee(__('inspections::inspections.unmatched'));
    }
}
