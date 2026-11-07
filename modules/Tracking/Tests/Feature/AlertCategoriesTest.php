<?php

namespace Modules\Tracking\Tests\Feature;

use App\Enums\AlertCategory;
use App\Enums\AlertRuleType;
use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Models\Vessel;
use App\Models\VesselCurrentState;
use App\Models\WatchEntry;
use App\Support\Alerts\AlertTriage;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_open_restricted_zone_alert_is_urgent(): void
    {
        $user = $this->signInAs(Role::Agent);
        $alert = Alert::factory()->create(['type' => AlertRuleType::Geofencing, 'status' => AlertStatus::Open]);

        $this->assertSame(AlertCategory::Urgent, AlertTriage::categorize(collect([$alert]), $user)[$alert->id]);
    }

    public function test_an_open_alert_on_a_vessel_in_violation_is_urgent_but_not_once_acknowledged(): void
    {
        $user = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        VesselCurrentState::updateOrCreate(['vessel_id' => $vessel->id], ['status_light' => 'in_violation']);
        $open = Alert::factory()->create(['vessel_id' => $vessel->id, 'status' => AlertStatus::Open]);
        $acknowledged = Alert::factory()->create(['vessel_id' => $vessel->id, 'status' => AlertStatus::Acknowledged]);

        $categories = AlertTriage::categorize(collect([$open, $acknowledged]), $user);

        $this->assertSame(AlertCategory::Urgent, $categories[$open->id]);
        $this->assertNotSame(AlertCategory::Urgent, $categories[$acknowledged->id]);
    }

    public function test_an_alert_on_a_watched_vessel_or_acknowledged_by_the_user_is_useful(): void
    {
        $user = $this->signInAs(Role::Agent);
        $watched = Vessel::factory()->create();
        WatchEntry::factory()->create(['vessel_id' => $watched->id, 'status' => 'open']);
        $onWatch = Alert::factory()->create(['vessel_id' => $watched->id, 'type' => AlertRuleType::Silence]);
        $mine = Alert::factory()->create(['status' => AlertStatus::Acknowledged, 'acknowledged_by' => $user->id]);

        $categories = AlertTriage::categorize(collect([$onWatch, $mine]), $user);

        $this->assertSame(AlertCategory::Useful, $categories[$onWatch->id]);
        $this->assertSame(AlertCategory::Useful, $categories[$mine->id]);
    }

    public function test_other_alerts_and_closed_alerts_are_in_the_last_category(): void
    {
        $user = $this->signInAs(Role::Agent);
        $plain = Alert::factory()->create(['type' => AlertRuleType::Speed]);
        $closed = Alert::factory()->create(['type' => AlertRuleType::Geofencing, 'status' => AlertStatus::Closed]);

        $categories = AlertTriage::categorize(collect([$plain, $closed]), $user);

        $this->assertSame(AlertCategory::Other, $categories[$plain->id]);
        $this->assertSame(AlertCategory::Other, $categories[$closed->id]);
    }

    public function test_every_alert_belongs_to_exactly_one_category(): void
    {
        $user = $this->signInAs(Role::Agent);
        Alert::factory()->count(4)->create(['type' => AlertRuleType::Geofencing]);
        Alert::factory()->count(3)->create(['type' => AlertRuleType::Speed]);
        Alert::factory()->create(['status' => AlertStatus::Closed]);

        $total = array_sum(array_map(fn (AlertCategory $category) => AlertTriage::apply(Alert::query(), $category, $user)->count(), AlertCategory::cases()));

        $this->assertSame(Alert::count(), $total);
    }

    public function test_the_alert_list_filters_by_category_and_shows_the_category_of_each_row(): void
    {
        $this->signInAs(Role::Agent);
        $urgent = Alert::factory()->create(['type' => AlertRuleType::Geofencing, 'vessel_id' => Vessel::factory()->create(['name' => 'NAVIRE URGENT'])->id]);
        Alert::factory()->create(['type' => AlertRuleType::Speed, 'vessel_id' => Vessel::factory()->create(['name' => 'NAVIRE ORDINAIRE'])->id]);

        $this->get(route('tracking.alerts.index'))
            ->assertOk()
            ->assertSee('NAVIRE URGENT')
            ->assertSee('NAVIRE ORDINAIRE')
            ->assertViewHas('categories', fn (array $categories) => $categories[$urgent->id] === AlertCategory::Urgent);

        $this->get(route('tracking.alerts.index', ['category' => 'urgent']))
            ->assertSee('NAVIRE URGENT')
            ->assertDontSee('NAVIRE ORDINAIRE');

        $this->get(route('tracking.alerts.index', ['category' => 'nonsense']))->assertSessionHasErrors('category');
    }

    public function test_the_preview_lists_alerts_by_category_with_a_link_to_all_of_them(): void
    {
        $this->signInAs(Role::Agent);
        Alert::factory()->create(['type' => AlertRuleType::Geofencing, 'vessel_id' => Vessel::factory()->create(['name' => 'NAVIRE APERCU'])->id]);

        $this->get(route('tracking.alerts.preview'))
            ->assertOk()
            ->assertDontSee('<html', false)
            ->assertSee('NAVIRE APERCU')
            ->assertSee(__('enums.alert_category.urgent'))
            ->assertSee(__('tracking::tracking.alerts.see_all'))
            ->assertSee(route('tracking.alerts.index'), false);
    }

    public function test_the_preview_needs_the_tracking_permission(): void
    {
        $this->get(route('tracking.alerts.preview'))->assertRedirect(route('login'));

        $this->signInAs(Role::Admin);
        $this->get(route('tracking.alerts.preview'))->assertForbidden();
    }

    public function test_the_alerts_menu_entry_carries_the_preview_address(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('vessels.index'))->assertSee('data-gs-preview="'.route('tracking.alerts.preview').'"', false);
    }
}
