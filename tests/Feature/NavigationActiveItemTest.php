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

    public function test_a_sibling_page_under_the_same_prefix_does_not_activate_the_parent_item(): void
    {
        $this->signInAs(Role::Director);

        $html = $this->get(route('reports.ai-agent.index'))->getContent();

        $this->assertSame(1, substr_count($html, '<li class="active">'));
        $this->assertMatchesRegularExpression('#<li class="active">\s*<a href="'.preg_quote(route('reports.ai-agent.index'), '#').'"#', $html);
    }

    public function test_a_detail_page_keeps_its_list_item_active(): void
    {
        $this->signInAs(Role::Admin);

        $html = $this->get(route('administration.users.create'))->getContent();

        $this->assertMatchesRegularExpression('#<li class="active">\s*<a href="'.preg_quote(route('administration.users.index'), '#').'"#', $html);
    }

    public function test_the_exact_route_wins_over_a_prefix_match(): void
    {
        $this->signInAs(Role::Admin);

        $html = $this->get(route('communications.ai-agent.edit'))->getContent();

        $this->assertMatchesRegularExpression('#<li class="active">\s*<a href="'.preg_quote(route('communications.ai-agent.edit'), '#').'"#', $html);
    }
}
