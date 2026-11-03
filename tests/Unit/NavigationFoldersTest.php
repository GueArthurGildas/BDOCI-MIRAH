<?php

namespace Tests\Unit;

use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationFolder;
use App\Support\Navigation\NavigationGroup;
use PHPUnit\Framework\TestCase;

class NavigationFoldersTest extends TestCase
{
    public function test_folder_items_join_the_group_of_their_folder(): void
    {
        $navigation = (new Navigation)->add('landings', 'catches.landings', 'icon-download', NavigationGroup::Registry, folder: NavigationFolder::EInspection);

        $this->assertSame([NavigationGroup::Overview->label()], $navigation->treeFor(null)->keys()->all());
        $this->assertSame(NavigationGroup::Overview, $navigation->groupsFor(null)->first()->first()->group);
    }

    public function test_folders_nest_and_sort_with_their_siblings(): void
    {
        $navigation = (new Navigation)
            ->add('vessels', 'vessels.index', 'icon-anchor', NavigationGroup::Surveillance, order: 10)
            ->add('beacons', 'tracking.beacons', 'icon-radio', NavigationGroup::Surveillance, order: 1, folder: NavigationFolder::MorningBoard)
            ->add('slots', 'tracking.slots.index', 'icon-camera', NavigationGroup::Surveillance, order: 5, folder: NavigationFolder::Logbook)
            ->add('alerts', 'tracking.alerts.index', 'icon-alert', NavigationGroup::Surveillance, order: 4, folder: NavigationFolder::Logbook)
            ->add('licenses', 'licenses.index', 'icon-file', NavigationGroup::Surveillance, order: 40);

        $nodes = $navigation->treeFor(null)->first();

        $this->assertSame(['vessels', null, 'licenses'], $nodes->map(fn ($node) => $node->item?->label)->all());
        $morning = $nodes[1];
        $this->assertSame(NavigationFolder::MorningBoard, $morning->folder);
        $this->assertSame(['beacons', null], $morning->children->map(fn ($node) => $node->item?->label)->all());
        $this->assertSame(['alerts', 'slots'], $morning->children[1]->children->map(fn ($node) => $node->item->label)->all());
    }

    public function test_a_folder_without_visible_children_is_hidden(): void
    {
        $navigation = (new Navigation)->add('landings', 'catches.landings', 'icon-download', NavigationGroup::Overview, permission: 'catches.view', folder: NavigationFolder::EInspection);

        $this->assertTrue($navigation->treeFor(null)->isEmpty());
    }

    public function test_a_node_knows_whether_it_contains_the_active_item_and_its_first_link(): void
    {
        $navigation = (new Navigation)
            ->add('alerts', 'tracking.alerts.index', 'icon-alert', NavigationGroup::Surveillance, order: 4, folder: NavigationFolder::Logbook)
            ->add('beacons', 'tracking.beacons', 'icon-radio', NavigationGroup::Surveillance, order: 1, folder: NavigationFolder::MorningBoard);

        $folder = $navigation->treeFor(null)->first()->first();
        $alerts = $navigation->groupsFor(null)->first()->firstWhere('label', 'alerts');
        $beacons = $navigation->groupsFor(null)->first()->firstWhere('label', 'beacons');

        $this->assertTrue($folder->isFolder());
        $this->assertTrue($folder->contains($alerts));
        $this->assertTrue($folder->contains($beacons));
        $this->assertFalse($folder->contains(null));
    }
}
