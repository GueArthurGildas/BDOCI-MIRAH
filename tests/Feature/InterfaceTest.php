<?php

namespace Tests\Feature;

use App\Http\Middleware\ModalFormRedirects;
use App\Models\LogbookDay;
use App\Models\Observation;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Models\Position;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\LogbookDayDocument;
use App\Support\Navigation\Navigation;
use App\Support\Navigation\NavigationItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_form_sent_from_a_modal_gets_its_redirect_as_json_and_keeps_the_flash_message(): void
    {
        $this->signInAs(Role::Agent);

        $response = $this->withHeaders([ModalFormRedirects::HEADER => '1', 'Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('logbook.store'), [
                'observed_at' => now()->format('Y-m-d H:i'),
                'level' => 'info',
                'description' => 'Observation envoyée depuis la fenêtre.',
            ]);

        $response->assertOk()->assertJsonStructure(['redirect']);
        $this->assertStringContainsString('/logbook/', $response->json('redirect'));
        $this->assertSame(__('logbook::logbook.created'), session('status'));
    }

    public function test_validation_errors_of_a_modal_form_come_back_as_json(): void
    {
        $this->signInAs(Role::Agent);

        $this->withHeaders([ModalFormRedirects::HEADER => '1', 'Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('logbook.store'), ['level' => 'info'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['observed_at', 'description']);
    }

    public function test_a_plain_form_still_redirects(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('logbook.store'), [
            'observed_at' => now()->format('Y-m-d H:i'),
            'level' => 'info',
            'description' => 'Observation en pleine page.',
        ])->assertRedirect();
    }

    public function test_the_modal_shell_and_the_required_field_legend_are_on_every_page(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('logbook.create'))
            ->assertOk()
            ->assertSee('id="gs-form-modal"', false)
            ->assertSee(__('ui.required_fields'))
            ->assertSee('gs-required', false);
    }

    public function test_the_morning_menu_groups_the_screens_of_a_watch_day_by_permission(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee(__('ui.morning_menu.title'))
            ->assertSee(__('ui.morning_menu.sections.shift'))
            ->assertSee(route('logbook.handovers.create'))
            ->assertSee(route('operations.index'))
            ->assertDontSee(route('witnesses.distributions.index'));

        $this->signInAs(Role::TeamLead);
        $this->get(route('profile.show'))->assertSee(route('witnesses.distributions.index'));
    }

    public function test_the_morning_menu_is_hidden_without_business_permission(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('profile.show'))->assertOk()->assertDontSee(__('ui.morning_menu.hint'));
    }

    public function test_every_page_has_a_refresh_button_and_asks_to_confirm_each_record(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('logbook.create'))
            ->assertOk()
            ->assertSee('data-gs-refresh', false)
            ->assertSee('data-gs-confirm-record="'.e(__('ui.confirm.record')).'"', false)
            ->assertSee('data-gs-record', false);
    }

    public function test_the_sidebar_lists_each_screen_once(): void
    {
        $user = $this->signInAs(Role::Director);

        $routes = app(Navigation::class)->groupsFor($user)->flatten(1)->map(fn (NavigationItem $item) => $item->route);

        $this->assertSame($routes->count(), $routes->unique()->count(), 'Duplicated: '.$routes->duplicates()->join(', '));
    }

    public function test_the_situation_map_offers_to_record_from_a_vessel_or_a_spot(): void
    {
        $this->signInAs(Role::Agent);
        $position = Position::factory()->create(['recorded_at' => now()->subMinutes(5)]);
        Observation::factory()->create(['latitude' => 5.1, 'longitude' => -4.2, 'observed_at' => now()->subHour()]);

        $this->get(route('tracking.map'))
            ->assertOk()
            ->assertViewHas('markers', fn (array $markers) => collect($markers)->contains(fn (array $marker) => collect($marker['actions'] ?? [])->contains(fn (array $action) => str_contains($action['url'], 'vessel_id='.$position->vessel_id)))
                && collect($markers)->contains(fn (array $marker) => ($marker['square'] ?? false) === true))
            ->assertViewHas('create', fn (?array $create) => $create !== null && $create['url'] === route('logbook.create'));

        $this->get(route('logbook.create', ['vessel_id' => $position->vessel_id, 'latitude' => 5.25, 'longitude' => -4.5]))
            ->assertOk()
            ->assertSee('value="5.25"', false);
    }

    public function test_printed_logbooks_say_whether_they_are_closed(): void
    {
        $this->freezeTime();
        $day = LogbookDay::factory()->create();

        $texts = collect(app(LogbookDayDocument::class)->build($day)->blocks())->flatMap(fn (array $block) => $block['rows'] ?? [])->flatten(1)->flatMap(fn ($cell) => $cell['lines'] ?? [])->pluck('text');

        $this->assertTrue($texts->contains(fn (string $text) => $text === __('logbook::logbook.print.status_open', ['date' => now()->format('d/m/Y H:i')])));
    }

    public function test_a_detail_page_offers_a_back_button_to_its_parent_page(): void
    {
        $this->signInWithPermissions(Permission::OperationsView);
        $operation = Operation::factory()->create(['category_id' => OperationCategory::factory()->create()->id]);
        app(LogbookChannels::class)->ensure();

        $this->get(route('operations.show', $operation))
            ->assertOk()
            ->assertSee('data-gs-back', false)
            ->assertSee('href="'.route('operations.index').'" class="btn btn-default btn-sm gs-back"', false);
    }
}
