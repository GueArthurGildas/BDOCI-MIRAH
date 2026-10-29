<?php

namespace Modules\Vessels\Tests\Feature;

use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VesselQuickViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_quick_view_returns_the_vessel_summary_without_the_page_layout(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create(['name' => 'AKWABA 1']);

        $this->get(route('vessels.quick', $vessel))
            ->assertOk()
            ->assertSee('AKWABA 1')
            ->assertSee(route('vessels.show', $vessel), false)
            ->assertDontSee('<html', false);
    }

    public function test_the_quick_view_needs_the_view_permission(): void
    {
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.quick', $vessel))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('vessels.quick', $vessel))->assertForbidden();
    }
}
