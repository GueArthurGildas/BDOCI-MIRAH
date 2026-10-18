<?php

namespace Modules\Catches\Tests\Feature;

use App\Models\CatchDeclaration;
use App\Models\Species;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatchPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_declarations_are_totalled_for_the_selected_species(): void
    {
        $this->signInAs(Role::Inspector);
        $tuna = Species::factory()->create(['name' => 'Albacore']);
        $sardine = Species::factory()->create(['name' => 'Sardinelle']);
        CatchDeclaration::factory()->for($tuna)->count(2)->create(['quantity_kg' => 1000]);
        CatchDeclaration::factory()->for($sardine)->create(['quantity_kg' => 500]);

        $this->get('/catches?species='.$tuna->id)
            ->assertOk()
            ->assertViewHas('totalQuantity', 2000.0);
    }
}
