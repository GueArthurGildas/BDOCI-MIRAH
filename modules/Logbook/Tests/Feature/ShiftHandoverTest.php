<?php

namespace Modules\Logbook\Tests\Feature;

use App\Enums\OperationCategoryCode;
use App\Models\LogbookDay;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Models\ShiftHandover;
use App\Models\User;
use App\Support\Authorization\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Covers;
use Tests\TestCase;

class ShiftHandoverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setTime(8, 12));
    }

    #[Covers('UC12', 'RG10', partial: true)]
    public function test_any_agent_records_the_handover_with_a_frozen_snapshot_of_the_operations(): void
    {
        $outgoing = $this->signInAs(Role::Agent);
        $incoming = User::factory()->create(['first_name' => 'Mariam', 'name' => 'CONDE']);
        $manager = User::factory()->create(['first_name' => 'Maxime', 'name' => 'DIOMANDE']);
        $mep = OperationCategory::factory()->create(['code' => OperationCategoryCode::Mep]);
        Operation::factory()->for($mep, 'category')->create(['code' => 'OPS-326-MEP-2026-PACIFIC_PRIDE', 'number' => 326]);
        Operation::factory()->for($mep, 'category')->create(['code' => 'OPS-320-MEP-2026-OLD', 'number' => 320, 'status' => 'closed', 'ended_at' => now()->subDay()]);
        DB::table('operation_counters')->insert(['category_id' => $mep->id, 'year' => now()->year, 'last_number' => 328]);

        $this->get(route('logbook.handovers.create'))->assertOk()->assertSee('328')->assertSee('OPS-326-MEP-2026-PACIFIC_PRIDE', false);

        $this->post(route('logbook.handovers.store'), [
            'taken_over_by' => $incoming->id,
            'supervised_by' => $manager->id,
            'instructions' => "L'étude de la DPEP pour l'OPS 326/MEP est à faire en priorité.",
        ])->assertRedirect(route('logbook.days.show', LogbookDay::sole()));

        $handover = ShiftHandover::sole();
        $this->assertSame(LogbookDay::sole()->id, $handover->logbook_day_id);
        $this->assertSame(now()->toDateString(), $handover->day->day->toDateString());
        $this->assertSame($outgoing->id, $handover->handed_over_by);
        $this->assertSame($incoming->id, $handover->taken_over_by);
        $this->assertSame($manager->id, $handover->supervised_by);
        $this->assertSame(328, $handover->operation_counters['mep']);
        $this->assertSame(0, $handover->operation_counters['vms']);
        $this->assertSame(['OPS-326-MEP-2026-PACIFIC_PRIDE'], $handover->open_operations['mep']);
        $this->assertSame([], $handover->open_operations['inn']);
        $this->assertSame($outgoing->id, Activity::where('event', 'logbook.record_handover')->sole()->causer_id);

        Operation::where('number', 326)->update(['status' => 'closed']);
        $this->assertSame(['OPS-326-MEP-2026-PACIFIC_PRIDE'], $handover->fresh()->open_operations['mep']);

        $this->get(route('logbook.days.show', LogbookDay::sole()))->assertOk()
            ->assertSee('Mariam CONDE')
            ->assertSee('Maxime DIOMANDE')
            ->assertSee("L'étude de la DPEP pour l'OPS 326/MEP est à faire en priorité.");
    }

    public function test_the_evening_handover_goes_into_the_day_that_started_in_the_morning(): void
    {
        $this->signInAs(Role::Agent);
        $incoming = User::factory()->create();

        $this->travelTo(now()->setTime(18, 5));
        $this->post(route('logbook.handovers.store'), ['taken_over_by' => $incoming->id])->assertSessionHasNoErrors();

        $this->travelTo(now()->addDay()->setTime(7, 55));
        $this->post(route('logbook.handovers.store'), ['taken_over_by' => $incoming->id])->assertSessionHasNoErrors();

        $this->assertSame(1, LogbookDay::count());
        $this->assertSame(2, LogbookDay::sole()->handovers()->count());
    }

    public function test_the_handover_is_given_to_someone_else_and_validates_its_input(): void
    {
        $agent = $this->signInAs(Role::Agent);

        $this->post(route('logbook.handovers.store'), ['taken_over_by' => $agent->id])->assertSessionHasErrors('taken_over_by');
        $this->post(route('logbook.handovers.store'), [])->assertSessionHasErrors('taken_over_by');
        $this->post(route('logbook.handovers.store'), ['taken_over_by' => User::factory()->create(['is_active' => false])->id])->assertSessionHasErrors('taken_over_by');

        $this->assertSame(0, ShiftHandover::count());
    }

    public function test_a_handover_cannot_be_written_into_a_locked_day(): void
    {
        $this->signInAs(Role::Agent);
        LogbookDay::factory()->locked()->create(['day' => now()->toDateString(), 'shift_start_at' => now()->setTime(8, 0), 'shift_end_at' => now()->addDay()->setTime(8, 0)]);

        $this->post(route('logbook.handovers.store'), ['taken_over_by' => User::factory()->create()->id])->assertSessionHasErrors('handed_over_at');

        $this->assertSame(0, ShiftHandover::count());
    }

    public function test_administrators_cannot_record_a_handover(): void
    {
        $this->signInAs(Role::Admin);

        $this->get(route('logbook.handovers.create'))->assertForbidden();
        $this->post(route('logbook.handovers.store'), ['taken_over_by' => User::factory()->create()->id])->assertForbidden();
    }
}
