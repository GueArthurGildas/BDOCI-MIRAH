<?php

namespace Modules\Movements\Tests\Feature;

use App\Models\EezDeclaration;
use App\Models\ErsCatchLine;
use App\Models\ErsDeclaration;
use App\Models\Vessel;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Number;
use Tests\TestCase;

class DeclarationListsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_eez_list_shows_the_crossing_destination_and_channel_and_filters_by_vessel(): void
    {
        $this->signInAs(Role::Director);
        $target = Vessel::factory()->create(['name' => 'NAVIRE LISTE']);
        $other = Vessel::factory()->create(['name' => 'NAVIRE AUTRE']);
        EezDeclaration::factory()->create(['vessel_id' => $target->id, 'destination' => 'Port de Sassandra']);
        EezDeclaration::factory()->create(['vessel_id' => $other->id, 'destination' => 'Port de Tabou']);

        $this->get(route('movements.eez.index', ['vessel' => $target->id]))
            ->assertOk()
            ->assertSee('Port de Sassandra')
            ->assertDontSee('Port de Tabou')
            ->assertSee(__('movements::eez.fields.crossed_at'))
            ->assertSee(__('movements::eez.fields.reception_channel'));

        $this->get(route('movements.eez.index', ['vessel' => 999999]))->assertSessionHasErrors('vessel');
    }

    public function test_the_eez_export_buttons_carry_the_current_filters(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        $this->get(route('movements.eez.index', ['vessel' => $vessel->id, 'from' => '2026-03-01']))
            ->assertSee('reports/exports/eez_declarations', false)
            ->assertSee('vessel='.$vessel->id, false)
            ->assertSee('from=2026-03-01', false)
            ->assertSee(__('movements::eez.export_csv'));
    }

    public function test_the_export_buttons_are_hidden_without_the_export_permission(): void
    {
        $this->signInWithPermissions(Permission::MovementsView, Permission::VmsView);

        $this->get(route('movements.eez.index'))->assertOk()->assertDontSee('reports/exports/eez_declarations', false);
        $this->get(route('vms.ers.index'))->assertOk()->assertDontSee('reports/exports/ers_declarations', false);
    }

    public function test_the_ers_list_is_filtered_by_vessel_and_period(): void
    {
        $this->signInAs(Role::Director);
        $target = Vessel::factory()->create(['name' => 'ERS CIBLE']);
        $other = Vessel::factory()->create(['name' => 'ERS AUTRE']);
        ErsDeclaration::factory()->create(['vessel_id' => $target->id, 'declared_at' => '2026-03-10 08:00:00']);
        ErsDeclaration::factory()->create(['vessel_id' => $target->id, 'declared_at' => '2026-08-10 08:00:00']);
        ErsDeclaration::factory()->create(['vessel_id' => $other->id, 'declared_at' => '2026-03-11 08:00:00']);

        $this->get(route('vms.ers.index', ['vessel' => $target->id]))->assertViewHas('declarations', fn ($declarations) => $declarations->total() === 2);
        $this->get(route('vms.ers.index', ['from' => '2026-03-01', 'to' => '2026-03-31']))->assertViewHas('declarations', fn ($declarations) => $declarations->total() === 2);
        $this->get(route('vms.ers.index', ['vessel' => $target->id, 'from' => '2026-03-01', 'to' => '2026-03-31']))->assertViewHas('declarations', fn ($declarations) => $declarations->total() === 1)
            ->assertSee('reports/exports/ers_declarations', false);
        $this->get(route('vms.ers.index', ['from' => '2026-03-31', 'to' => '2026-03-01']))->assertSessionHasErrors('to');
    }

    public function test_the_ers_page_shows_the_whole_declaration(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create(['name' => 'ERS DETAIL']);
        $declaration = ErsDeclaration::factory()->create(['vessel_id' => $vessel->id, 'declared_at' => '2026-04-02 09:00:00']);
        ErsCatchLine::factory()->count(2)->create(['ers_declaration_id' => $declaration->id, 'live_weight_kg' => 500]);

        $this->get(route('vms.ers.show', $declaration))
            ->assertOk()
            ->assertSee('ERS DETAIL')
            ->assertSee(__('vms::vms.ers.total_weight_label'))
            ->assertSee(__('vms::vms.ers.species_count'))
            ->assertSee(Number::format(1000.0, 2));
    }

    public function test_the_vessel_declarations_tab_offers_an_export_of_all_its_declarations(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.show', $vessel))
            ->assertSee('reports/exports/eez_declarations', false)
            ->assertSee('vessel='.$vessel->id, false)
            ->assertSee(__('vessels::vessels.eez_export'));
    }
}
