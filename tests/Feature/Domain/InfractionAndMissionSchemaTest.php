<?php

namespace Tests\Feature\Domain;

use App\Models\InfractionCase;
use App\Models\LicenseRevocation;
use App\Models\LicenseSuspension;
use App\Models\WitnessReport;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InfractionAndMissionSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_table_of_phase_six_exists_and_the_legacy_tables_are_gone(): void
    {
        $tables = ['witnesses', 'witness_reports', 'reported_vessels', 'report_usabilities', 'report_investigations', 'report_decisions', 'authorized_list_distributions', 'infraction_types', 'infraction_cases', 'upheld_infractions', 'evidence_items', 'seals', 'precautionary_measures', 'seizures', 'violation_reports', 'litigation_follow_ups', 'decisions', 'payments', 'appeals', 'missions', 'mission_authorizations', 'mission_participants', 'observers', 'observer_boardings', 'assets', 'mission_assets', 'mission_briefings', 'mission_preparations', 'patrol_tracks', 'observed_units', 'mission_events', 'mission_debriefings', 'mission_reviews'];

        $this->assertCount(33, $tables);
        $this->assertSame([], array_values(array_filter($tables, fn (string $table) => ! Schema::hasTable($table))));
        $this->assertSame([], array_values(array_filter(['infractions', 'inspections', 'mission_vessel', 'mission_user', 'legacy_missions'], fn (string $table) => Schema::hasTable($table))));
    }

    public function test_licence_suspensions_and_revocations_are_tied_to_infraction_cases_by_a_real_foreign_key(): void
    {
        $this->expectException(QueryException::class);

        LicenseSuspension::factory()->create(['infraction_case_id' => 999999]);
    }

    public function test_revocations_are_tied_to_infraction_cases_by_a_real_foreign_key(): void
    {
        $case = InfractionCase::factory()->create();
        $revocation = LicenseRevocation::factory()->create(['infraction_case_id' => $case->id]);

        $this->assertSame($case->id, $revocation->infraction_case_id);
        $this->expectException(QueryException::class);

        DB::table('license_revocations')->where('id', $revocation->id)->update(['infraction_case_id' => 999999]);
    }

    public function test_enumerations_are_enforced_by_the_database(): void
    {
        $this->expectException(QueryException::class);

        DB::table('infraction_cases')->insert(['reference' => 'DI-X', 'vessel_id' => InfractionCase::factory()->create()->vessel_id, 'status' => 'invalid', 'opened_at' => now()]);
    }

    public function test_one_violation_report_per_case_and_two_reported_vessels_at_most(): void
    {
        $case = InfractionCase::factory()->create();
        DB::table('violation_reports')->insert(['infraction_case_id' => $case->id, 'number' => 'PV-1']);

        $this->expectException(QueryException::class);

        DB::table('violation_reports')->insert(['infraction_case_id' => $case->id, 'number' => 'PV-2']);
    }

    public function test_a_third_reported_vessel_rank_is_refused(): void
    {
        $report = WitnessReport::factory()->create();

        $this->expectException(QueryException::class);

        DB::table('reported_vessels')->insert(['witness_report_id' => $report->id, 'rank' => 3]);
    }
}
