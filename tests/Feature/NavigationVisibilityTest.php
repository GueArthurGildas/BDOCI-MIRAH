<?php

namespace Tests\Feature;

use App\Support\Authorization\Role;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_are_only_visible_to_users_holding_their_permission(): void
    {
        $navigation = (new Navigation)
            ->add('users', 'dashboard', 'icon-users', NavigationGroup::Administration, permission: 'users.manage');

        $this->assertTrue($navigation->groupsFor($this->signInAs(Role::Agent))->isEmpty());
        $this->assertCount(1, $navigation->groupsFor($this->signInAs(Role::Admin)));
    }
}
