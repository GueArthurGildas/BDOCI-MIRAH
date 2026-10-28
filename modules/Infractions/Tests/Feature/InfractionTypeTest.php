<?php

namespace Modules\Infractions\Tests\Feature;

use App\Models\InfractionCase;
use App\Models\InfractionType;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC16', partial: true)]
class InfractionTypeTest extends TestCase
{
    use RefreshDatabase;

    #[Assumes('H-6-3')]
    public function test_the_administrator_edits_the_nomenclature(): void
    {
        $this->signInAs(Role::Admin);

        $this->post(route('infractions.types.store'), ['code' => 'INF-90', 'label' => 'Pêche au-delà du quota', 'severity' => 'serious', 'is_active' => 1])->assertRedirect(route('infractions.types.index'));
        $type = InfractionType::where('code', 'INF-90')->sole();
        $this->put(route('infractions.types.update', $type), ['code' => 'INF-90', 'label' => 'Dépassement de quota', 'is_active' => 0])->assertRedirect();

        $this->assertSame('Dépassement de quota', $type->fresh()->label);
        $this->assertFalse($type->fresh()->is_active);
        $this->get(route('infractions.types.index'))->assertOk()->assertSee('Dépassement de quota');
    }

    public function test_codes_are_unique_and_business_roles_cannot_edit_the_nomenclature(): void
    {
        InfractionType::factory()->create(['code' => 'INF-91']);

        $this->signInAs(Role::Director);
        $this->get(route('infractions.types.index'))->assertForbidden();

        $this->signInAs(Role::Admin);
        $this->post(route('infractions.types.store'), ['code' => 'INF-91', 'label' => 'Doublon'])->assertSessionHasErrors('code');
    }

    public function test_inactive_types_cannot_be_upheld(): void
    {
        $this->signInAs(Role::TeamLead);
        $case = InfractionCase::factory()->create();
        $type = InfractionType::factory()->create(['is_active' => false]);

        $this->post(route('infractions.upheld.store', $case), ['infraction_type_id' => $type->id])->assertSessionHasErrors('infraction_type_id');
        $this->assertSame(0, $case->upheld()->count());
    }
}
