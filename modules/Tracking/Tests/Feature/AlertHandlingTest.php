<?php

namespace Modules\Tracking\Tests\Feature;

use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Models\AlertHandling;
use App\Models\Operation;
use App\Models\VesselEvent;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class AlertHandlingTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC06', partial: true)]
    public function test_the_list_shows_unresolved_alerts_and_the_detail_page_opens(): void
    {
        $this->signInAs(Role::Agent);
        $alert = Alert::factory()->create();
        Alert::factory()->create(['status' => AlertStatus::Closed]);

        $this->get(route('tracking.alerts.index'))->assertOk()->assertViewHas('alerts', fn ($alerts) => $alerts->count() === 1);
        $this->get(route('tracking.alerts.show', $alert))->assertOk()->assertSee($alert->vessel->name);
    }

    #[Covers('UC06', partial: true)]
    public function test_an_agent_acknowledges_an_alert_and_the_vessel_journal_records_it(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $alert = Alert::factory()->create();

        $this->post(route('tracking.alerts.acknowledge', $alert))->assertRedirect(route('tracking.alerts.show', $alert));

        $alert->refresh();
        $this->assertSame(AlertStatus::Acknowledged, $alert->status);
        $this->assertSame($agent->id, $alert->acknowledged_by);
        $this->assertTrue(VesselEvent::where('vessel_id', $alert->vessel_id)->where('type', 'alert.acknowledged')->where('user_id', $agent->id)->exists());
    }

    #[Covers('UC06', partial: true)]
    public function test_handling_records_the_verification_the_contact_and_the_measures_and_can_close_the_alert(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $alert = Alert::factory()->create();
        $operation = Operation::factory()->create();

        $this->post(route('tracking.alerts.handle', $alert), [
            'verification_questions' => ['position_checked' => 1],
            'is_vessel_contacted' => 1,
            'measures' => 'Captain called by VHF',
            'operation_id' => $operation->id,
            'close' => 1,
        ])->assertRedirect();

        $handling = AlertHandling::first();
        $this->assertTrue($handling->verification_questions['position_checked']);
        $this->assertFalse($handling->verification_questions['beacon_powered']);
        $this->assertTrue($handling->is_vessel_contacted);
        $this->assertSame($agent->id, $handling->handled_by);
        $this->assertSame([AlertStatus::Closed, $operation->id], [$alert->fresh()->status, $alert->fresh()->operation_id]);
    }

    #[Covers('UC06', partial: true)]
    public function test_viewers_without_the_create_permission_cannot_handle_alerts(): void
    {
        $this->signInWithPermissions(Permission::TrackingView);
        $alert = Alert::factory()->create();

        $this->post(route('tracking.alerts.acknowledge', $alert))->assertForbidden();
        $this->post(route('tracking.alerts.handle', $alert), ['measures' => 'x'])->assertForbidden();

        $this->assertSame(AlertStatus::Open, $alert->fresh()->status);
        $this->assertSame(0, AlertHandling::count());
    }
}
