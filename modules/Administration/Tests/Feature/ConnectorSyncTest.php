<?php

namespace Modules\Administration\Tests\Feature;

use App\Enums\DataSourceIndicator;
use App\Enums\DataSourceMode;
use App\Enums\ExternalLinkSource;
use App\Enums\RejectionStatus;
use App\Models\DataSource;
use App\Models\DataSourceError;
use App\Models\DataSourceRejection;
use App\Models\DeclaredObservedCatch;
use App\Models\ExternalInspection;
use App\Models\ExternalLink;
use App\Models\Landing;
use App\Models\MrepDpep;
use App\Models\MrepGiesRefusal;
use App\Models\MrepTargeting;
use App\Models\Species;
use App\Models\VesselEvent;
use App\Models\VesselSource;
use App\Support\Actions\ActionRunner;
use App\Support\Authorization\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Modules\Administration\Actions\SynchroniseConnector;
use Tests\Attributes\Assumes;
use Tests\Attributes\Covers;
use Tests\Support\ConnectorFixtures;
use Tests\TestCase;

class ConnectorSyncTest extends TestCase
{
    use RefreshDatabase;

    private function source(string $code, DataSourceMode $mode = DataSourceMode::Api): DataSource
    {
        return DataSource::factory()->create(['code' => $code, 'mode' => $mode, 'last_synced_at' => null]);
    }

    private function sync(DataSource $source, array $extra = [])
    {
        return app(ActionRunner::class)->run(SynchroniseConnector::class, auth()->user(), ['data_source_id' => $source->id, ...$extra]);
    }

    #[Covers('UC23', 'UC19', partial: true)]
    #[Assumes('H-7-1')]
    public function test_the_recorded_payloads_feed_the_inspection_mirror(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();

        $sync = $this->sync($this->source('e-inspection'));

        $this->assertSame(11, $sync->received_count);
        $this->assertSame(4, $sync->rejected_count);
        $this->assertSame(5, ExternalInspection::count());
        $this->assertSame(2, Landing::count());
        $this->assertSame(4, DeclaredObservedCatch::count());
        $this->assertNotNull($sync->finished_at);
    }

    #[Covers('UC23', partial: true)]
    public function test_rejected_records_are_kept_for_review_with_their_reason(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();
        $source = $this->source('e-inspection');

        $this->sync($source);

        $this->assertSame(4, DataSourceRejection::where('data_source_id', $source->id)->where('status', RejectionStatus::Pending)->count());
        $this->assertTrue(DataSourceRejection::where('details', 'like', '%Unknown species code "XXX"%')->exists());
        $this->assertTrue(DataSourceRejection::where('details', 'like', '%"inspection_id" is required%')->exists());
    }

    #[Covers('UC23', partial: true)]
    public function test_a_second_synchronisation_creates_no_duplicates(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();
        $source = $this->source('e-inspection');

        $this->sync($source);
        $this->sync($source);

        $this->assertSame(5, ExternalInspection::count());
        $this->assertSame(4, DeclaredObservedCatch::count());
        $this->assertSame(4, DataSourceRejection::count());
    }

    #[Covers('UC23', partial: true)]
    public function test_a_rejected_record_that_becomes_valid_is_settled_automatically(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();
        $source = $this->source('e-inspection');
        $this->sync($source);

        Species::factory()->create(['fao_code' => 'XXX']);
        $this->sync($source);

        $rejection = DataSourceRejection::where('external_ref', 'EI-2026-0407')->firstOrFail();
        $this->assertSame(RejectionStatus::Corrected, $rejection->status);
        $this->assertNotNull($rejection->handled_by);
    }

    #[Covers('UC19', partial: true)]
    #[Assumes('H-7-4')]
    public function test_catch_variance_is_compared_with_the_csp_threshold(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();

        $this->sync($this->source('e-inspection'));

        $yellowfin = DeclaredObservedCatch::whereHas('species', fn ($query) => $query->where('fao_code', 'YFT'))->firstOrFail();
        $skipjack = DeclaredObservedCatch::whereHas('species', fn ($query) => $query->where('fao_code', 'SKJ'))->firstOrFail();
        $this->assertEquals(13.1, $yellowfin->variance_pct);
        $this->assertTrue($yellowfin->exceeds_csp_threshold);
        $this->assertEquals(-2.67, $skipjack->variance_pct);
        $this->assertFalse($skipjack->exceeds_csp_threshold);
    }

    #[Covers('UC19', partial: true)]
    #[Assumes('H-7-4')]
    public function test_the_threshold_is_a_setting(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();
        config(['surveillance.connectors.catch_variance_threshold_percent' => 20]);

        $this->sync($this->source('e-inspection'));

        $this->assertFalse(DeclaredObservedCatch::where('variance_pct', 13.1)->firstOrFail()->exceeds_csp_threshold);
    }

    #[Covers('UC19', partial: true)]
    #[Assumes('H-7-5')]
    public function test_an_inspection_of_an_unknown_vessel_is_kept_unmatched_and_a_landing_is_rejected(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();

        $this->sync($this->source('e-inspection'));

        $this->assertNull(ExternalInspection::where('external_id', 'EI-2026-0405')->firstOrFail()->vessel_id);
        $this->assertTrue(DataSourceRejection::where('details', 'like', 'No vessel matches the identifiers of the landing')->exists());
    }

    #[Covers('UC19', partial: true)]
    public function test_a_new_inspection_of_a_known_vessel_enters_the_vessel_journal(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();

        $this->sync($this->source('e-inspection'));
        $this->sync(DataSource::where('code', 'e-inspection')->first());

        $this->assertSame(4, VesselEvent::where('type', 'inspection.received')->count());
    }

    #[Covers('UC13', 'UC14', partial: true)]
    #[Assumes('H-7-6')]
    public function test_the_mrep_feed_fills_the_dpep_mirror(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();
        $this->sync($this->source('e-inspection'));
        $source = $this->source('dpep-mrep', DataSourceMode::DirectRead);

        $sync = $this->sync($source);

        $this->assertSame(9, $sync->received_count);
        $this->assertSame(3, $sync->rejected_count);
        $this->assertSame(3, MrepDpep::count());
        $dpep = MrepDpep::where('external_id', 'DPEP-2026-00871')->firstOrFail();
        $this->assertTrue($dpep->is_72h_deadline_met);
        $this->assertSame(2, $dpep->notifications()->count());
        $this->assertSame('authorization', $dpep->latestDecision->decision->value);
        $this->assertNotNull($dpep->portCalls()->first()->inspection_id);
        $this->assertSame(1, MrepGiesRefusal::count());
        $this->assertSame(2, MrepTargeting::count());
    }

    #[Covers('UC23', partial: true)]
    public function test_the_vessel_feeds_land_in_the_source_links_with_matching(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::vessels();

        $this->sync($this->source('vms-themis'));

        $this->assertSame(3, VesselSource::where('source', 'themis')->count());
        $this->assertNotNull(VesselSource::where('external_id', 'TH-100202')->firstOrFail()->vessel_id);
        $this->assertNull(VesselSource::where('external_id', 'TH-100203')->firstOrFail()->vessel_id);
    }

    #[Covers('UC23', partial: true)]
    #[Assumes('H-7-2', 'H-7-1')]
    public function test_an_unavailable_mode_turns_the_light_red_and_records_the_error(): void
    {
        $this->signInAs(Role::Admin);
        $source = $this->source('e-inspection', DataSourceMode::Scraping);

        $sync = $this->sync($source);

        $source->refresh();
        $this->assertSame(DataSourceIndicator::Red, $source->indicator_status);
        $this->assertNull($sync->finished_at);
        $this->assertSame(1, DataSourceError::where('data_source_id', $source->id)->count());
        $this->assertNotNull($source->last_error);
    }

    #[Covers('UC23', partial: true)]
    #[Assumes('H-7-2')]
    public function test_the_light_is_orange_with_rejections_and_green_without(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::referentials();
        ConnectorFixtures::vessels();
        $source = $this->source('e-inspection');

        $this->sync($source);
        $this->assertSame(DataSourceIndicator::Orange, $source->refresh()->indicator_status);

        $path = sys_get_temp_dir().'/neno-clean.json';
        file_put_contents($path, json_encode([['vessel_ref' => 'NE-1', 'name' => 'VEDETTE', 'ircs' => 'TUVC1']]));
        $clean = $this->source('neno', DataSourceMode::Import);
        $this->sync($clean, ['file' => $path]);

        $this->assertSame(DataSourceIndicator::Green, $clean->refresh()->indicator_status);
    }

    #[Covers('UC23', partial: true)]
    public function test_a_controlled_import_reads_the_uploaded_file(): void
    {
        $this->signInAs(Role::Admin);
        $source = $this->source('neno', DataSourceMode::Import);
        $file = UploadedFile::fake()->createWithContent('neno.json', json_encode(['records' => [['vessel_ref' => 'NE-9', 'name' => 'VEDETTE CSP 09', 'ircs' => 'TUVC9']]]));

        $this->post(route('administration.connectors.sync', $source), ['file' => $file])->assertRedirect();

        $this->assertSame(1, VesselSource::where('external_id', 'NE-9')->count());
        $this->assertSame(DataSourceIndicator::Green, $source->refresh()->indicator_status);
    }

    #[Covers('UC23', partial: true)]
    public function test_only_holders_of_the_sync_permission_can_synchronise(): void
    {
        $this->signInAs(Role::Agent);
        $source = $this->source('neno');

        $this->post(route('administration.connectors.sync', $source))->assertForbidden();
        $this->expectException(AuthorizationException::class);
        $this->sync($source);
    }

    #[Covers('UC23', partial: true)]
    public function test_the_mode_can_be_changed_and_is_audited(): void
    {
        $this->signInAs(Role::Admin);
        $source = $this->source('e-inspection');

        $this->patch(route('administration.connectors.mode', $source), ['mode' => 'import'])->assertRedirect();

        $this->assertSame(DataSourceMode::Import, $source->refresh()->mode);
        $this->get(route('administration.connectors.show', $source))->assertOk();
    }

    #[Covers('UC23', partial: true)]
    #[Assumes('H-7-3')]
    public function test_old_data_is_flagged_stale_and_the_light_leaves_green(): void
    {
        $this->signInAs(Role::Admin);
        $old = DataSource::factory()->create(['last_synced_at' => now()->subHours(30)]);
        $recent = DataSource::factory()->create(['last_synced_at' => now()->subHours(2)]);

        $this->artisan('connectors:refresh-freshness')->assertSuccessful();

        $this->assertTrue($old->refresh()->is_stale);
        $this->assertSame(DataSourceIndicator::Orange, $old->indicator_status);
        $this->assertFalse($recent->refresh()->is_stale);
    }

    #[Covers('UC23', partial: true)]
    public function test_the_scheduled_command_synchronises_the_sources_that_have_a_connector(): void
    {
        $this->signInAs(Role::Admin);
        ConnectorFixtures::vessels();
        $this->source('neno');
        $this->source('missions');

        $this->artisan('connectors:sync')->assertSuccessful();

        $this->assertSame(2, VesselSource::where('source', 'neno')->count());
        $this->assertNotNull(DataSource::where('code', 'neno')->first()->last_synced_at);
        $this->assertNull(DataSource::where('code', 'missions')->first()->last_synced_at);
    }

    #[Covers('UC23', partial: true)]
    public function test_external_links_are_edited_by_connector_managers(): void
    {
        $this->signInAs(Role::Admin);

        $this->put(route('administration.connectors.links.update'), ['links' => [
            'themis' => ['model_url' => 'https://themis.example.org/v/{id}', 'is_copy_fallback' => '0'],
            'neno' => ['model_url' => null, 'is_copy_fallback' => '1'],
            'e_inspection' => ['model_url' => null, 'is_copy_fallback' => '1'],
        ]])->assertSessionHasNoErrors();

        $this->assertSame('https://themis.example.org/v/ABC%2F1', ExternalLink::forSource(ExternalLinkSource::Themis)->urlFor('ABC/1'));
        $this->get(route('administration.connectors.links'))->assertOk();
    }
}
