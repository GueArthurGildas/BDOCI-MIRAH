<?php

namespace Modules\Reports\Tests\Feature;

use App\Enums\DataSourceStatus;
use App\Enums\OperationStatus;
use App\Models\AiAgentAction;
use App\Models\DataSource;
use App\Models\Document;
use App\Models\ExportLog;
use App\Models\Operation;
use App\Models\OperationArchive;
use App\Models\ScheduledReport;
use App\Models\User;
use App\Models\Vessel;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Role;
use App\Support\Documents\DocumentStore;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Reports\Actions\ArchiveOperation;
use Spatie\Activitylog\Models\Activity;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\TestCase;

#[Covers('UC24', partial: true)]
class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_agents_and_administrators_cannot_open_the_reports(): void
    {
        $this->signInAs(Role::Agent);
        $this->get(route('reports.index'))->assertForbidden();

        $this->signInAs(Role::Admin);
        $this->get(route('reports.archives.index'))->assertForbidden();
    }

    public function test_a_director_creates_and_updates_a_scheduled_report_through_the_action(): void
    {
        $director = $this->signInAs(Role::Director);

        $this->post(route('reports.store'), ['code' => 'operations_activity', 'frequency' => 'monthly', 'format' => 'pdf', 'recipient_id' => $director->id])
            ->assertRedirect(route('reports.index'));

        $report = ScheduledReport::sole();
        $this->assertTrue($report->is_active);
        $this->assertSame($director->id, Activity::where('event', 'reports.save_scheduled_report')->sole()->causer_id);

        $this->put(route('reports.update', $report), ['code' => 'logbook_activity', 'frequency' => 'weekly', 'format' => 'excel', 'recipient_id' => $director->id, 'is_active' => 0])
            ->assertRedirect(route('reports.index'));

        $this->assertSame('logbook_activity', $report->fresh()->code);
        $this->assertFalse($report->fresh()->is_active);
        $this->get(route('reports.index'))->assertOk();
    }

    public function test_a_scheduled_report_rejects_an_unknown_report_code(): void
    {
        $director = $this->signInAs(Role::Director);

        $this->post(route('reports.store'), ['code' => 'nope', 'frequency' => 'monthly', 'format' => 'pdf', 'recipient_id' => $director->id])
            ->assertSessionHasErrors('code');

        $this->assertSame(0, ScheduledReport::count());
    }

    #[Assumes('H-9-2')]
    public function test_the_scheduled_command_freezes_one_document_per_period_and_notifies_the_recipient(): void
    {
        Storage::fake(DocumentStore::DISK);
        $recipient = User::factory()->create();
        $report = ScheduledReport::factory()->create(['recipient_id' => $recipient->id]);
        Operation::factory()->create(['started_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay()]);

        $this->artisan('reports:run-scheduled')->assertSuccessful();
        $this->artisan('reports:run-scheduled')->assertSuccessful();

        $document = app(DocumentStore::class)->for($report)->sole();
        $this->assertTrue($document->is_frozen);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertSame(1, $recipient->notifications()->count());
        $this->assertSame('reports::alerts.report_ready', $recipient->notifications()->first()->data['translation_key']);
    }

    public function test_an_excel_report_is_a_semicolon_separated_file(): void
    {
        Storage::fake(DocumentStore::DISK);
        $report = ScheduledReport::factory()->create(['format' => 'excel']);
        Operation::factory()->create(['name' => 'Senneur', 'started_at' => now()->subMonthNoOverflow()->startOfMonth()->addDay()]);

        $this->artisan('reports:run-scheduled')->assertSuccessful();

        $document = app(DocumentStore::class)->for($report)->sole();
        $this->assertStringEndsWith('.csv', $document->name);
        $this->assertStringContainsString('Senneur', Storage::disk(DocumentStore::DISK)->get($document->path));
    }

    public function test_inactive_reports_are_skipped_and_the_alert_can_be_disabled(): void
    {
        Storage::fake(DocumentStore::DISK);
        $recipient = User::factory()->create();
        ScheduledReport::factory()->create(['is_active' => false]);
        $this->artisan('reports:run-scheduled')->assertSuccessful();
        $this->assertSame(0, Document::count());

        ScheduledReport::factory()->create(['recipient_id' => $recipient->id]);
        config(['surveillance.alerts.scheduled_reports.enabled' => false]);
        $this->artisan('reports:run-scheduled')->assertSuccessful();

        $this->assertSame(1, Document::count());
        $this->assertSame(0, $recipient->notifications()->count());
    }

    public function test_the_generate_button_produces_the_report_once_and_the_document_downloads(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::Director);
        $report = ScheduledReport::factory()->create();

        $this->post(route('reports.generate', $report))->assertSessionHas('status');
        $this->post(route('reports.generate', $report))->assertSessionHas('error');

        $document = app(DocumentStore::class)->for($report)->sole();
        $this->get(route('reports.documents.download', $document))->assertOk();
        $this->get(route('reports.index'))->assertOk()->assertSee($document->name);
    }

    public function test_only_documents_of_scheduled_reports_can_be_downloaded_here(): void
    {
        Storage::fake(DocumentStore::DISK);
        $this->signInAs(Role::Director);
        $document = app(DocumentStore::class)->storeContent('other.txt', 'x', 'text/plain');

        $this->get(route('reports.documents.download', $document))->assertNotFound();
    }

    public function test_an_export_is_logged_with_user_permission_filters_and_row_count(): void
    {
        $director = $this->signInAs(Role::Director);
        Operation::factory()->count(2)->create(['year' => 2026]);
        Operation::factory()->create(['year' => 2025]);

        $response = $this->get(route('reports.exports.download', ['type' => 'operations', 'format' => 'csv', 'year' => 2026]))->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $log = ExportLog::sole();
        $this->assertSame($director->id, $log->user_id);
        $this->assertSame('reports.export_operations', $log->permission);
        $this->assertSame(['year' => '2026'], $log->filters);
        $this->assertSame(2, $log->row_count);
        $this->assertSame(1, Activity::where('event', 'reports.export_operations')->count());
        $this->get(route('reports.exports.index'))->assertOk()->assertSee(__('reports::reports.exports.types.operations'));
    }

    public function test_every_export_type_renders_csv_and_pdf(): void
    {
        $this->signInAs(Role::Director);
        Vessel::factory()->create();

        foreach (['operations', 'vessels', 'logbook'] as $type) {
            foreach (['csv', 'pdf'] as $format) {
                $this->get(route('reports.exports.download', ['type' => $type, 'format' => $format]))->assertOk();
            }
        }

        $this->assertSame(6, ExportLog::count());
    }

    public function test_exports_need_the_permission_of_their_type_and_write_nothing_when_refused(): void
    {
        $this->signInAs(Role::Agent);

        $this->get(route('reports.exports.download', ['type' => 'vessels', 'format' => 'csv']))->assertForbidden();
        $this->get(route('reports.exports.download', ['type' => 'vessels', 'format' => 'bad']))->assertForbidden();

        $this->assertSame(0, ExportLog::count());
    }

    public function test_the_export_log_is_append_only(): void
    {
        $log = ExportLog::factory()->create();

        $this->expectException(QueryException::class);
        $log->update(['row_count' => 0]);
    }

    private function oldClosedOperation(int $years): Operation
    {
        return Operation::factory()->create(['status' => OperationStatus::Closed, 'ended_at' => now()->subYears($years)->subDay()]);
    }

    #[Assumes('H-9-1')]
    public function test_only_operations_closed_for_five_years_are_archived_and_become_read_only(): void
    {
        $old = $this->oldClosedOperation(5);
        $recent = $this->oldClosedOperation(4);
        $open = Operation::factory()->create(['started_at' => now()->subYears(8)]);

        $this->artisan('reports:archive-operations')->assertSuccessful();

        $this->assertSame(OperationStatus::Archived, $old->fresh()->status);
        $this->assertSame(OperationStatus::Closed, $recent->fresh()->status);
        $this->assertSame(OperationStatus::Open, $open->fresh()->status);
        $this->assertSame([$old->id], OperationArchive::pluck('operation_id')->all());

        $this->expectException(QueryException::class);
        $old->fresh()->update(['summary' => 'edit']);
    }

    #[Assumes('H-9-1')]
    public function test_the_database_blocks_changes_to_the_children_of_an_archived_operation(): void
    {
        $operation = $this->oldClosedOperation(6);
        $this->artisan('reports:archive-operations')->assertSuccessful();

        $this->expectException(QueryException::class);
        $operation->events()->create(['occurred_at' => now(), 'description' => 'late', 'user_id' => User::factory()->create()->id]);
    }

    #[Assumes('H-9-1')]
    public function test_the_archive_threshold_is_a_setting(): void
    {
        $operation = $this->oldClosedOperation(2);
        config(['surveillance.archive_after_years' => 1]);

        $this->artisan('reports:archive-operations')->assertSuccessful();

        $this->assertSame(OperationStatus::Archived, $operation->fresh()->status);
    }

    public function test_the_archive_screen_lists_and_runs_the_archiving(): void
    {
        $this->signInAs(Role::Director);
        $operation = $this->oldClosedOperation(6);

        $this->get(route('reports.archives.index'))->assertOk();
        $this->post(route('reports.archives.run'))->assertRedirect(route('reports.archives.index'))->assertSessionHas('status');

        $this->get(route('reports.archives.index'))->assertOk()->assertSee($operation->code);
        $this->get(route('operations.show', $operation))->assertOk();
        $this->assertSame(1, Activity::where('event', 'reports.archive_operation')->count());
    }

    public function test_an_operation_that_is_not_old_enough_cannot_be_archived_by_the_action(): void
    {
        $this->signInAs(Role::Director);
        $operation = $this->oldClosedOperation(1);

        $this->expectException(ValidationException::class);
        app(ActionRunner::class)->run(ArchiveOperation::class, auth()->user(), ['operation_id' => $operation->id]);
    }

    public function test_the_health_banner_shows_for_supervisors_when_a_source_has_a_problem(): void
    {
        $this->signInAs(Role::Director);
        $this->get(route('reports.index'))->assertDontSee('data-health-banner', false);

        DataSource::factory()->create(['name' => 'THEMIS', 'status' => DataSourceStatus::Down]);

        $this->get(route('reports.index'))->assertSee('data-health-banner', false)->assertSee('THEMIS');
    }

    public function test_the_health_banner_is_hidden_from_agents_and_shown_to_administrators(): void
    {
        DataSource::factory()->create(['name' => 'THEMIS', 'status' => DataSourceStatus::Degraded]);

        $this->signInAs(Role::Agent);
        $this->get(route('dashboard'))->assertDontSee('data-health-banner', false);

        $this->signInAs(Role::Admin);
        $this->get(route('dashboard'))->assertSee('data-health-banner', false);
    }

    #[Covers('UC27', partial: true)]
    public function test_supervisors_review_the_requests_made_to_the_ai_agent(): void
    {
        $this->signInAs(Role::Director);
        $this->seed(RolesAndPermissionsSeeder::class);
        $request = AiAgentAction::factory()->create(['instruction' => 'Prévenir le chef de quart']);

        $this->get(route('reports.ai-agent.index'))->assertOk()->assertSee($request->instruction);
    }
}
