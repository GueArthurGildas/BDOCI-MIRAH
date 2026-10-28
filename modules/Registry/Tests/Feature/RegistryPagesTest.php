<?php

namespace Modules\Registry\Tests\Feature;

use App\Enums\SourceSystem;
use App\Models\Country;
use App\Models\Gear;
use App\Models\SourcePriority;
use App\Models\Species;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Registry\Support\ResourceCatalog;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

class RegistryPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('registry.index'))->assertRedirect(route('login'));
    }

    public function test_every_registry_list_renders_for_a_reader(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('registry.index'))->assertOk();

        foreach (ResourceCatalog::all()->keys() as $key) {
            $this->get(route('registry.list', $key))->assertOk();
        }

        $this->get(route('registry.list', 'unknown'))->assertNotFound();
    }

    public function test_readers_cannot_change_a_registry(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('registry.create', 'gears'))->assertForbidden();
        $this->post(route('registry.store', 'gears'), ['code_fao' => 'OTB', 'label' => 'Chalut'])->assertForbidden();
        $this->assertSame(0, Gear::count());
    }

    public function test_a_gear_is_created_and_updated_through_audited_actions(): void
    {
        $actor = $this->signInAs(Role::TeamLead);

        $this->get(route('registry.create', 'gears'))->assertOk();
        $this->post(route('registry.store', 'gears'), ['code_fao' => 'OTB', 'label' => 'Chalut de fond'])->assertRedirect(route('registry.list', 'gears'));

        $gear = Gear::sole();
        $this->get(route('registry.edit', ['gears', $gear->id]))->assertOk()->assertSee('Chalut de fond');
        $this->put(route('registry.update', ['gears', $gear->id]), ['code_fao' => 'OTB', 'label' => 'Chalut à panneaux'])->assertRedirect();

        $this->assertSame('Chalut à panneaux', $gear->fresh()->label);
        $this->assertSame($actor->id, Activity::where('event', 'registry.create_record')->sole()->causer_id);
        $this->assertSame(1, Activity::where('event', 'registry.update_record')->count());
    }

    public function test_codes_are_validated_and_unique(): void
    {
        $this->signInAs(Role::TeamLead);
        Gear::factory()->create(['code_fao' => 'LLS']);

        $this->post(route('registry.store', 'gears'), ['code_fao' => 'LLS', 'label' => 'Palangre'])->assertSessionHasErrors('code_fao');
        $this->post(route('registry.store', 'gears'), ['label' => ''])->assertSessionHasErrors(['code_fao', 'label']);
        $this->assertSame(1, Gear::count());
    }

    public function test_species_use_fao_codes(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->post(route('registry.store', 'species'), ['fao_code' => 'YFT', 'name' => 'Albacore', 'scientific_name' => 'Thunnus albacares'])->assertRedirect();

        $this->assertSame('YFT', Species::sole()->fao_code);
    }

    public function test_a_country_can_be_registered(): void
    {
        $this->signInAs(Role::TeamLead);

        $this->post(route('registry.store', 'countries'), ['iso3_code' => 'CIV', 'iso2_code' => 'CI', 'label' => 'Côte d\'Ivoire'])->assertRedirect();

        $this->assertSame('CI', Country::sole()->iso2_code);
    }

    #[Covers('RG02', partial: true)]
    public function test_the_source_priorities_are_editable(): void
    {
        $this->signInAs(Role::TeamLead);
        $this->get(route('registry.priorities.edit'))->assertOk();

        $ranks = collect(config('registry.tracked_vessel_fields'))->mapWithKeys(fn ($field) => [$field => collect(SourceSystem::cases())->mapWithKeys(fn ($source) => [$source->value => 5])->all()])->all();
        $ranks['name']['neno'] = 1;

        $this->put(route('registry.priorities.update'), ['ranks' => $ranks])->assertRedirect(route('registry.priorities.edit'));

        $this->assertSame(1, SourcePriority::where('field', 'name')->where('source', 'neno')->sole()->rank);
        $this->put(route('registry.priorities.update'), ['ranks' => []])->assertSessionHasErrors();
    }
}
