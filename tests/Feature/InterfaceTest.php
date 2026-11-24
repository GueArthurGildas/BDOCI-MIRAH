<?php

namespace Tests\Feature;

use App\Http\Middleware\ModalFormRedirects;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Logbook\LogbookChannels;
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
