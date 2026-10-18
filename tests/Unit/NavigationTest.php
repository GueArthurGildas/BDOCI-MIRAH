<?php

namespace Tests\Unit;

use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;
use PHPUnit\Framework\TestCase;

class NavigationTest extends TestCase
{
    public function test_items_are_grouped_in_group_order_and_sorted_within_groups(): void
    {
        $navigation = (new Navigation)
            ->add('vessels', 'vessels.index', 'icon-anchor', NavigationGroup::Registry)
            ->add('map', 'tracking.map', 'icon-map', NavigationGroup::Surveillance, order: 20)
            ->add('logbook', 'logbook.index', 'icon-book', NavigationGroup::Surveillance, order: 10);

        $groups = $navigation->groupsFor(null);

        $this->assertSame([NavigationGroup::Surveillance->label(), NavigationGroup::Registry->label()], $groups->keys()->all());
        $this->assertSame(['logbook', 'map'], $groups->first()->pluck('label')->all());
    }
}
