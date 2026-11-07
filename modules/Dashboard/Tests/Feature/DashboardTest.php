<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Enums\ObservationStatus;
use App\Models\DataSource;
use App\Models\InternalNote;
use App\Models\Observation;
use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Dashboard\Support\WidgetCatalog;
use Tests\Attributes\Covers;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_users_without_permission_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/dashboard')
            ->assertForbidden();
    }

    #[Covers('UC25', partial: true)]
    public function test_dashboard_shows_key_indicators(): void
    {
        $this->signInAs(Role::Agent);
        DataSource::factory()->create(['name' => 'VMS / THEMIS']);
        Observation::factory()->create(['description' => 'Rupture d\'émission à surveiller', 'status' => ObservationStatus::Open]);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('VMS / THEMIS')
            ->assertSee('Rupture d\'émission à surveiller', escape: true)
            ->assertViewHas('kpis', fn ($kpis) => $kpis->first(fn ($data) => $data->widget->key === 'pending_observations')->value === 1);
    }

    public function test_dashboard_is_highlighted_in_the_sidebar(): void
    {
        $this->signInAs(Role::Agent);

        $this->get('/dashboard')
            ->assertSee('<li class="active">', escape: false)
            ->assertSee(route('dashboard'));
    }

    public function test_dashboard_shows_the_messages_addressed_to_the_user(): void
    {
        $user = $this->signInAs(Role::Agent);
        InternalNote::factory()->create(['recipient_user_id' => $user->id, 'free_text' => 'Vérifier la balise du navire', 'is_read' => false]);
        InternalNote::factory()->create(['free_text' => 'Message pour quelqu\'un d\'autre']);

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Vérifier la balise du navire')
            ->assertDontSee('Message pour quelqu\'un d\'autre');
    }

    public function test_user_chooses_the_widgets_to_display(): void
    {
        $user = $this->signInWithPermissions(Permission::DashboardView, Permission::VesselsView, Permission::LicensesView);

        $this->get('/dashboard')->assertViewHas('kpis', fn ($kpis) => $kpis->pluck('widget.key')->all() === ['active_vessels']);

        $this->put(route('dashboard.widgets.update'), ['widgets' => ['licenses_by_status', 'vessels_by_flag']])->assertRedirect(route('dashboard'));

        $this->assertSame(['vessels_by_flag', 'licenses_by_status'], $user->fresh()->dashboard_widgets);
        $this->get('/dashboard')
            ->assertViewHas('kpis', fn ($kpis) => $kpis->isEmpty())
            ->assertViewHas('charts', fn ($charts) => $charts->count() === 2);
    }

    public function test_user_cannot_select_a_widget_they_have_no_permission_for(): void
    {
        $user = $this->signInWithPermissions(Permission::DashboardView);

        $this->put(route('dashboard.widgets.update'), ['widgets' => ['licenses_by_status']])->assertSessionHasErrors('widgets.0');

        $this->assertNull($user->fresh()->dashboard_widgets);
    }

    public function test_user_restores_the_default_selection(): void
    {
        $user = $this->signInWithPermissions(Permission::DashboardView, Permission::VesselsView);
        $user->update(['dashboard_widgets' => []]);

        $this->delete(route('dashboard.widgets.reset'))->assertRedirect(route('dashboard'));

        $this->assertNull($user->fresh()->dashboard_widgets);
    }

    public function test_customize_modal_lists_only_the_available_widgets(): void
    {
        $this->signInWithPermissions(Permission::DashboardView, Permission::LicensesView);

        $this->get(route('dashboard.customize'))
            ->assertOk()
            ->assertSee('value="licenses_by_status"', escape: false)
            ->assertDontSee('value="vessels_by_flag"', escape: false);
    }

    public function test_widget_detail_explains_the_indicator_and_links_to_its_list(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('dashboard.widgets.show', 'silent_beacons'))
            ->assertOk()
            ->assertSee(__('indicators::indicators.descriptions.silent_beacons'))
            ->assertSee(route('indicators.show', 'silent_beacons'), escape: false)
            ->assertSee(__('dashboard::dashboard.details.see_more'));
    }

    public function test_chart_detail_shows_the_figures(): void
    {
        $this->signInAs(Role::Director);

        $this->get(route('dashboard.widgets.show', 'licenses_by_status'))
            ->assertOk()
            ->assertSee(__('dashboard::dashboard.details.figures'))
            ->assertSee(route('licenses.index'), escape: false);
    }

    public function test_widget_detail_is_hidden_without_permission(): void
    {
        $this->signInWithPermissions(Permission::DashboardView);

        $this->get(route('dashboard.widgets.show', 'silent_beacons'))->assertNotFound();
        $this->get(route('dashboard.widgets.show', 'unknown'))->assertNotFound();
    }

    public function test_every_widget_can_be_built_for_the_director(): void
    {
        $this->signInAs(Role::Director);
        $catalog = app(WidgetCatalog::class);

        foreach ($catalog->all() as $widget) {
            $this->get(route('dashboard.widgets.show', $widget->key))->assertOk();
        }

        $this->put(route('dashboard.widgets.update'), ['widgets' => $catalog->all()->keys()->all()]);
        $this->get('/dashboard')->assertOk();
    }
}
