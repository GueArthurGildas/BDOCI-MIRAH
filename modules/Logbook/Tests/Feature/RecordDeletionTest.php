<?php

namespace Modules\Logbook\Tests\Feature;

use App\Models\LogbookDay;
use App\Models\LogbookEntry;
use App\Models\Observation;
use App\Models\SurveillanceObservation;
use App\Models\WitnessReport;
use App\Support\Authorization\Role;
use App\Support\Logbook\LogbookDayDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\TestCase;

class RecordDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function openEntry(array $attributes = []): LogbookEntry
    {
        $day = LogbookDay::factory()->create(['day' => today()->toDateString(), 'shift_start_at' => today()->setTime(8, 0), 'shift_end_at' => today()->addDay()->setTime(8, 0)]);

        return LogbookEntry::factory()->for($day, 'day')->create($attributes);
    }

    #[Assumes('H-10-2')]
    public function test_the_team_lead_deletes_an_observation_and_its_logbook_line_stays_struck_through(): void
    {
        $lead = $this->signInAs(Role::TeamLead, ['first_name' => 'Ibrahim', 'name' => 'TOURE']);
        $entry = $this->openEntry(['content' => 'Navire observé au large']);
        $observation = Observation::factory()->create(['logbook_entry_id' => $entry->id]);
        $mirror = SurveillanceObservation::factory()->create(['legacy_observation_id' => $observation->id, 'vessel_id' => $observation->vessel_id]);

        $this->delete(route('logbook.destroy', $observation), ['reason' => 'Doublon'])->assertRedirect(route('logbook.index'));

        $this->assertSoftDeleted($observation);
        $this->assertSoftDeleted($mirror);
        $this->assertSame($lead->id, Observation::withTrashed()->find($observation->id)->deleted_by);
        $this->assertSame('Doublon', $entry->fresh()->cancellation_reason);
        $this->assertSame('logbook.delete_observation', Activity::where('event', 'logbook.delete_observation')->sole()->event);

        $this->get(route('logbook.index'))->assertOk()->assertDontSee(route('logbook.show', $observation), false);
        $this->get(route('logbook.days.show', $entry->logbook_day_id))->assertOk()->assertSee('gs-cancelled', false)->assertSee('Ibrahim TOURE')->assertSee('Doublon');

        $what = collect(app(LogbookDayDocument::class)->build($entry->day->fresh())->blocks())->flatMap(fn (array $block) => $block['rows'] ?? [])->flatten(1)->flatMap(fn ($cell) => $cell['lines'] ?? []);
        $this->assertTrue($what->contains(fn (array $line) => $line['text'] === 'Navire observé au large' && $line['strike'] === true));
    }

    #[Assumes('H-10-2')]
    public function test_agents_cannot_delete_and_a_reason_is_required(): void
    {
        $this->signInAs(Role::Agent);
        $observation = Observation::factory()->create();

        $this->delete(route('logbook.destroy', $observation), ['reason' => 'x'])->assertForbidden();

        $this->signInAs(Role::Director);
        $this->delete(route('logbook.destroy', $observation), [])->assertSessionHasErrors('reason');

        $this->assertNotSoftDeleted($observation);
    }

    #[Assumes('H-10-2')]
    public function test_nothing_is_deleted_once_the_day_is_closed(): void
    {
        $this->signInAs(Role::TeamLead);
        $day = LogbookDay::factory()->locked()->create();
        $entry = LogbookEntry::factory()->for($day, 'day')->create();
        $report = WitnessReport::factory()->create(['logbook_entry_id' => $entry->id]);

        $this->delete(route('witnesses.destroy', $report), ['reason' => 'Erreur'])->assertSessionHasErrors('reason');
        $this->delete(route('logbook.entries.destroy', $entry), ['reason' => 'Erreur'])->assertSessionHasErrors('reason');

        $this->assertNotSoftDeleted($report);
        $this->assertNull($entry->fresh()->cancelled_at);
    }

    #[Assumes('H-10-2')]
    public function test_witness_reports_vessel_checks_and_free_lines_are_deleted_by_the_team_lead(): void
    {
        $this->signInAs(Role::TeamLead);
        $reportEntry = $this->openEntry();
        $report = WitnessReport::factory()->create(['logbook_entry_id' => $reportEntry->id]);
        $checkEntry = LogbookEntry::factory()->for($reportEntry->day, 'day')->create();
        $check = SurveillanceObservation::factory()->create(['logbook_entry_id' => $checkEntry->id]);
        $free = LogbookEntry::factory()->for($reportEntry->day, 'day')->create();

        $this->delete(route('logbook.entries.destroy', $reportEntry), ['reason' => 'Erreur'])->assertSessionHasErrors('reason');

        $this->delete(route('witnesses.destroy', $report), ['reason' => 'Faux signalement'])->assertRedirect(route('witnesses.index'));
        $this->delete(route('watch.observations.destroy', $check), ['reason' => 'Mauvais navire'])->assertRedirect(route('watch.observations.index'));
        $this->delete(route('logbook.entries.destroy', $free), ['reason' => 'Saisie en double'])->assertRedirect(route('logbook.days.show', $free->logbook_day_id));

        $this->assertSoftDeleted($report);
        $this->assertSoftDeleted($check);
        $this->assertNotNull($reportEntry->fresh()->cancelled_at);
        $this->assertNotNull($checkEntry->fresh()->cancelled_at);
        $this->assertSame('Saisie en double', $free->fresh()->cancellation_reason);
        $this->assertSame(3, LogbookEntry::whereNotNull('cancelled_at')->count());
    }
}
