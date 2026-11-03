<?php

namespace Tests\Feature;

use App\Models\AlertRule;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ErgonomicsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_table_without_server_pagination_is_paginated_by_five_in_the_browser(): void
    {
        $html = Blade::render('<x-ui.table><x-slot:head><th>A</th></x-slot:head><tr><td>1</td></tr></x-ui.table>');

        $this->assertStringContainsString('data-gs-paginate="5"', $html);
    }

    public function test_a_table_paginated_by_the_server_or_opted_out_is_left_alone(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $served = Blade::render('<x-ui.table :paginator="$paginator"><x-slot:head><th>A</th></x-slot:head></x-ui.table>', ['paginator' => $paginator]);
        $optedOut = Blade::render('<x-ui.table :paginate="false"><x-slot:head><th>A</th></x-slot:head></x-ui.table>');

        $this->assertStringNotContainsString('data-gs-paginate', $served);
        $this->assertStringNotContainsString('data-gs-paginate', $optedOut);
    }

    public function test_big_lists_show_ten_rows_per_page_by_default(): void
    {
        $this->signInAs(Role::Director);
        Vessel::factory()->count(12)->create();

        foreach (['vessels.index' => 'vessels', 'licenses.index' => 'licenses', 'infractions.index' => 'cases', 'movements.index' => 'movements'] as $route => $variable) {
            $data = $this->get(route($route))->assertOk()->original->getData();
            $paginator = collect($data)->first(fn ($value) => $value instanceof LengthAwarePaginator);

            $this->assertNotNull($paginator, $route);
            $this->assertSame(10, $paginator->perPage(), $route);
        }
    }

    public function test_the_page_carries_the_labels_used_by_the_browser_components(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('vessels.index'))
            ->assertSee('data-gs-no-results="'.__('ui.no_results').'"', false)
            ->assertSee('data-gs-pager-range=', false)
            ->assertSee('data-gs-select-placeholder=', false);
    }

    public function test_the_three_notions_are_explained_where_they_are_used(): void
    {
        $this->signInAs(Role::Director);

        foreach (['tracking.alerts.index', 'communications.messages.index', 'notifications.index'] as $route) {
            $this->get(route($route))->assertOk()->assertSee(__('ui.notions.title'))->assertSee(__('ui.notions.notification.name'));
        }
    }

    public function test_the_alert_rule_label_is_translated_with_the_code_as_fallback(): void
    {
        $known = new AlertRule(['code' => 'silence_vms']);
        $unknown = new AlertRule(['code' => 'custom_rule']);

        $this->assertSame(__('tracking::tracking.rules.codes.silence_vms'), $known->label());
        $this->assertSame('custom_rule', $unknown->label());
    }
}
