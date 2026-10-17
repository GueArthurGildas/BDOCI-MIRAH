<?php

namespace Tests\Unit;

use App\Support\Navigation\Navigation;
use PHPUnit\Framework\TestCase;

class NavigationTest extends TestCase
{
    public function test_items_are_sorted_by_order(): void
    {
        $navigation = (new Navigation)
            ->add('customers::customers.title', 'customers.index', 'icon-users', order: 20)
            ->add('dashboard::dashboard.title', 'dashboard', 'icon-home', order: 0);

        $this->assertSame(['dashboard::dashboard.title', 'customers::customers.title'], $navigation->items()->pluck('label')->all());
    }
}
