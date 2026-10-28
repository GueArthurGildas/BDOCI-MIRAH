<?php

namespace Modules\Inspections\Tests\Feature;

use App\Enums\InspectionResult;
use App\Models\Infraction;
use App\Models\Inspection;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class InspectionPagesTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC19', partial: true)]
    public function test_inspections_can_be_filtered_by_result(): void
    {
        $this->signInAs(Role::Agent);
        Inspection::factory()->create(['external_reference' => 'EI-COMPLIANT', 'result' => InspectionResult::Compliant]);
        Inspection::factory()->create(['external_reference' => 'EI-NONCOMPLIANT', 'result' => InspectionResult::NonCompliant]);

        $this->get('/inspections?result=non_compliant')
            ->assertOk()
            ->assertSee('EI-NONCOMPLIANT')
            ->assertDontSee('EI-COMPLIANT');
    }

    #[Covers('UC19', partial: true)]
    public function test_inspection_detail_lists_related_infractions(): void
    {
        $this->signInAs(Role::Agent);
        $inspection = Inspection::factory()->create();
        Infraction::factory()->for($inspection)->create(['reference' => 'PV-2026-999', 'vessel_id' => $inspection->vessel_id]);

        $this->get(route('inspections.show', $inspection))->assertOk()->assertSee('PV-2026-999');
    }
}
