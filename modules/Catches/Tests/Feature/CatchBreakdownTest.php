<?php

namespace Modules\Catches\Tests\Feature;

use App\Enums\CatchSource;
use App\Enums\MirrorOrigin;
use App\Models\CatchDeclaration;
use App\Models\DataSource;
use App\Models\Landing;
use App\Models\Species;
use App\Models\Transshipment;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatchBreakdownTest extends TestCase
{
    use RefreshDatabase;

    private function declare(Vessel $vessel, Species $species, float $kg, string $start, CatchSource $source = CatchSource::Logbook): CatchDeclaration
    {
        return CatchDeclaration::factory()->create([
            'vessel_id' => $vessel->id,
            'species_id' => $species->id,
            'quantity_kg' => $kg,
            'period_start' => $start,
            'period_end' => now()->parse($start)->addDays(6),
            'source' => $source,
        ]);
    }

    public function test_quantities_are_broken_down_by_species_with_their_share_and_vessel_count(): void
    {
        $this->signInAs(Role::Director);
        $tuna = Species::factory()->create(['name' => 'Thon']);
        $sardine = Species::factory()->create(['name' => 'Sardinelle']);
        $first = Vessel::factory()->create();
        $second = Vessel::factory()->create();
        $this->declare($first, $tuna, 750, '2026-02-02');
        $this->declare($second, $tuna, 250, '2026-02-09');
        $this->declare($first, $sardine, 1000, '2026-03-02');

        $this->get(route('catches.index'))
            ->assertOk()
            ->assertViewHas('totalQuantity', 2000.0)
            ->assertViewHas('bySpecies', function ($rows) use ($tuna) {
                $row = $rows->first(fn (array $row) => $row['species']->id === $tuna->id);

                return $row['total_kg'] === 1000.0 && $row['vessels_count'] === 2 && round($row['share']) === 50.0 && round($rows->sum('share')) === 100.0;
            });
    }

    public function test_quantities_are_broken_down_by_month_and_by_vessel(): void
    {
        $this->signInAs(Role::Director);
        $species = Species::factory()->create();
        $big = Vessel::factory()->create(['name' => 'GROS NAVIRE']);
        $small = Vessel::factory()->create(['name' => 'PETIT NAVIRE']);
        $this->declare($big, $species, 900, '2026-01-05');
        $this->declare($big, $species, 100, '2026-02-02');
        $this->declare($small, $species, 50, '2026-02-09');

        $this->get(route('catches.index'))
            ->assertViewHas('periodChart', fn (array $chart) => $chart['data']['labels'] === ['2026-01', '2026-02'] && array_map('intval', $chart['data']['datasets'][0]['data']) === [900, 150])
            ->assertViewHas('byVessel', fn ($rows) => $rows->pluck('vessel.name')->all() === ['GROS NAVIRE', 'PETIT NAVIRE'] && $rows->first()['total_kg'] === 1000.0);
    }

    public function test_the_vessel_filter_narrows_everything_and_shows_the_vessel(): void
    {
        $this->signInAs(Role::Director);
        $species = Species::factory()->create();
        $target = Vessel::factory()->create(['name' => 'NAVIRE CIBLE']);
        $other = Vessel::factory()->create(['name' => 'NAVIRE AUTRE']);
        $this->declare($target, $species, 300, '2026-02-02');
        $this->declare($other, $species, 700, '2026-02-02');

        $this->get(route('catches.index', ['vessel' => $target->id]))
            ->assertOk()
            ->assertViewHas('totalQuantity', 300.0)
            ->assertViewHas('selectedVessel', fn ($vessel) => $vessel->id === $target->id)
            ->assertViewHas('declarations', fn ($declarations) => $declarations->total() === 1)
            ->assertSee(__('catches::catches.all_vessels'));

        $this->get(route('catches.index', ['vessel' => 999999]))->assertSessionHasErrors('vessel');
    }

    public function test_a_quick_period_keeps_only_recent_declarations(): void
    {
        $this->signInAs(Role::Director);
        $species = Species::factory()->create();
        $vessel = Vessel::factory()->create();
        $this->declare($vessel, $species, 100, today()->subDays(10)->toDateString());
        $this->declare($vessel, $species, 900, today()->subDays(200)->toDateString());

        $this->get(route('catches.index', ['last' => '30d']))->assertViewHas('totalQuantity', 100.0);
        $this->get(route('catches.index', ['last' => '365d']))->assertViewHas('totalQuantity', 1000.0);
        $this->get(route('catches.index', ['last' => '1d']))->assertSessionHasErrors('last');
    }

    public function test_the_page_states_where_each_declaration_comes_from(): void
    {
        $this->signInAs(Role::Director);
        $species = Species::factory()->create();
        $vessel = Vessel::factory()->create();
        $this->declare($vessel, $species, 10, '2026-02-02', CatchSource::Logbook);
        $this->declare($vessel, $species, 10, '2026-02-09', CatchSource::Observer);

        $this->get(route('catches.index'))
            ->assertSee(__('catches::catches.feeding.title'))
            ->assertSee(__('enums.catch_source.logbook'))
            ->assertSee(__('enums.catch_source.observer'))
            ->assertSee(__('catches::catches.feeding.catches_limit'));
    }

    public function test_the_vessel_file_links_to_its_catches(): void
    {
        $this->signInAs(Role::Director);
        $vessel = Vessel::factory()->create();

        $this->get(route('vessels.show', $vessel))->assertSee(route('catches.index', ['vessel' => $vessel->id]), false);
    }

    public function test_landings_and_transshipments_explain_how_they_are_fed(): void
    {
        $this->signInAs(Role::Director);
        DataSource::factory()->create(['code' => 'e-inspection', 'name' => 'e-inspection', 'last_synced_at' => now()->subHours(3)]);
        Landing::factory()->create(['origin' => MirrorOrigin::EInspection]);
        Transshipment::factory()->create(['origin' => MirrorOrigin::EInspection]);

        foreach (['catches.landings', 'catches.transshipments'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee(__('catches::catches.feeding.title'))
                ->assertSee(__('catches::catches.feeding.mirror_source'))
                ->assertSee(__('enums.mirror_origin.e_inspection'))
                ->assertSee(__('catches::catches.feeding.mirror_fallback'))
                ->assertDontSee(__('catches::catches.feeding.mirror_never'));
        }
    }

    public function test_an_unsynchronized_source_is_reported_as_such(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('catches.landings'))->assertSee(__('catches::catches.feeding.mirror_never'));
    }
}
