<?php

namespace Tests\Feature;

use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationActiveItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_current_sidebar_item_is_active(): void
    {
        $this->signInAs(Role::Admin);

        $html = $this->get(route('administration.users.index'))->getContent();

        $this->assertSame(1, substr_count($html, '<li class="active">'));
    }

    public function test_page_header_icon_follows_the_active_navigation_item(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('administration.users.index'))->assertSee('icon-users');
    }
}
