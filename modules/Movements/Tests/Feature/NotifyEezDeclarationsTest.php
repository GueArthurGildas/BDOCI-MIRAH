<?php

namespace Modules\Movements\Tests\Feature;

use App\Enums\ZoneType;
use App\Models\EezDeclaration;
use App\Models\EntryExitAlert;
use App\Models\MissedDeclaration;
use App\Models\Position;
use App\Models\Vessel;
use App\Models\Zone;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Movements\Services\MissedDeclarationDetector;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC10', partial: true)]
class NotifyEezDeclarationsTest extends TestCase
{
    use RefreshDatabase;

    private function foreignVesselInEez(string $flag = 'ES', ZoneType $zone = ZoneType::ExclusiveEconomicZone, int $minutesAgo = 30): Vessel
    {
        $vessel = Vessel::factory()->create(['flag' => $flag]);
        Position::factory()->create([
            'vessel_id' => $vessel->id,
            'zone_id' => Zone::factory()->create(['type' => $zone])->id,
            'recorded_at' => now()->subMinutes($minutesAgo),
        ]);

        return $vessel;
    }

    #[Assumes('H-4b-5')]
    public function test_a_foreign_vessel_in_the_eez_without_entry_declaration_is_a_missed_declaration(): void
    {
        $vessel = $this->foreignVesselInEez();
        $this->foreignVesselInEez('CI');
        $this->foreignVesselInEez('GH', ZoneType::PortArea);
        $this->foreignVesselInEez('FR', ZoneType::ExclusiveEconomicZone, 60 * 24);

        $detected = app(MissedDeclarationDetector::class)->detect();

        $this->assertSame([$vessel->id], $detected->pluck('vessel_id')->all());
        $this->assertSame([$vessel->id], MissedDeclaration::pluck('vessel_id')->all());
    }

    #[Assumes('H-4b-5')]
    public function test_a_vessel_with_a_current_entry_declaration_is_not_missed_and_detection_does_not_repeat(): void
    {
        $declared = $this->foreignVesselInEez();
        EezDeclaration::factory()->for($declared)->create(['received_at' => now()->subDay()]);
        $exited = $this->foreignVesselInEez('PT');
        EezDeclaration::factory()->for($exited)->create(['received_at' => now()->subDays(3)]);
        EezDeclaration::factory()->exit()->for($exited)->create(['received_at' => now()->subDays(2)]);

        app(MissedDeclarationDetector::class)->detect();
        app(MissedDeclarationDetector::class)->detect();

        $this->assertSame([$exited->id], MissedDeclaration::pluck('vessel_id')->all());
    }

    public function test_declaration_viewers_are_notified_of_missed_declarations_and_open_alerts(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $this->foreignVesselInEez();
        EntryExitAlert::factory()->count(2)->create();

        $this->artisan('movements:notify-eez-declarations')->assertSuccessful();

        $notifications = $agent->notifications()->get();
        $this->assertCount(2, $notifications);
        $this->assertEqualsCanonicalizing([1, 2], $notifications->pluck('data.params.count')->all());
    }

    public function test_the_administrator_is_not_notified(): void
    {
        $admin = $this->signInAs(Role::Admin);
        $this->foreignVesselInEez();

        $this->artisan('movements:notify-eez-declarations')->assertSuccessful();

        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_nothing_is_sent_when_there_is_nothing_to_report(): void
    {
        $agent = $this->signInAs(Role::Agent);

        $this->artisan('movements:notify-eez-declarations')->assertSuccessful();

        $this->assertSame(0, $agent->notifications()->count());
    }

    public function test_nothing_is_sent_when_the_alert_is_disabled_but_detection_continues(): void
    {
        config(['surveillance.alerts.eez_declarations.enabled' => false]);
        $agent = $this->signInAs(Role::Agent);
        $this->foreignVesselInEez();

        $this->artisan('movements:notify-eez-declarations')->assertSuccessful();

        $this->assertSame(0, $agent->notifications()->count());
        $this->assertSame(1, MissedDeclaration::count());
    }

    public function test_a_new_notification_is_sent_after_the_cooldown(): void
    {
        $agent = $this->signInAs(Role::Agent);
        EntryExitAlert::factory()->create();

        $this->artisan('movements:notify-eez-declarations');
        $this->artisan('movements:notify-eez-declarations');
        $this->assertSame(1, $agent->notifications()->count());

        $this->travel(config('surveillance.alerts.eez_declarations.cooldown_hours') + 1)->hours();
        $this->artisan('movements:notify-eez-declarations');

        $this->assertSame(2, $agent->notifications()->count());
    }
}
