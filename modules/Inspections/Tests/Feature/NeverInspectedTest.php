<?php

namespace Modules\Inspections\Tests\Feature;

use App\Enums\ObservedUnitLevel;
use App\Models\ExternalInspection;
use App\Models\Mission;
use App\Models\ObservedUnit;
use App\Models\Vessel;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NeverInspectedTest extends TestCase
{
    use RefreshDatabase;

    private function sight(Vessel $vessel, int $times, ObservedUnitLevel $level = ObservedUnitLevel::Sighted, ?string $at = null): void
    {
        $mission = Mission::factory()->create();

        foreach (range(1, $times) as $index) {
            ObservedUnit::factory()->create([
                'mission_id' => $mission->id,
                'vessel_id' => $vessel->id,
                'level' => $level,
                'observed_at' => $at ?? now()->subDays($index),
                'inspection_id' => $level === ObservedUnitLevel::Inspected ? ExternalInspection::factory()->create(['vessel_id' => $vessel->id])->id : null,
            ]);
        }
    }

    public function test_only_vessels_sighted_often_and_never_inspected_are_listed(): void
    {
        $this->signInAs(Role::Director);
        $often = Vessel::factory()->create(['name' => 'SOUVENT VU']);
        $once = Vessel::factory()->create(['name' => 'VU UNE FOIS']);
        $inspected = Vessel::factory()->create(['name' => 'DEJA CONTROLE']);
        $this->sight($often, 3);
        $this->sight($once, 1);
        $this->sight($inspected, 4);
        $this->sight($inspected, 1, ObservedUnitLevel::Inspected);

        $this->get(route('inspections.never-inspected'))
            ->assertOk()
            ->assertViewHas('minimum', 2)
            ->assertViewHas('vessels', fn ($vessels) => $vessels->pluck('name')->all() === ['SOUVENT VU'] && (int) $vessels->first()->sighted_count === 3)
            ->assertSee('SOUVENT VU')
            ->assertDontSee('VU UNE FOIS')
            ->assertDontSee('DEJA CONTROLE');

        $this->get(route('inspections.never-inspected', ['min' => 1]))
            ->assertViewHas('vessels', fn ($vessels) => $vessels->pluck('name')->sort()->values()->all() === ['SOUVENT VU', 'VU UNE FOIS']);
    }

    public function test_the_most_seen_vessels_come_first_with_their_last_sighting(): void
    {
        $this->signInAs(Role::Director);
        $few = Vessel::factory()->create(['name' => 'PEU VU']);
        $many = Vessel::factory()->create(['name' => 'BEAUCOUP VU']);
        $this->sight($few, 2, at: '2026-03-01 10:00:00');
        $this->sight($many, 5, at: '2026-02-01 10:00:00');

        $this->get(route('inspections.never-inspected'))
            ->assertViewHas('vessels', fn ($vessels) => $vessels->pluck('name')->all() === ['BEAUCOUP VU', 'PEU VU']);
    }

    public function test_search_and_period_filters_apply(): void
    {
        $this->signInAs(Role::Director);
        $recent = Vessel::factory()->create(['name' => 'ALPHA RECENT']);
        $old = Vessel::factory()->create(['name' => 'BETA ANCIEN']);
        $this->sight($recent, 2, at: '2026-06-10 10:00:00');
        $this->sight($old, 2, at: '2026-01-10 10:00:00');

        $this->get(route('inspections.never-inspected', ['search' => 'ALPHA']))->assertViewHas('vessels', fn ($vessels) => $vessels->pluck('name')->all() === ['ALPHA RECENT']);
        $this->get(route('inspections.never-inspected', ['from' => '2026-06-01', 'to' => '2026-06-30']))->assertViewHas('vessels', fn ($vessels) => $vessels->pluck('name')->all() === ['ALPHA RECENT']);
        $this->get(route('inspections.never-inspected', ['min' => 4]))->assertSessionHasErrors('min');
        $this->get(route('inspections.never-inspected', ['from' => '2026-06-30', 'to' => '2026-06-01']))->assertSessionHasErrors('to');
    }

    public function test_the_page_is_reserved_to_users_who_can_view_inspections(): void
    {
        $this->get(route('inspections.never-inspected'))->assertRedirect(route('login'));

        $this->signInWithPermissions(Permission::DashboardView);
        $this->get(route('inspections.never-inspected'))->assertForbidden();
    }

    public function test_the_entry_sits_in_the_e_inspection_menu_folder(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('inspections.never-inspected'))->assertSee(__('inspections::never.nav'))->assertSee('gs-folder is-open', false);
    }
}
