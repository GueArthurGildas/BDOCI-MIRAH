<?php

namespace Modules\Logbook\Tests\Feature;

use App\Models\LogbookEntry;
use App\Models\Observation;
use App\Models\SurveillanceObservation;
use App\Models\Vessel;
use App\Support\Authorization\Role;
use App\Support\Logbook\LogbookChannels;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;
use ZipArchive;

class ObservationLogbookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setTime(10, 0));
    }

    #[Covers('UC08', partial: true)]
    #[Assumes('H-3a-10')]
    public function test_an_observation_is_written_in_the_daily_logbook_and_both_pages_link_to_each_other(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create(['name' => 'CALIPSO']);

        $this->post(route('logbook.store'), [
            'observed_at' => now()->subHour()->format('Y-m-d H:i'),
            'vessel_id' => $vessel->id,
            'level' => 'warning',
            'description' => 'Navire en pêche dans la zone 1.',
        ])->assertSessionHasNoErrors();

        $observation = Observation::sole();
        $entry = LogbookEntry::sole();
        $this->assertSame($entry->id, $observation->logbook_entry_id);
        $this->assertSame('Navire en pêche dans la zone 1.', $entry->content);
        $this->assertSame(app(LogbookChannels::class)->idFor(LogbookChannels::INTERNAL), $entry->channel_id);
        $this->assertSame([$vessel->id], $entry->vessels()->pluck('vessels.id')->all());
        $this->assertSame($entry->id, SurveillanceObservation::sole()->logbook_entry_id);

        $this->get(route('logbook.days.show', $entry->logbook_day_id))
            ->assertOk()
            ->assertSee('id="entry-'.$entry->id.'"', false)
            ->assertSee(route('logbook.show', $observation))
            ->assertSee(__('logbook::logbook.entries.observation', ['id' => $observation->id]));

        $this->get(route('logbook.show', $observation))
            ->assertOk()
            ->assertSee(route('logbook.days.show', $entry->logbook_day_id).'#entry-'.$entry->id, false);
    }

    #[Covers('UC08', partial: true)]
    #[Assumes('H-3a-10')]
    public function test_an_observation_without_vessel_is_still_written_in_the_daily_logbook(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('logbook.store'), [
            'observed_at' => now()->format('Y-m-d H:i'),
            'level' => 'info',
            'description' => 'Activité suspecte signalée au large.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(LogbookEntry::sole()->id, Observation::sole()->logbook_entry_id);
        $this->assertSame(0, SurveillanceObservation::count());
    }

    #[Covers('UC08', partial: true)]
    public function test_the_printed_logbook_names_the_observation_of_a_line(): void
    {
        $this->signInAs(Role::Agent);

        $this->post(route('logbook.store'), [
            'observed_at' => now()->format('Y-m-d H:i'),
            'level' => 'info',
            'description' => 'Activité suspecte signalée au large.',
        ])->assertSessionHasNoErrors();

        $entry = LogbookEntry::sole();
        $response = $this->get(route('logbook.days.word', $entry->logbook_day_id))->assertOk();
        $zip = new ZipArchive;
        $path = tempnam(sys_get_temp_dir(), 'docx');
        file_put_contents($path, $response->getContent());
        $this->assertTrue($zip->open($path));
        $xml = html_entity_decode($zip->getFromName('word/document.xml'), ENT_QUOTES | ENT_XML1);
        $zip->close();
        unlink($path);

        $this->assertStringContainsString('Observation n° '.Observation::sole()->id, $xml);
    }
}
