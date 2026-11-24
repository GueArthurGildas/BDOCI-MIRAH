<?php

namespace Modules\Vessels\Tests\Feature;

use App\Enums\OperationStatus;
use App\Models\LogbookEntry;
use App\Models\Operation;
use App\Models\ReportedVessel;
use App\Models\Vessel;
use App\Models\WitnessReport;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Covers;
use Tests\TestCase;

class VesselLogbookTest extends TestCase
{
    use RefreshDatabase;

    #[Covers('UC01', partial: true)]
    public function test_the_vessel_file_lists_its_logbook_lines_and_operations(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        $entry = LogbookEntry::factory()->create(['content' => 'Appel radio au capitaine.']);
        $entry->vessels()->attach($vessel);
        LogbookEntry::factory()->create(['content' => 'Ligne d\'un autre navire.']);
        $operation = Operation::factory()->create(['status' => OperationStatus::InProgress]);
        $operation->vessels()->attach($vessel);

        $this->get(route('vessels.show', [$vessel, 'tab' => 'logbook']))
            ->assertOk()
            ->assertSee(__('vessels::vessels.tabs.logbook'))
            ->assertSee('Appel radio au capitaine.')
            ->assertDontSee('Ligne d\'un autre navire.')
            ->assertSee(route('logbook.days.show', $entry->logbook_day_id).'#entry-'.$entry->id, false)
            ->assertSee($operation->code)
            ->assertSee(route('operations.show', $operation));

        $this->get(route('vessels.show', [$vessel, 'view' => 'folders', 'pane' => 'logbook']))
            ->assertOk()
            ->assertSee('Appel radio au capitaine.');
    }

    #[Covers('UC01', partial: true)]
    public function test_the_logbook_tab_follows_the_logbook_permission(): void
    {
        $this->signInWithPermissions(Permission::VesselsView);
        $vessel = Vessel::factory()->create();
        $entry = LogbookEntry::factory()->create(['content' => 'Ligne confidentielle.']);
        $entry->vessels()->attach($vessel);

        $this->get(route('vessels.show', $vessel))->assertOk()->assertDontSee('Ligne confidentielle.');
    }

    #[Covers('UC01', 'UC15', partial: true)]
    public function test_the_observations_tab_lists_the_visual_sighting_reports_of_the_vessel(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        $report = WitnessReport::factory()->create();
        ReportedVessel::create(['witness_report_id' => $report->id, 'rank' => 1, 'vessel_id' => $vessel->id, 'name' => $vessel->name]);

        $this->get(route('vessels.show', [$vessel, 'tab' => 'observations']))
            ->assertOk()
            ->assertSee(route('witnesses.show', $report));
    }

    public function test_the_vessel_sheet_still_renders_with_its_logbook_section(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        LogbookEntry::factory()->create()->vessels()->attach($vessel);

        $this->get(route('vessels.sheet', $vessel))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }
}
