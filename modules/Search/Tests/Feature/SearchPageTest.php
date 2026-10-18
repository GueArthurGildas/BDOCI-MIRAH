<?php

namespace Modules\Search\Tests\Feature;

use App\Models\Infraction;
use App\Models\Observation;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_requires_a_minimum_term_length(): void
    {
        $this->signInAs(Role::Inspector);

        $this->get('/search?q=a')->assertOk()->assertViewHas('canSearch', false);
    }

    public function test_results_are_grouped_by_source(): void
    {
        $this->signInAs(Role::Supervisor);
        $vessel = Vessel::factory()->create(['name' => 'MARAHOUÉ 12']);
        Infraction::factory()->for($vessel)->create(['reference' => 'PV-2026-555']);

        $this->get('/search?q=marahoué')
            ->assertOk()
            ->assertSee(route('vessels.show', $vessel))
            ->assertSee('PV-2026-555');
    }

    public function test_results_only_include_sources_the_user_may_see(): void
    {
        $this->signInAs(Role::Inspector);
        Observation::factory()->create(['description' => 'Comportement suspect de ALBACORA']);

        $this->get('/search?q=albacora')
            ->assertOk()
            ->assertDontSee('Comportement suspect de ALBACORA');
    }
}
