<?php

namespace Modules\Operations\Tests\Feature;

use App\Enums\ActorRole;
use App\Enums\LogbookDirection;
use App\Enums\LogbookHandlingStatus;
use App\Enums\OperationCategoryCode;
use App\Enums\OperationMilestone;
use App\Enums\OperationStatus;
use App\Enums\WatchStatus;
use App\Models\LegalEntity;
use App\Models\LogbookEntry;
use App\Models\Operation;
use App\Models\OperationCategory;
use App\Models\OperationEvent;
use App\Models\Vessel;
use App\Models\VesselActor;
use App\Models\WatchEntry;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use App\Support\Logbook\LogbookChannels;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Attributes\Covers;
use Tests\TestCase;

class OperationLogbookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(DocumentStore::DISK);
        $this->travelTo(now()->setTime(10, 0));
    }

    private function channel(string $code): int
    {
        return app(LogbookChannels::class)->idFor($code);
    }

    private function open(array $overrides = []): Operation
    {
        $category = OperationCategory::factory()->create(['code' => OperationCategoryCode::Vms]);

        $this->post(route('operations.store'), [
            'category_id' => $category->id,
            'name' => 'CALIPSO',
            'summary' => 'Défaut de réception des positions du F/V CALIPSO.',
            'channel_id' => $this->channel('themis'),
            'direction' => 'received',
            ...$overrides,
        ])->assertSessionHasNoErrors();

        return Operation::sole();
    }

    #[Covers('UC11', 'UC12', partial: true)]
    public function test_opening_an_operation_writes_a_red_line_in_the_daily_logbook(): void
    {
        $agent = $this->signInAs(Role::Agent);
        config(['surveillance.watch.csp_manager' => 'Maxime DIOMANDE']);
        $vessel = Vessel::factory()->create(['name' => 'CALIPSO']);

        $operation = $this->open(['vessel_ids' => [$vessel->id], 'vessel_position' => '02°16\'30"N / 006°48\'59"W', 'comments' => 'Aucun autre navire.']);

        $line = LogbookEntry::sole();
        $this->assertSame($operation->id, $line->operation_id);
        $this->assertSame(OperationMilestone::Opened, $line->operation_milestone);
        $this->assertSame($this->channel('themis'), $line->channel_id);
        $this->assertSame('Défaut de réception des positions du F/V CALIPSO.', $line->content);
        $this->assertSame(LogbookHandlingStatus::InProgress, $line->handling_status);
        $this->assertTrue($line->occurred_at->equalTo($operation->started_at));
        $this->assertSame([$vessel->id], $line->vessels->pluck('id')->all());
        $this->assertSame(1, WatchEntry::open()->where('vessel_id', $vessel->id)->where('operation_id', $operation->id)->count());
        $this->assertSame(['lead' => $agent->id], $operation->personnel->mapWithKeys(fn ($user) => [$user->pivot->role => $user->id])->sortKeys()->all());
        $this->assertSame('Maxime DIOMANDE', $operation->csp_manager_name);
        $this->assertSame('02°16\'30"N / 006°48\'59"W', $operation->context->vessel_position);

        $this->get(route('logbook.days.show', $line->day))->assertOk()
            ->assertSee(__('logbook::logbook.entries.operation_opened', ['code' => $operation->code]))
            ->assertSee(__('operations::operations.in_progress_list'));

        $this->get(route('operations.show', $operation))->assertOk()
            ->assertSee('Maxime DIOMANDE')
            ->assertSee('Aucun autre navire.')
            ->assertSee(__('operations::operations.fields.mmsi'));
    }

    #[Covers('UC11', 'UC12', partial: true)]
    public function test_closing_an_operation_writes_a_green_line_with_the_result_and_the_closing_block(): void
    {
        $this->signInAs(Role::TeamLead);
        $vessel = Vessel::factory()->create();
        $operation = $this->open(['vessel_ids' => [$vessel->id]]);

        $this->travelTo(now()->addDays(2)->setTime(10, 21));
        $this->post(route('operations.close', $operation), [
            'result' => 'Équipement VMS réparé, navire à nouveau visible.',
            'infractions_noted' => 'Aucune.',
            'channel_id' => $this->channel('email'),
            'direction' => 'received',
        ])->assertSessionHasNoErrors();

        $operation->refresh();
        $this->assertSame(OperationStatus::Closed, $operation->status);
        $this->assertSame('Aucune.', $operation->infractions_noted);

        $closing = LogbookEntry::where('operation_milestone', OperationMilestone::Closed)->sole();
        $this->assertSame($operation->id, $closing->operation_id);
        $this->assertSame('Équipement VMS réparé, navire à nouveau visible.', $closing->content);
        $this->assertSame($this->channel('email'), $closing->channel_id);
        $this->assertSame(LogbookHandlingStatus::Closed, $closing->handling_status);
        $this->assertSame(LogbookHandlingStatus::Closed, LogbookEntry::where('operation_milestone', OperationMilestone::Opened)->sole()->handling_status);
        $this->assertSame(WatchStatus::Closed, WatchEntry::sole()->status);
        $this->assertSame(1, WatchEntry::count());

        $this->get(route('operations.show', $operation))->assertOk()
            ->assertSee(__('operations::operations.closing_title'))
            ->assertSee(__('operations::operations.duration', ['days' => 2, 'hours' => 0, 'minutes' => 21]));
    }

    #[Covers('UC11', 'UC12', partial: true)]
    public function test_information_about_an_operation_in_progress_goes_only_into_the_operation_logbook(): void
    {
        $agent = $this->signInAs(Role::Agent);
        $operation = $this->open();

        $this->post(route('logbook.entries.store'), [
            'channel_id' => $this->channel('phone'),
            'direction' => 'sent',
            'content' => 'Contact avec l\'armateur : le signal VMS est coupé.',
            'is_order_or_decision' => 1,
            'operation_id' => $operation->id,
        ])->assertRedirect(route('operations.show', $operation));

        $this->assertSame(1, LogbookEntry::count());
        $event = OperationEvent::sole();
        $this->assertSame('Contact avec l\'armateur : le signal VMS est coupé.', $event->body);
        $this->assertSame($this->channel('phone'), $event->channel_id);
        $this->assertSame(LogbookDirection::Sent, $event->direction);
        $this->assertTrue($event->is_order_or_decision);
        $this->assertSame($agent->id, $event->entered_by);
        $this->assertSame(OperationStatus::InProgress, $operation->fresh()->status);

        $this->get(route('operations.show', $operation))->assertOk()->assertSee('Contact avec l&#039;armateur : le signal VMS est coupé.', false);
    }

    public function test_a_closed_operation_cannot_receive_lines_from_the_daily_logbook(): void
    {
        $this->signInAs(Role::Agent);
        $operation = Operation::factory()->create(['status' => OperationStatus::Closed]);

        $this->post(route('logbook.entries.store'), [
            'channel_id' => $this->channel('phone'),
            'direction' => 'received',
            'content' => 'Trop tard.',
            'operation_id' => $operation->id,
        ])->assertSessionHasErrors('operation_id');

        $this->assertSame(0, OperationEvent::count());
        $this->assertSame(0, LogbookEntry::count());
    }

    public function test_an_operation_logbook_line_requires_its_channel(): void
    {
        $this->signInAs(Role::Agent);
        $operation = Operation::factory()->create();

        $this->post(route('operations.events.store', $operation), ['body' => 'Sans canal'])->assertSessionHasErrors(['channel_id', 'direction']);

        $this->assertSame(0, OperationEvent::count());
    }

    public function test_the_context_block_shows_the_vessel_owner_from_the_registry(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create();
        $owner = LegalEntity::create(['name' => 'Armement Atlantique SA', 'phone' => '+225 01 02 03 04']);
        VesselActor::create(['vessel_id' => $vessel->id, 'role' => ActorRole::Shipowner, 'legal_entity_id' => $owner->id]);
        $operation = $this->open(['vessel_ids' => [$vessel->id]]);

        $this->get(route('operations.show', $operation))->assertOk()->assertSee('Armement Atlantique SA')->assertSee('+225 01 02 03 04');
    }

    #[Covers('UC11', partial: true)]
    public function test_attachments_are_gathered_in_the_operation_file(): void
    {
        $this->signInAs(Role::Agent);
        $operation = $this->open();

        $this->post(route('operations.documents.store', $operation), ['file' => UploadedFile::fake()->create('fiche_navire.pdf', 20, 'application/pdf')])->assertSessionHasNoErrors();

        $document = app(DocumentStore::class)->for($operation)->sole();
        $this->assertSame('fiche_navire.pdf', $document->name);
        $this->get(route('operations.show', $operation))->assertOk()->assertSee('fiche_navire.pdf');
        $this->get(route('operations.documents.download', [$operation, $document]))->assertOk();

        $other = Operation::factory()->create(['category_id' => $operation->category_id]);
        $this->get(route('operations.documents.download', [$other, $document]))->assertNotFound();
    }

    public function test_a_closed_operation_refuses_new_attachments(): void
    {
        $this->signInAs(Role::Agent);
        $operation = Operation::factory()->create(['status' => OperationStatus::Closed]);

        $this->post(route('operations.documents.store', $operation), ['file' => UploadedFile::fake()->create('trop_tard.pdf', 20, 'application/pdf')])->assertSessionHasErrors('file');

        $this->assertCount(0, app(DocumentStore::class)->for($operation));
    }

    #[Covers('UC11', partial: true)]
    public function test_opening_an_operation_files_the_sheet_of_each_vessel_in_the_operation_file(): void
    {
        $this->signInAs(Role::Agent);
        $vessel = Vessel::factory()->create(['name' => 'CALIPSO', 'registration_number' => 'AN-1234']);

        $operation = $this->open(['vessel_ids' => [$vessel->id]]);

        $sheet = app(DocumentStore::class)->for($operation)->sole();
        $this->assertSame('fiche-an-1234.pdf', $sheet->name);
        $this->assertSame('application/pdf', $sheet->mime_type);
        $this->assertTrue(app(DocumentStore::class)->isIntact($sheet));
        $this->get(route('operations.show', $operation))->assertOk()->assertSee('fiche-an-1234.pdf');
    }
}
