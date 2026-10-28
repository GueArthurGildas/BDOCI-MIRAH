<?php

namespace Modules\Logbook\Tests\Feature;

use App\Enums\LogbookDayStatus;
use App\Enums\LogbookHandlingStatus;
use App\Enums\WatchOrigin;
use App\Enums\WatchStatus;
use App\Models\LogbookDay;
use App\Models\LogbookEntry;
use App\Models\Vessel;
use App\Models\VesselEvent;
use App\Models\WatchEntry;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookCalendar;
use App\Support\Logbook\LogbookChannels;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

class LogbookDayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(DocumentStore::DISK);
        $this->travelTo(now()->setTime(11, 0));
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'channel_id' => app(LogbookChannels::class)->idFor('phone'),
            'direction' => 'received',
            'content' => 'Appel de l\'agent maritime : arrivée retardée du chalutier.',
        ], $overrides);
    }

    public function test_guests_are_redirected_and_administrators_are_forbidden(): void
    {
        $this->get(route('logbook.days.index'))->assertRedirect(route('login'));

        $this->signInAs(Role::Admin);
        $this->get(route('logbook.days.index'))->assertForbidden();
    }

    #[Covers('UC12', 'RG10', partial: true)]
    public function test_a_line_cites_vessels_and_puts_them_on_the_watch_list_and_in_the_journal(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();

        $this->post(route('logbook.entries.store'), $this->payload(['vessel_ids' => [$vessel->id], 'is_order_or_decision' => 1]))
            ->assertRedirect();

        $entry = LogbookEntry::sole();
        $this->assertSame(LogbookHandlingStatus::ToHandle, $entry->handling_status);
        $this->assertSame($agent->id, $entry->entered_by);
        $this->assertTrue($entry->is_order_or_decision);
        $this->assertSame(LogbookDayStatus::Open, $entry->day->status);

        $watch = WatchEntry::sole();
        $this->assertSame(WatchOrigin::Logbook, $watch->origin);
        $this->assertSame($vessel->id, $watch->vessel_id);
        $this->assertSame($entry->id, $watch->logbook_entry_id);

        $this->assertSame('entry.added', VesselEvent::sole()->type);
        $this->assertSame($agent->id, Activity::where('event', 'logbook.add_entry')->sole()->causer_id);
    }

    #[Covers('UC12', 'RG10', partial: true)]
    #[Assumes('H-3a-7')]
    public function test_a_cited_vessel_stays_on_watch_until_the_line_is_closed(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        $this->post(route('logbook.entries.store'), $this->payload(['vessel_ids' => [$vessel->id]]));
        $entry = LogbookEntry::sole();

        $this->patch(route('logbook.entries.update', $entry), ['handling_status' => 'in_progress']);
        $this->assertSame(WatchStatus::Open, WatchEntry::sole()->status);

        $this->patch(route('logbook.entries.update', $entry), ['handling_status' => 'closed']);
        $this->assertSame(WatchStatus::Closed, WatchEntry::sole()->status);
        $this->assertNotNull(WatchEntry::sole()->closed_at);

        $this->patch(route('logbook.entries.update', $entry), ['handling_status' => 'to_handle']);
        $this->assertSame(1, WatchEntry::open()->count());
    }

    #[Covers('UC12', partial: true)]
    public function test_a_line_validates_its_input_and_writes_nothing_when_invalid(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('logbook.entries.store'), $this->payload(['content' => '', 'direction' => 'sideways']))
            ->assertSessionHasErrors(['content', 'direction']);

        $this->assertSame(0, LogbookEntry::count());
        $this->assertSame(0, LogbookDay::count());
    }

    #[Covers('UC12', partial: true)]
    #[Assumes('H-3a-1')]
    public function test_a_line_belongs_to_the_day_whose_shift_started_before_it(): void
    {
        $this->signInAs(Role::Agent);
        $this->travelTo(now()->setTime(12, 0));

        $this->post(route('logbook.entries.store'), $this->payload(['occurred_at' => now()->subDay()->setTime(7, 30)->toDateTimeString()]));
        $this->post(route('logbook.entries.store'), $this->payload(['occurred_at' => now()->setTime(8, 0)->toDateTimeString()]));

        $this->assertSame(
            [now()->subDays(2)->toDateString(), now()->toDateString()],
            LogbookDay::orderBy('day')->get()->map(fn (LogbookDay $day) => $day->day->toDateString())->all(),
        );
        $this->assertSame('08:00', LogbookDay::orderByDesc('day')->first()->shift_start_at->format('H:i'));
        $this->assertSame('18:00', LogbookDay::orderByDesc('day')->first()->shift_end_at->format('H:i'));
    }

    #[Covers('UC12', partial: true)]
    #[Assumes('H-3a-1')]
    public function test_the_shift_hours_come_from_the_settings(): void
    {
        config(['surveillance.watch.shift_start_hour' => 6, 'surveillance.watch.shift_end_hour' => 16]);

        $day = app(LogbookCalendar::class)->dayFor(now()->setTime(6, 30));

        $this->assertSame('06:00', $day->shift_start_at->format('H:i'));
        $this->assertSame('16:00', $day->shift_end_at->format('H:i'));
        $this->assertSame(now()->toDateString(), $day->day->toDateString());
    }

    #[Covers('UC12', 'RG10')]
    #[Assumes('H-3a-4')]
    public function test_closing_the_day_freezes_an_unmodifiable_pdf_and_locks_the_lines(): void
    {
        $lead = $this->signInAs(Role::TeamLead);
        $this->post(route('logbook.entries.store'), $this->payload(['is_order_or_decision' => 1]));
        $this->post(route('logbook.entries.store'), $this->payload(['direction' => 'sent', 'content' => 'Réponse à l\'agent maritime.']));
        $day = LogbookDay::sole();

        $this->post(route('logbook.days.close', $day))->assertRedirect(route('logbook.days.show', $day));

        $day->refresh();
        $this->assertSame(LogbookDayStatus::Locked, $day->status);
        $this->assertNotNull($day->locked_at);
        $this->assertSame('Main_courante_CSP_du_'.$day->day->format('Y_m_d').'.pdf', $day->pdfDocument->name);
        $this->assertTrue($day->pdfDocument->is_frozen);
        $this->assertSame(64, strlen($day->pdfDocument->sha256_fingerprint));
        $this->assertTrue(app(DocumentStore::class)->isIntact($day->pdfDocument));
        $this->assertStringStartsWith('%PDF', Storage::disk(DocumentStore::DISK)->get($day->pdfDocument->path));

        $this->assertSame(2, $day->closing->counters['entries_total']);
        $this->assertSame(1, $day->closing->counters['sent']);
        $this->assertSame(1, $day->closing->counters['orders_or_decisions']);
        $this->assertSame($lead->id, $day->closing->closed_by);
        $this->assertSame($lead->id, Activity::where('event', 'logbook.close_day')->sole()->causer_id);

        $this->get(route('logbook.days.pdf', $day))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('logbook.days.show', $day))->assertOk()->assertSee($day->pdfDocument->sha256_fingerprint);
    }

    #[Covers('UC12', partial: true)]
    public function test_agents_cannot_close_the_day(): void
    {
        $this->signInAs(Role::Agent);
        $this->post(route('logbook.entries.store'), $this->payload());
        $day = LogbookDay::sole();

        $this->post(route('logbook.days.close', $day))->assertForbidden();

        $this->assertSame(LogbookDayStatus::Open, $day->fresh()->status);
        $this->assertNull($day->fresh()->pdf_document_id);
    }

    #[Covers('UC12', partial: true)]
    public function test_a_locked_day_cannot_be_closed_twice_nor_receive_new_lines(): void
    {
        $this->signInAs(Role::TeamLead);
        $this->post(route('logbook.entries.store'), $this->payload());
        $day = LogbookDay::sole();
        $this->post(route('logbook.days.close', $day));

        $this->post(route('logbook.days.close', $day))->assertSessionHasErrors('day');
        $this->post(route('logbook.entries.store'), $this->payload())->assertSessionHasErrors('occurred_at');

        $this->assertSame(1, LogbookEntry::count());
    }

    #[Covers('UC12', 'RG10', partial: true)]
    public function test_the_database_refuses_to_update_a_line_of_a_locked_day(): void
    {
        $this->signInAs(Role::TeamLead);
        $this->post(route('logbook.entries.store'), $this->payload());
        $entry = LogbookEntry::sole();
        $this->post(route('logbook.days.close', $entry->day));

        $this->expectException(QueryException::class);
        $this->expectExceptionMessage('logbook day is locked');

        DB::table('logbook_entries')->where('id', $entry->id)->update(['content' => 'Texte modifié après verrouillage']);
    }

    #[Covers('UC12', 'RG10', partial: true)]
    #[Assumes('H-3a-4')]
    public function test_a_locked_line_cannot_change_status_but_can_be_rectified_in_the_current_day(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $this->post(route('logbook.entries.store'), $this->payload(['occurred_at' => now()->subDay()->setTime(10, 0)->toDateTimeString(), 'vessel_ids' => [$vessel->id]]));
        $original = LogbookEntry::sole();
        $this->post(route('logbook.days.close', $original->day));

        $this->patch(route('logbook.entries.update', $original), ['handling_status' => 'closed'])->assertSessionHasErrors('handling_status');
        $this->assertSame(LogbookHandlingStatus::ToHandle, $original->fresh()->handling_status);

        $this->post(route('logbook.entries.rectify.store', $original), ['content' => 'Rectification : le chalutier arrive demain.'])->assertRedirect();

        $correction = LogbookEntry::whereNotNull('corrects_entry_id')->sole();
        $this->assertSame($original->id, $correction->corrects_entry_id);
        $this->assertSame(LogbookDayStatus::Open, $correction->day->status);
        $this->assertTrue($correction->day->isNot($original->day));
        $this->assertSame($original->channel_id, $correction->channel_id);
        $this->assertSame([$vessel->id], $correction->vessels->pluck('id')->all());
        $this->assertSame($original->content, $original->fresh()->content);
        $this->assertSame(1, WatchEntry::count());
    }

    public function test_a_rectifying_line_cannot_itself_be_rectified(): void
    {
        $this->signInAs(Role::Agent);
        $this->post(route('logbook.entries.store'), $this->payload());
        $original = LogbookEntry::sole();
        $this->post(route('logbook.entries.rectify.store', $original), ['content' => 'Première rectification']);
        $correction = LogbookEntry::whereNotNull('corrects_entry_id')->sole();

        $this->post(route('logbook.entries.rectify.store', $correction), ['content' => 'Seconde'])->assertSessionHasErrors('content');
    }

    public function test_days_and_lines_are_listed(): void
    {
        $this->signInAs(Role::Agent);
        $this->post(route('logbook.entries.store'), $this->payload(['content' => 'Ligne visible dans la journée']));

        $this->get(route('logbook.days.index'))->assertOk();
        $this->get(route('logbook.days.today'))->assertRedirect(route('logbook.days.show', LogbookDay::sole()));
        $this->get(route('logbook.days.show', LogbookDay::sole()))->assertOk()->assertSee('Ligne visible dans la journée');
    }
}
