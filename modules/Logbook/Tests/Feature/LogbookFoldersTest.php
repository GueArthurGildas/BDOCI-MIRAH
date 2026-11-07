<?php

namespace Modules\Logbook\Tests\Feature;

use App\Enums\LogbookDirection;
use App\Models\LogbookDay;
use App\Models\LogbookEntry;
use App\Models\Operation;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Authorization\Permission;
use App\Support\Authorization\Role;
use App\Support\Logbook\LogbookChannels;
use App\Support\Logbook\LogbookDayWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Logbook\Support\LogbookDayFolders;
use Tests\TestCase;
use ZipArchive;

class LogbookFoldersTest extends TestCase
{
    use RefreshDatabase;

    private function day(string $date, array $attributes = []): LogbookDay
    {
        return LogbookDay::factory()->create(['day' => $date, ...$attributes]);
    }

    private function entry(LogbookDay $day, array $attributes = [], array $vessels = []): LogbookEntry
    {
        $entry = LogbookEntry::factory()->create(['logbook_day_id' => $day->id, 'occurred_at' => $day->day->copy()->setTime(10, 0), ...$attributes]);
        $entry->vessels()->attach(collect($vessels)->map->id->all());

        return $entry;
    }

    public function test_the_list_is_filtered_by_text_vessel_author_status_channel_and_period(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create(['name' => 'NAVIRE FILTRE']);
        $author = User::factory()->create(['name' => 'Ouattara Auteur']);
        $target = $this->day('2026-03-10');
        $this->entry($target, ['content' => 'Appel du port de Tabou', 'entered_by' => $author->id], [$vessel]);
        $other = $this->day('2026-05-20');
        $this->entry($other, ['content' => 'Rien à signaler']);

        $this->get(route('logbook.days.index', ['search' => 'Tabou']))->assertViewHas('days', fn ($days) => $days->pluck('id')->all() === [$target->id]);
        $this->get(route('logbook.days.index', ['search' => 'NAVIRE FILTRE']))->assertViewHas('days', fn ($days) => $days->pluck('id')->all() === [$target->id]);
        $this->get(route('logbook.days.index', ['search' => 'Ouattara']))->assertViewHas('days', fn ($days) => $days->pluck('id')->all() === [$target->id]);
        $this->get(route('logbook.days.index', ['from' => '2026-05-01', 'to' => '2026-05-31']))->assertViewHas('days', fn ($days) => $days->pluck('id')->all() === [$other->id]);
        $this->get(route('logbook.days.index', ['channel' => app(LogbookChannels::class)->idFor('phone')]))->assertViewHas('days', fn ($days) => $days->count() === 2);
        $this->get(route('logbook.days.index', ['status' => 'locked']))->assertViewHas('days', fn ($days) => $days->isEmpty());
    }

    public function test_an_inverted_period_or_an_unknown_view_is_rejected(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('logbook.days.index', ['from' => '2026-05-10', 'to' => '2026-05-01']))->assertSessionHasErrors('to');
        $this->get(route('logbook.days.index', ['view' => 'cards']))->assertSessionHasErrors('view');
        $this->get(route('logbook.days.index', ['folder' => 'secrets']))->assertSessionHasErrors('folder');
    }

    public function test_the_folder_view_groups_days_by_year_and_month_and_opens_the_selected_day(): void
    {
        $this->signInAs(Role::Agent);
        $march = $this->day('2026-03-10');
        $this->day('2026-03-11');
        $this->day('2025-12-24');
        $this->entry($march, ['content' => 'Entrée de mars']);

        $response = $this->get(route('logbook.days.index', ['view' => 'folders', 'day' => $march->id]))->assertOk();

        $response->assertViewHas('tree', fn ($tree) => $tree->keys()->all() === [2026, 2025] && $tree[2026]->keys()->all() === ['2026-03'] && $tree[2026]['2026-03']->count() === 2)
            ->assertViewHas('selected', fn ($selected) => $selected->id === $march->id)
            ->assertSee(__('logbook::logbook.folders.vessels'))
            ->assertSee(__('logbook::logbook.folders.actors'))
            ->assertSee(__('logbook::logbook.folders.decisions'))
            ->assertSee(__('logbook::logbook.timeline.title'));
    }

    public function test_without_a_selection_the_latest_matching_day_is_shown(): void
    {
        $this->signInAs(Role::Agent);
        $this->day('2026-01-05');
        $latest = $this->day('2026-04-02');

        $this->get(route('logbook.days.index', ['view' => 'folders']))->assertViewHas('selected', fn ($selected) => $selected->id === $latest->id);
    }

    public function test_the_folders_list_involved_vessels_actors_operations_and_decisions(): void
    {
        $day = $this->day('2026-03-10');
        $first = Vessel::factory()->create(['name' => 'AKWABA UN']);
        $second = Vessel::factory()->create(['name' => 'BAOULE DEUX']);
        $operation = Operation::factory()->create(['code' => 'OP-2026-77']);
        $author = User::factory()->create(['name' => 'Traoré', 'first_name' => 'Issa']);
        $this->entry($day, ['content' => 'A', 'entered_by' => $author->id, 'operation_id' => $operation->id], [$first, $second]);
        $this->entry($day, ['content' => 'B', 'entered_by' => $author->id, 'is_order_or_decision' => true], [$first]);
        $this->entry($day, ['content' => 'C']);

        $folders = LogbookDayFolders::for($day);

        $this->assertSame(['AKWABA UN' => 2, 'BAOULE DEUX' => 1], $folders['vessels']->pluck('count', 'label')->all());
        $this->assertSame(2, $folders['actors']->firstWhere('label', 'Issa Traoré')['count']);
        $this->assertSame(['OP-2026-77' => 1], $folders['operations']->pluck('count', 'label')->all());
        $this->assertSame(1, $folders['decisions']);
        $this->assertSame(3, $folders['entries']->count());
    }

    public function test_a_folder_and_an_item_narrow_the_entries(): void
    {
        $this->signInAs(Role::Agent);
        $day = $this->day('2026-03-10');
        $vessel = Vessel::factory()->create(['name' => 'NAVIRE DOSSIER']);
        $this->entry($day, ['content' => 'Entrée avec navire'], [$vessel]);
        $this->entry($day, ['content' => 'Entrée sans navire']);
        $this->entry($day, ['content' => 'Décision du chef', 'is_order_or_decision' => true]);

        $this->get(route('logbook.days.index', ['view' => 'folders', 'day' => $day->id, 'folder' => 'vessels', 'item' => $vessel->id]))
            ->assertViewHas('content', fn (array $content) => $content['filtered']->pluck('content')->all() === ['Entrée avec navire'])
            ->assertSee('Entrée avec navire')
            ->assertDontSee('Entrée sans navire');

        $this->get(route('logbook.days.index', ['view' => 'folders', 'day' => $day->id, 'folder' => 'decisions']))
            ->assertViewHas('content', fn (array $content) => $content['filtered']->pluck('content')->all() === ['Décision du chef']);

        $this->get(route('logbook.days.index', ['view' => 'folders', 'day' => $day->id, 'folder' => 'vessels']))
            ->assertViewHas('content', fn (array $content) => $content['filtered']->count() === 1);
    }

    public function test_the_word_document_contains_the_day_its_entries_and_who_was_involved(): void
    {
        $this->signInAs(Role::Agent);
        $day = $this->day('2026-03-10');
        $vessel = Vessel::factory()->create(['name' => 'NAVIRE WORD']);
        $author = User::factory()->create(['name' => 'Kouamé', 'first_name' => 'Ange']);
        $this->entry($day, ['content' => 'Contrôle radio du chalutier & équipage', 'entered_by' => $author->id, 'is_order_or_decision' => true, 'direction' => LogbookDirection::Sent], [$vessel]);

        $response = $this->get(route('logbook.days.word', $day))->assertOk()->assertHeader('Content-Type', LogbookDayWord::MIME_TYPE);

        $this->assertStringContainsString('Main_courante_2026-03-10.docx', $response->headers->get('Content-Disposition'));

        $path = tempnam(sys_get_temp_dir(), 'docx');
        file_put_contents($path, $response->getContent());
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path));
        $xml = html_entity_decode($zip->getFromName('word/document.xml'), ENT_QUOTES | ENT_XML1);
        $zip->close();
        unlink($path);

        $this->assertStringContainsString('10/03/2026', $xml);
        $this->assertStringContainsString('Contrôle radio du chalutier & équipage', $xml);
        $this->assertStringContainsString('NAVIRE WORD', $xml);
        $this->assertStringContainsString('Ange Kouamé', $xml);
        $this->assertStringContainsString(__('logbook::logbook.entries.order_or_decision'), $xml);
    }

    public function test_the_word_document_needs_the_logbook_permission(): void
    {
        $day = $this->day('2026-03-10');

        $this->get(route('logbook.days.word', $day))->assertRedirect(route('login'));

        $this->signInWithPermissions(Permission::DashboardView);
        $this->get(route('logbook.days.word', $day))->assertForbidden();
    }

    public function test_the_day_page_shows_a_timeline_with_each_kind_of_entry(): void
    {
        $this->signInAs(Role::Agent);
        $day = $this->day(today()->toDateString());
        $this->entry($day, ['content' => 'Appel reçu', 'occurred_at' => $day->day->copy()->setTime(9, 0)]);
        $original = $this->entry($day, ['content' => 'Message émis', 'direction' => LogbookDirection::Sent, 'occurred_at' => $day->day->copy()->setTime(10, 0)]);
        $this->entry($day, ['content' => 'Ordre donné', 'is_order_or_decision' => true, 'occurred_at' => $day->day->copy()->setTime(11, 0)]);
        $this->entry($day, ['content' => 'Correction', 'corrects_entry_id' => $original->id, 'occurred_at' => $day->day->copy()->setTime(12, 0)]);

        $html = $this->get(route('logbook.days.show', $day))->assertOk()->assertSee(__('logbook::logbook.timeline.title'))->getContent();

        foreach (['received', 'sent', 'decision', 'correction'] as $kind) {
            $this->assertStringContainsString(__('logbook::logbook.timeline.legend.'.$kind), $html);
        }

        $this->assertLessThan(strpos($html, 'Ordre donné'), strpos($html, 'Appel reçu'));
        $this->assertSame(1, substr_count($html, 'data-guide="entries"'));
    }
}
