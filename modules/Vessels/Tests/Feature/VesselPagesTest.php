<?php

namespace Modules\Vessels\Tests\Feature;

use App\Enums\VesselCategory;
use App\Models\License;
use App\Models\Position;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VesselPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/vessels')->assertRedirect(route('login'));
    }

    public function test_vessels_can_be_listed_and_filtered(): void
    {
        $this->signInAs(Role::Inspector);
        Vessel::factory()->create(['name' => 'AKWABA 1']);
        Vessel::factory()->patrol()->create(['name' => 'VEDETTE CSP 01']);

        $this->get('/vessels')->assertOk()->assertSee('AKWABA 1')->assertSee('VEDETTE CSP 01');

        $this->get('/vessels?category='.VesselCategory::Patrol->value)
            ->assertOk()
            ->assertSee('VEDETTE CSP 01')
            ->assertDontSee('AKWABA 1');

        $this->get('/vessels?search=akwaba')->assertSee('AKWABA 1')->assertDontSee('VEDETTE CSP 01');
    }

    public function test_vessel_profile_gathers_its_history(): void
    {
        $this->signInAs(Role::Inspector);
        $vessel = Vessel::factory()->create();
        License::factory()->for($vessel)->create(['number' => 'LIC-0001-2026']);
        Position::factory()->for($vessel)->create();

        $this->get(route('vessels.show', $vessel))
            ->assertOk()
            ->assertSee($vessel->name)
            ->assertSee('LIC-0001-2026');
    }

    public function test_inspectors_cannot_manage_vessels(): void
    {
        $this->signInAs(Role::Inspector);

        $this->get('/vessels/create')->assertForbidden();
        $this->post('/vessels', [])->assertForbidden();
    }

    public function test_supervisors_can_create_and_update_vessels(): void
    {
        $this->signInAs(Role::Supervisor);
        $payload = [
            'name' => 'BANDAMA 7',
            'registration_number' => 'AN-7777',
            'flag' => 'CI',
            'category' => 'fishing',
            'type' => 'seiner',
            'status' => 'active',
        ];

        $this->post('/vessels', $payload)->assertRedirect();
        $vessel = Vessel::where('registration_number', 'AN-7777')->firstOrFail();

        $this->put(route('vessels.update', $vessel), [...$payload, 'owner' => 'Armement Ivoirien'])
            ->assertRedirect(route('vessels.show', $vessel));

        $this->assertSame('Armement Ivoirien', $vessel->fresh()->owner);
    }

    public function test_registration_number_must_be_unique(): void
    {
        $this->signInAs(Role::Supervisor);
        Vessel::factory()->create(['registration_number' => 'AN-0001']);

        $this->post('/vessels', ['registration_number' => 'AN-0001'])
            ->assertSessionHasErrors(['registration_number', 'name', 'flag', 'type', 'category', 'status']);
    }
}
