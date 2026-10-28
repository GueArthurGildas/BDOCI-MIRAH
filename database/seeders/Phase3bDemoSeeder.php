<?php

namespace Database\Seeders;

use App\Enums\AlertRuleType;
use App\Enums\AssessmentResult;
use App\Enums\BeaconModem;
use App\Enums\VmsFailureCategory;
use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\ConsistencyRule;
use App\Models\ControlSlot;
use App\Models\EmissionCutoff;
use App\Models\Gear;
use App\Models\IdentityAnomaly;
use App\Models\Position;
use App\Models\Presentation;
use App\Models\Species;
use App\Models\TrackingDevice;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselReportingObligation;
use App\Support\Actions\ActionRunner;
use App\Support\Vms\AlertRuleEvaluator;
use App\Support\Vms\AssistedTests;
use App\Support\Vms\BeaconMirror;
use App\Support\Vms\LocalityResolver;
use App\Support\Vms\TrackBuilder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Modules\Tracking\Actions\AcknowledgeAlert;
use Modules\Tracking\Actions\ConfirmSlotPosition;
use Modules\Tracking\Actions\HandleAlert;
use Modules\Tracking\Actions\OpenControlSlot;
use Modules\Tracking\Actions\RunSweep;
use Modules\Vms\Actions\AuthorizeEmissionCutoff;
use Modules\Vms\Actions\CreateApprovedInstaller;
use Modules\Vms\Actions\DeclareVmsFailure;
use Modules\Vms\Actions\IssueCessationOrder;
use Modules\Vms\Actions\RecordBeaconInstallation;
use Modules\Vms\Actions\RecordCompatibilityAssessment;
use Modules\Vms\Actions\RecordErsDeclaration;
use Modules\Vms\Actions\RecordManualPositionReport;
use Modules\Vms\Actions\RecordWorkingOrderAttestation;
use Modules\Vms\Actions\RequestNewBeacon;
use Modules\Vms\Actions\RequestVmsAssessment;

class Phase3bDemoSeeder extends Seeder
{
    private const COASTLINE = [
        [-7.55, 4.35], [-7.35, 4.42], [-6.64, 4.74], [-6.08, 4.95], [-5.57, 5.08],
        [-5.02, 5.14], [-4.42, 5.20], [-4.01, 5.25], [-3.74, 5.19], [-3.28, 5.12], [-2.75, 5.10],
    ];

    private const BEACON_MODELS = [
        ['SkyTrack 200', 'CLS', BeaconModem::Satellite],
        ['MarineLink M3', 'Satlink', BeaconModem::Gprs],
        ['FT2225', 'Thrane', BeaconModem::Gnss],
    ];

    private const INSTALLERS = ['CLS Côte d\'Ivoire', 'Ivoire Marine Électronique'];

    private ActionRunner $runner;

    public function run(): void
    {
        fake()->seed(3002);
        $this->runner = app(ActionRunner::class);
        $director = User::where('email', 'director@example.com')->firstOrFail();
        $agent = User::where('email', 'agent@example.com')->firstOrFail();

        app(LocalityResolver::class)->ensure();
        $this->seedRules();
        $this->seedBeacons();
        $this->seedPositionDetails();
        $this->seedConformity($director, $agent);
        $this->seedFailures($director, $agent);
        $this->seedSlots($agent);
        $this->seedAlerts($agent);
        $this->seedSummaries();
        $this->seedErs($agent);
        $this->seedMiscellaneous();
    }

    private function seedRules(): void
    {
        AlertRule::create(['code' => 'silence_vms', 'type' => AlertRuleType::Silence, 'threshold_hours' => 4]);
        AlertRule::create(['code' => 'restricted_zone', 'type' => AlertRuleType::Geofencing, 'threshold_hours' => null]);
        AlertRule::create(['code' => 'speed_limit', 'type' => AlertRuleType::Speed, 'threshold_hours' => null, 'parameters' => ['max_knots' => 25]]);

        ConsistencyRule::create(['code' => AssistedTests::SPEED_ACTIVITY_RULE, 'description' => 'Speed coherent with the declared activity', 'parameters' => AssistedTests::DEFAULT_SPEED_ACTIVITY]);
        ConsistencyRule::create([
            'code' => AssistedTests::TRAJECTORY_RULE,
            'description' => 'Trajectory within the authorized zones and beyond the coast distance',
            'parameters' => [
                'window_hours' => 6,
                'coastline' => ['type' => 'LineString', 'coordinates' => self::COASTLINE],
            ],
        ]);
    }

    private function seedBeacons(): void
    {
        app(BeaconMirror::class)->importLegacyDevices();

        DB::table('beacons')->orderBy('id')->get()->each(function ($beacon, int $index) {
            [$model, $manufacturer, $modem] = self::BEACON_MODELS[$index % count(self::BEACON_MODELS)];

            DB::table('beacons')->where('id', $beacon->id)->update([
                'model' => $model, 'manufacturer' => $manufacturer, 'supplier' => 'CLS Abidjan', 'modem' => $modem->value,
                'subscription' => sprintf('SUB-%05d', 1000 + $beacon->id),
            ]);
        });
    }

    private function seedPositionDetails(): void
    {
        DB::statement("UPDATE positions SET activity = 'moored' WHERE speed_knots IS NOT NULL AND speed_knots < 0.5");
        DB::statement("UPDATE positions SET activity = CASE WHEN speed_knots >= 6 THEN 'transit' ELSE 'fishing' END WHERE activity IS NULL AND speed_knots IS NOT NULL AND id % 3 <> 0");
        DB::statement("UPDATE positions SET received_at = recorded_at + interval '3 minutes'");
    }

    private function seedConformity($director, $agent): void
    {
        $equipped = Vessel::whereIn('id', TrackingDevice::where('system', 'vms')->whereNull('removed_at')->pluck('vessel_id'))->orderBy('id')->get();

        foreach ($equipped as $vessel) {
            VesselReportingObligation::create([
                'vessel_id' => $vessel->id,
                'requires_vms' => true,
                'requires_ais' => false,
                'requires_ers' => $vessel->flag !== 'CI',
                'requires_eez_declaration' => $vessel->flag !== 'CI',
                'requires_fishing_log' => $vessel->flag === 'CI',
            ]);
        }

        Vessel::where('category', 'fishing')->whereNotIn('id', $equipped->pluck('id'))->orderBy('id')->limit(4)->get()
            ->each(fn (Vessel $vessel) => VesselReportingObligation::create(['vessel_id' => $vessel->id, 'requires_vms' => true, 'requires_fishing_log' => true]));

        $installers = collect(self::INSTALLERS)->map(fn (string $name) => $this->runner->run(CreateApprovedInstaller::class, $director, ['name' => $name, 'approved_until' => today()->addYear()->toDateString()]));

        $unequipped = Vessel::whereIn('id', VesselReportingObligation::where('requires_vms', true)->pluck('vessel_id'))->whereNotIn('id', DB::table('beacon_assignments')->whereNull('ended_on')->pluck('vessel_id'))->get();

        foreach ($unequipped->take(2) as $index => $vessel) {
            $request = $this->runner->run(RequestVmsAssessment::class, $agent, ['vessel_id' => $vessel->id, 'requested_on' => today()->subDays(10 + $index)->toDateString()]);
            $this->runner->run(RecordCompatibilityAssessment::class, $agent, ['assessment_request_id' => $request->id, 'result' => [AssessmentResult::Compatible, AssessmentResult::ToComplete][$index]->value]);
        }

        $newBeacon = $this->runner->run(RequestNewBeacon::class, $agent, ['vessel_id' => $unequipped[2]->id, 'requested_on' => today()->subDays(6)->toDateString()]);
        $serial = DB::table('beacons')->insertGetId(['serial_number' => 'BCN-NEW-0001', 'model' => 'SkyTrack 200', 'manufacturer' => 'CLS', 'modem' => 'satellite', 'created_at' => now(), 'updated_at' => now()]);
        $this->runner->run(RecordBeaconInstallation::class, $agent, ['new_beacon_request_id' => $newBeacon->id, 'installer_id' => $installers[0]->id, 'beacon_id' => $serial, 'installed_on' => today()->subDays(2)->toDateString()]);

        $this->runner->run(RequestNewBeacon::class, $agent, ['vessel_id' => $unequipped[3]->id, 'requested_on' => today()->subDays(3)->toDateString()]);
    }

    private function seedFailures($director, $agent): void
    {
        $vessels = Vessel::whereIn('id', DB::table('beacon_assignments')->whereNull('ended_on')->pluck('vessel_id'))->orderBy('id')->limit(8)->get();

        $overdue = $this->runner->run(DeclareVmsFailure::class, $agent, ['vessel_id' => $vessels[0]->id, 'category' => VmsFailureCategory::Foreign->value, 'started_at' => now()->subDays(9)->toDateTimeString()]);
        $this->runner->run(RecordManualPositionReport::class, $agent, ['vms_failure_id' => $overdue->id, 'latitude' => 4.35, 'longitude' => -5.9, 'received_at' => now()->subDays(8)->toDateTimeString()]);
        $this->runner->run(IssueCessationOrder::class, $director, ['vms_failure_id' => $overdue->id, 'includes_suspension' => true, 'requires_port_return' => true]);

        $ongoing = $this->runner->run(DeclareVmsFailure::class, $agent, ['vessel_id' => $vessels[1]->id, 'category' => VmsFailureCategory::Ivorian->value, 'started_at' => now()->subDays(3)->toDateTimeString()]);
        foreach ([2, 1] as $index => $daysAgo) {
            $this->runner->run(RecordManualPositionReport::class, $agent, ['vms_failure_id' => $ongoing->id, 'latitude' => 4.9 - $index * 0.1, 'longitude' => -6.2 + $index * 0.2, 'received_at' => now()->subDays($daysAgo)->toDateTimeString()]);
        }

        $repaired = $this->runner->run(DeclareVmsFailure::class, $agent, ['vessel_id' => $vessels[2]->id, 'category' => VmsFailureCategory::TunaVessel->value, 'started_at' => now()->subDays(20)->toDateTimeString()]);
        $this->runner->run(RecordWorkingOrderAttestation::class, $agent, ['vms_failure_id' => $repaired->id, 'attested_on' => today()->subDays(12)->toDateString()]);

        $this->runner->run(AuthorizeEmissionCutoff::class, $director, [
            'vessel_id' => $vessels[3]->id, 'started_at' => now()->subDays(1)->toDateTimeString(), 'ended_at' => now()->addDays(2)->toDateTimeString(), 'reason' => 'Arrêt technique au port',
        ]);
    }

    private function seedSlots($agent): void
    {
        $slot = $this->runner->run(OpenControlSlot::class, $agent);

        $slot->slotPositions()->orderBy('id')->limit(6)->get()->each(fn ($position) => $this->runner->run(ConfirmSlotPosition::class, $agent, [
            'slot_position_id' => $position->id,
            'emission_test' => $position->emission_test->value,
            'speed_activity_test' => $position->speed_activity_test->value === 'to_confirm' ? 'ok' : $position->speed_activity_test->value,
            'trajectory_test' => $position->trajectory_test->value === 'to_confirm' ? 'ok' : $position->trajectory_test->value,
        ]));

        $this->runner->run(RunSweep::class, $agent, ['control_slot_id' => $slot->id]);

        ControlSlot::factory()->create(['started_at' => now()->subHours(6)->startOfHour(), 'ended_at' => now()->subHours(4)->startOfHour(), 'operator_user_id' => $agent->id, 'team_id' => $agent->team_id]);
    }

    private function seedAlerts($agent): void
    {
        app(AlertRuleEvaluator::class)->evaluate();

        $alerts = Alert::orderBy('id')->limit(3)->get();

        if ($alerts->isEmpty()) {
            return;
        }

        $this->runner->run(AcknowledgeAlert::class, $agent, ['alert_id' => $alerts[0]->id]);

        if ($alerts->count() > 1) {
            $this->runner->run(HandleAlert::class, $agent, [
                'alert_id' => $alerts[1]->id,
                'verification_questions' => ['position_checked' => true, 'beacon_powered' => true],
                'is_vessel_contacted' => true,
                'measures' => 'Capitaine joint par radio VHF, retour du signal attendu sous 2 heures.',
            ]);
        }
    }

    private function seedSummaries(): void
    {
        Artisan::call('tracking:summarize-positions', ['--date' => today()->toDateString()]);
        Artisan::call('tracking:summarize-positions', ['--date' => today()->subDay()->toDateString()]);
        Artisan::call('tracking:aggregate-monthly', ['--month' => now()->format('Y-m')]);

        $builder = app(TrackBuilder::class);

        Position::where('recorded_at', '>=', now()->subDay())->distinct()->limit(15)->pluck('vessel_id')
            ->each(fn (int $vesselId) => $builder->build($vesselId, now()->subDay(), now()->addMinute()));
    }

    private function seedErs($agent): void
    {
        $species = Species::pluck('id', 'fao_code');
        $presentation = Presentation::first();
        $gear = Gear::first();

        Vessel::where('category', 'fishing')->whereIn('id', VesselReportingObligation::where('requires_ers', true)->pluck('vessel_id'))->orderBy('id')->limit(5)->get()
            ->each(fn (Vessel $vessel, int $index) => $this->runner->run(RecordErsDeclaration::class, $agent, [
                'vessel_id' => $vessel->id,
                'declared_at' => now()->subDays($index + 1)->toDateTimeString(),
                'source' => ['import', 'api', 'manual_entry'][$index % 3],
                'lines' => [
                    ['species_id' => $species['YFT'], 'presentation_id' => $presentation?->id, 'live_weight_kg' => 12000 + $index * 800, 'fao_zone' => '34.3.5', 'gear_id' => $gear?->id],
                    ['species_id' => $species['SKJ'], 'live_weight_kg' => 18000 - $index * 500, 'fao_zone' => '34.3.5'],
                ],
            ]));
    }

    private function seedMiscellaneous(): void
    {
        $vessels = Vessel::where('category', 'fishing')->orderBy('id')->limit(3)->get();

        IdentityAnomaly::create(['vessel_id' => $vessels[0]->id, 'detail' => 'Le nom diffusé en AIS diffère du nom déclaré', 'detected_at' => now()->subDays(2)]);
        IdentityAnomaly::create(['vessel_id' => $vessels[1]->id, 'detail' => 'MMSI AIS absent de la fiche navire', 'detected_at' => now()->subDay()]);
        EmissionCutoff::create(['vessel_id' => $vessels[2]->id, 'started_at' => now()->subDays(5), 'ended_at' => now()->subDays(4), 'is_technical_stop' => true]);
        EmissionCutoff::create(['vessel_id' => $vessels[0]->id, 'started_at' => now()->subHours(30), 'ended_at' => now()->subHours(26), 'is_technical_stop' => false]);
    }
}
