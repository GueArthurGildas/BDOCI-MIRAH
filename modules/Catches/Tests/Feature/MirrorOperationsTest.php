<?php

namespace Modules\Catches\Tests\Feature;

use App\Models\Landing;
use App\Models\Transshipment;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class MirrorOperationsTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC19', partial: true)]
    public function test_landings_are_listed_and_filtered_by_vessel(): void
    {
        $this->signInAs(Role::Agent);
        Landing::factory()->for(Vessel::factory()->create(['name' => 'ALBACORA CINCO']), 'vessel')->create();
        Landing::factory()->for(Vessel::factory()->create(['name' => 'LAGUNE II']), 'vessel')->create();

        $this->get('/catches/landings?search=ALBACORA')->assertOk()->assertSee('ALBACORA CINCO')->assertDontSee('LAGUNE II');
    }

    #[Covers('UC19', partial: true)]
    public function test_transshipments_show_the_partner_vessel(): void
    {
        $this->signInAs(Role::Agent);
        Transshipment::factory()->create(['partner_vessel_id' => Vessel::factory()->create(['name' => 'PARTNER REEFER'])->id]);

        $this->get('/catches/transshipments')->assertOk()->assertSee('PARTNER REEFER');
    }

    #[Covers('UC19', partial: true)]
    public function test_guests_are_redirected(): void
    {
        $this->get('/catches/landings')->assertRedirect('/login');
    }
}
