<?php

namespace Modules\Movements\Tests\Feature;

use App\Enums\EezDeclarationStatus;
use App\Models\EezDeclaration;
use App\Models\EezDeclarationCheck;
use App\Models\EntryExitAlert;
use App\Models\EntryExitLink;
use App\Models\MissedDeclaration;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC10', partial: true)]
class EezPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('movements.eez.index'))->assertRedirect(route('login'));
    }

    public function test_declarations_can_be_filtered_by_type_and_status(): void
    {
        $this->signInAs(Role::Agent);
        EezDeclaration::factory()->create(['vessel_id' => Vessel::factory()->create(['name' => 'ENTRY VESSEL', 'flag' => 'ES'])->id]);
        EezDeclaration::factory()->exit()->create(['vessel_id' => Vessel::factory()->create(['name' => 'EXIT VESSEL', 'flag' => 'ES'])->id, 'status' => EezDeclarationStatus::Closed]);

        $this->get(route('movements.eez.index', ['type' => 'entry']))->assertOk()->assertSee('ENTRY VESSEL')->assertDontSee('EXIT VESSEL');
        $this->get(route('movements.eez.index', ['status' => 'closed']))->assertOk()->assertSee('EXIT VESSEL')->assertDontSee('ENTRY VESSEL');
        $this->get(route('movements.eez.index', ['search' => 'ENTRY']))->assertOk()->assertSee('ENTRY VESSEL')->assertDontSee('EXIT VESSEL');
    }

    public function test_the_detail_page_shows_checks_and_pairing(): void
    {
        $this->signInAs(Role::Agent);
        $link = EntryExitLink::factory()->create(['variance_kg' => 1200]);
        EezDeclarationCheck::factory()->create(['eez_declaration_id' => $link->exit_declaration_id, 'check_type' => 'vms', 'result' => 'ko', 'comment' => 'silent_beacon']);

        $this->get(route('movements.eez.show', $link->exit))
            ->assertOk()
            ->assertSee(__('movements::eez.check_comments.silent_beacon'))
            ->assertSee(route('movements.eez.show', $link->entry));
    }

    public function test_the_create_form_is_only_offered_to_users_who_can_declare(): void
    {
        $this->signInAs(Role::Agent);
        $this->get(route('movements.eez.index'))->assertOk()->assertSee(route('movements.eez.create'));
        $this->get(route('movements.eez.create'))->assertOk();
    }

    public function test_alerts_default_to_open_ones(): void
    {
        $this->signInAs(Role::Agent);
        EntryExitAlert::factory()->create(['vessel_id' => Vessel::factory()->create(['name' => 'OPEN ALERT', 'flag' => 'ES'])->id]);
        EntryExitAlert::factory()->create(['vessel_id' => Vessel::factory()->create(['name' => 'CLOSED ALERT', 'flag' => 'ES'])->id, 'status' => 'closed']);

        $this->get(route('movements.eez.alerts'))->assertOk()->assertSee('OPEN ALERT')->assertDontSee('CLOSED ALERT');
        $this->get(route('movements.eez.alerts', ['status' => 'closed']))->assertSee('CLOSED ALERT')->assertDontSee('OPEN ALERT');
    }

    public function test_missed_declarations_are_listed_until_a_declaration_resolves_them(): void
    {
        $this->signInAs(Role::Agent);
        $pending = MissedDeclaration::factory()->create(['vessel_id' => Vessel::factory()->create(['name' => 'PENDING VESSEL', 'flag' => 'ES'])->id]);
        $resolvedVessel = Vessel::factory()->create(['name' => 'RESOLVED VESSEL', 'flag' => 'ES']);
        MissedDeclaration::factory()->create(['vessel_id' => $resolvedVessel->id, 'detected_at' => now()->subDay()]);
        EezDeclaration::factory()->for($resolvedVessel)->create(['received_at' => now()->subHour()]);

        $this->get(route('movements.eez.missed.index'))->assertOk()->assertSee('PENDING VESSEL')->assertDontSee('RESOLVED VESSEL');
        $this->get(route('movements.eez.missed.index', ['resolved' => 1]))->assertSee('RESOLVED VESSEL')->assertDontSee('PENDING VESSEL');
        $this->assertFalse($pending->isResolved());
    }

    public function test_the_port_movement_pages_remain_available(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('movements.index'))->assertOk();
    }
}
