<?php

namespace Modules\Movements\Tests\Feature;

use App\Enums\EezDeclarationStatus;
use App\Enums\EntryExitAlertStatus;
use App\Enums\EntryExitExplanation;
use App\Models\EezDeclaration;
use App\Models\EntryExitAlert;
use App\Models\EntryExitLink;
use App\Models\MissedDeclaration;
use App\Models\VesselEvent;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC10')]
class EezDeclarationFollowUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_agent_acknowledges_then_closes_a_declaration(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $declaration = EezDeclaration::factory()->create(['status' => EezDeclarationStatus::Verified]);

        $this->post(route('movements.eez.acknowledge', $declaration))->assertRedirect();
        $this->assertSame(EezDeclarationStatus::Acknowledged, $declaration->fresh()->status);

        $this->post(route('movements.eez.close', $declaration))->assertRedirect();
        $this->assertSame(EezDeclarationStatus::Closed, $declaration->fresh()->status);

        $this->assertSame(['eez_declaration.acknowledged', 'eez_declaration.closed'], VesselEvent::orderBy('id')->pluck('type')->all());
        $this->assertSame($agent->id, Activity::where('event', 'movements.close_eez_declaration')->sole()->causer_id);
    }

    public function test_acknowledging_keeps_the_variance_status(): void
    {
        $this->signInAs(Role::Agent);
        $declaration = EezDeclaration::factory()->exit()->create(['status' => EezDeclarationStatus::Variance]);

        $this->post(route('movements.eez.acknowledge', $declaration));

        $this->assertSame(EezDeclarationStatus::Variance, $declaration->fresh()->status);
    }

    public function test_a_variance_is_explained(): void
    {
        $this->signInAs(Role::TeamLead);
        $link = EntryExitLink::factory()->create(['variance_kg' => 300, 'explanation' => EntryExitExplanation::Unexplained]);

        $this->post(route('movements.eez.explain', $link), ['explanation' => 'transshipment'])->assertRedirect();
        $this->post(route('movements.eez.explain', $link), ['explanation' => 'nonsense'])->assertSessionHasErrors('explanation');

        $this->assertSame(EntryExitExplanation::Transshipment, $link->fresh()->explanation);
    }

    public function test_an_entry_exit_alert_is_closed(): void
    {
        $this->signInAs(Role::Agent);
        $alert = EntryExitAlert::factory()->create();

        $this->post(route('movements.eez.alerts.close', $alert))->assertRedirect();

        $this->assertSame(EntryExitAlertStatus::Closed, $alert->fresh()->status);
        $this->assertSame('entry_exit_alert.closed', VesselEvent::sole()->type);
    }

    public function test_a_missed_declaration_is_qualified_and_reminded(): void
    {
        $this->signInAs(Role::Agent);
        $missed = MissedDeclaration::factory()->create();

        $this->post(route('movements.eez.missed.remind', $missed))->assertRedirect();
        $this->post(route('movements.eez.missed.remind', $missed))->assertRedirect();
        $this->post(route('movements.eez.missed.qualify', $missed), ['qualification' => 'Silence du capitaine'])->assertRedirect();
        $this->post(route('movements.eez.missed.qualify', $missed), ['qualification' => ''])->assertSessionHasErrors('qualification');

        $missed->refresh();
        $this->assertSame(2, $missed->reminders);
        $this->assertSame('Silence du capitaine', $missed->qualification);
        $this->assertSame(3, VesselEvent::count());
    }

    public function test_the_administrator_cannot_follow_up_declarations(): void
    {
        $this->signInAs(Role::Admin);
        $declaration = EezDeclaration::factory()->create();
        $alert = EntryExitAlert::factory()->create();
        $missed = MissedDeclaration::factory()->create();

        $this->post(route('movements.eez.acknowledge', $declaration))->assertForbidden();
        $this->post(route('movements.eez.alerts.close', $alert))->assertForbidden();
        $this->post(route('movements.eez.missed.remind', $missed))->assertForbidden();
        $this->get(route('movements.eez.index'))->assertForbidden();
    }
}
