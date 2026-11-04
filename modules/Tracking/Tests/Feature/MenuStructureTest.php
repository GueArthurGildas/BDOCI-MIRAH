<?php

namespace Modules\Tracking\Tests\Feature;

use App\Models\Vessel;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationNode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MenuStructureTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<int, string|array<string, mixed>> */
    private function outline(Collection $nodes): array
    {
        return $nodes->map(fn (NavigationNode $node) => $node->isFolder()
            ? [$node->folder->value => $this->outline($node->children)]
            : $node->item->route)->all();
    }

    private function tree(string $groupKey): array
    {
        $user = $this->signInAs(Role::Director);

        return $this->outline(app(Navigation::class)->treeFor($user)->get('ui.navigation_groups.'.$groupKey));
    }

    public function test_pilotage_holds_e_inspection_thermis_fleet_notifications_and_communications(): void
    {
        $this->assertSame([
            'dashboard',
            ['e_inspection' => ['catches.landings', 'infractions.index', 'catches.index', 'catches.transshipments', 'inspections.index', 'inspections.never-inspected']],
            'thermis.index',
            'fleet.index',
            'notifications.index',
            'communications.messages.index',
            'profile.show',
        ], $this->tree('overview'));
    }

    public function test_surveillance_starts_with_vessels_morning_board_alerts_licenses_and_authorized_vessels(): void
    {
        $tree = $this->tree('surveillance');

        $this->assertSame('vessels.index', $tree[0]);
        $this->assertArrayHasKey('morning_board', $tree[1]);
        $this->assertSame(['tracking.alerts.index', 'licenses.index', 'licenses.authorized.index'], array_slice($tree, 2, 3));
    }

    public function test_the_morning_board_gathers_its_sections_and_the_logbook_modules(): void
    {
        $morning = $this->tree('surveillance')[1]['morning_board'];

        $this->assertSame(['watch.board.index', 'tracking.beacons', 'watch.instructions.index', 'logbook.index'], array_slice($morning, 0, 4));
        $this->assertSame([
            'watch.observations.index',
            'watch.rotations.index',
            'watch.instructions.index',
            'tracking.alerts.index',
            'tracking.slots.index',
            'logbook.days.index',
            'vms.beacons.index',
            'logbook.index',
            'vms.failures.index',
            'vms.ers.index',
        ], $morning[4]['logbook']);
    }

    public function test_vessels_and_licenses_no_longer_sit_in_the_registry_group(): void
    {
        $registry = $this->tree('registry');

        $this->assertNotContains('vessels.index', $registry);
        $this->assertNotContains('licenses.index', $registry);
        $this->assertContains('vessels.duplicates.index', $registry);
    }

    public function test_the_sidebar_opens_the_folders_leading_to_the_current_page(): void
    {
        $this->signInAs(Role::Director);

        $html = $this->get(route('vms.failures.index'))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'gs-folder is-open'));
        $this->assertSame(1, substr_count($html, '<li class="active">'));
        $this->assertMatchesRegularExpression('#<li class="active">\s*<a href="'.preg_quote(route('vms.failures.index'), '#').'"#', $html);
    }

    public function test_a_page_outside_the_folders_leaves_them_closed(): void
    {
        $this->signInAs(Role::Director);

        $html = $this->get(route('vessels.index'))->assertOk()->getContent();

        $this->assertSame(0, substr_count($html, 'gs-folder is-open'));
        $this->assertSame(1, substr_count($html, '<li class="active">'));
    }

    public function test_a_page_listed_twice_still_highlights_a_single_entry(): void
    {
        $this->signInAs(Role::Director);

        foreach (['tracking.alerts.index', 'logbook.index', 'watch.instructions.index'] as $route) {
            $this->assertSame(1, preg_match_all('/<li class="active"[^>]*>/', $this->get(route($route))->getContent()), $route);
        }
    }

    public function test_thermis_page_explains_when_it_is_not_configured(): void
    {
        $this->signInAs(Role::Agent);
        config(['surveillance.vms.themis_url' => null, 'surveillance.vms.themis_vessel_url' => null]);

        $this->get(route('thermis.index'))->assertOk()->assertSee(__('tracking::thermis.not_configured'));
    }

    public function test_thermis_page_builds_links_for_the_selected_vessels(): void
    {
        $this->signInAs(Role::Agent);
        config(['surveillance.vms.themis_url' => 'https://thermis.example/map', 'surveillance.vms.themis_vessel_url' => 'https://thermis.example/vessel/{identifier}']);
        $first = Vessel::factory()->create(['imo_number' => 'IMO1111111']);
        $second = Vessel::factory()->create(['imo_number' => 'IMO2222222']);
        Vessel::factory()->create(['imo_number' => 'IMO3333333']);

        $this->get(route('thermis.index', ['vessels' => [$first->id, $second->id]]))
            ->assertOk()
            ->assertSee('https://thermis.example/vessel/IMO1111111', false)
            ->assertSee('https://thermis.example/vessel/IMO2222222', false)
            ->assertDontSee('https://thermis.example/vessel/IMO3333333', false)
            ->assertSee('https://thermis.example/map', false);
    }

    public function test_thermis_page_needs_the_tracking_permission(): void
    {
        $this->signInWithPermissions(Permission::DashboardView);

        $this->get(route('thermis.index'))->assertForbidden();
    }
}
