<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TYPE_MAP = ['sea_patrol' => 'maritime', 'port_control' => 'port', 'joint_operation' => 'maritime'];

    private const STATUS_MAP = ['planned' => 'scheduled', 'in_progress' => 'in_progress', 'completed' => 'completed', 'cancelled' => 'closed'];

    public function up(): void
    {
        Schema::rename('missions', 'legacy_missions');
        DB::statement('ALTER TABLE legacy_missions RENAME CONSTRAINT missions_pkey TO legacy_missions_pkey');
        DB::statement('ALTER SEQUENCE missions_id_seq RENAME TO legacy_missions_id_seq');
        DB::statement('ALTER INDEX IF EXISTS missions_reference_unique RENAME TO legacy_missions_reference_unique');

        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('nature', 40);
            $table->string('type', 40);
            $table->string('origin', 80)->nullable();
            $table->text('objectives')->nullable();
            $table->timestamp('planned_start_at')->nullable();
            $table->timestamp('planned_end_at')->nullable();
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_end_at')->nullable();
            $table->jsonb('zones')->nullable();
            $table->jsonb('points_gps')->nullable();
            $table->string('status', 40)->default('scheduled');
            $table->foreignId('operation_id')->nullable()->constrained();
            $table->unsignedBigInteger('alert_id')->nullable();
            $table->foreignId('witness_report_id')->nullable()->constrained('witness_reports');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['status', 'planned_start_at']);
        });

        DB::statement("ALTER TABLE missions ADD CONSTRAINT missions_nature_check CHECK (nature IN ('internal', 'joint'))");
        DB::statement("ALTER TABLE missions ADD CONSTRAINT missions_type_check CHECK (type IN ('maritime', 'lagoon', 'continental', 'port', 'aerial_drone'))");
        DB::statement("ALTER TABLE missions ADD CONSTRAINT missions_status_check CHECK (status IN ('scheduled', 'authorized', 'in_progress', 'completed', 'closed'))");

        Schema::create('mission_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained();
            $table->foreignId('authorized_by')->constrained('users');
            $table->string('authorizing_role', 40);
            $table->timestamp('authorized_at');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE mission_authorizations ADD CONSTRAINT mission_authorizations_authorizing_role_check CHECK (authorizing_role IN ('director', 'team_lead'))");

        Schema::create('mission_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained();
            $table->foreignId('agent_id')->nullable()->constrained();
            $table->string('role', 60)->nullable();
            $table->string('agency', 120)->nullable();
            $table->string('grade', 80)->nullable();
            $table->boolean('is_mission_lead')->default(false);
            $table->timestamps();
        });

        Schema::create('observers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people');
            $table->timestamps();
        });

        Schema::create('observer_boardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained();
            $table->foreignId('observer_id')->constrained();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('label', 160);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE assets ADD CONSTRAINT assets_type_check CHECK (type IN ('intervention_boat', 'patrol_boat', 'vehicle', 'drone', 'vhf', 'gps', 'inspection_equipment', 'seals'))");

        Schema::create('mission_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained();
            $table->foreignId('asset_id')->constrained();
            $table->decimal('engine_hours', 8, 1)->nullable();
            $table->decimal('nautical_miles', 8, 1)->nullable();
            $table->decimal('planned_fuel_l', 10, 1)->nullable();
            $table->decimal('consumed_fuel_l', 10, 1)->nullable();
            $table->string('state', 60)->nullable();
            $table->text('breakdown')->nullable();
            $table->decimal('budget', 14, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('mission_briefings', function (Blueprint $table) {
            $table->foreignId('mission_id')->primary()->constrained();
            $table->timestamp('held_at')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('mission_preparations', function (Blueprint $table) {
            $table->foreignId('mission_id')->primary()->constrained();
            $table->jsonb('checklist')->nullable();
        });

        Schema::create('patrol_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained();
            $table->foreignId('document_id')->nullable()->constrained();
            $table->jsonb('zones_crossed')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE patrol_tracks ADD COLUMN geometry geometry(LineString, 4326)');
        DB::statement('CREATE INDEX patrol_tracks_geometry_gist ON patrol_tracks USING GIST (geometry)');

        Schema::create('observed_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->string('level', 40);
            $table->string('sighting_source', 40)->nullable();
            $table->string('recorded_name', 190)->nullable();
            $table->string('recorded_marks', 160)->nullable();
            $table->string('observed_activity', 160)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->timestamp('observed_at');
            $table->foreignId('inspection_id')->nullable()->constrained('external_inspections');
            $table->timestamps();

            $table->index('vessel_id');
        });

        DB::statement("ALTER TABLE observed_units ADD CONSTRAINT observed_units_level_check CHECK (level IN ('sighted', 'approached', 'inspected'))");
        DB::statement("ALTER TABLE observed_units ADD CONSTRAINT observed_units_sighting_source_check CHECK (sighting_source IN ('visual', 'radar', 'vms'))");
        DB::statement("ALTER TABLE observed_units ADD CONSTRAINT observed_units_inspected_requires_inspection_check CHECK (level <> 'inspected' OR inspection_id IS NOT NULL)");

        Schema::create('mission_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained();
            $table->timestamp('occurred_at');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('mission_debriefings', function (Blueprint $table) {
            $table->foreignId('mission_id')->primary()->constrained();
            $table->timestamp('held_at')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('mission_reviews', function (Blueprint $table) {
            $table->foreignId('mission_id')->primary()->constrained();
            $table->integer('agents_count')->nullable();
            $table->decimal('duration_hours', 8, 1)->nullable();
            $table->decimal('nautical_miles', 8, 1)->nullable();
            $table->decimal('fuel_l', 10, 1)->nullable();
            $table->integer('sighted_units_count')->nullable();
            $table->integer('approached_units_count')->nullable();
            $table->integer('inspected_units_count')->nullable();
            $table->integer('compliant_count')->nullable();
            $table->integer('non_compliant_count')->nullable();
            $table->integer('infractions_count')->nullable();
            $table->integer('violation_reports_count')->nullable();
            $table->integer('diversions_count')->nullable();
            $table->integer('immobilizations_count')->nullable();
            $table->integer('seizures_count')->nullable();
            $table->integer('arrests_count')->nullable();
            $table->integer('penalties_count')->nullable();
            $table->timestamp('calculated_at');
        });

        DB::statement("CREATE OR REPLACE VIEW v_seen_never_inspected AS SELECT u.vessel_id, SUM(CASE WHEN u.level IN ('sighted', 'approached') THEN 1 ELSE 0 END) AS sighted_count, SUM(CASE WHEN u.level = 'inspected' THEN 1 ELSE 0 END) AS inspected_count FROM observed_units u WHERE u.vessel_id IS NOT NULL GROUP BY u.vessel_id HAVING SUM(CASE WHEN u.level = 'inspected' THEN 1 ELSE 0 END) = 0 AND SUM(CASE WHEN u.level IN ('sighted', 'approached') THEN 1 ELSE 0 END) > 0");

        $this->copyLegacyMissions();

        Schema::dropIfExists('mission_vessel');
        Schema::dropIfExists('mission_user');
        Schema::dropIfExists('legacy_missions');
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS v_seen_never_inspected');

        foreach (['mission_reviews', 'mission_debriefings', 'mission_events', 'observed_units', 'patrol_tracks', 'mission_preparations', 'mission_briefings', 'mission_assets', 'assets', 'observer_boardings', 'observers', 'mission_participants', 'mission_authorizations', 'missions'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->text('objective');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('status');
            $table->text('results')->nullable();
            $table->timestamps();
        });

        Schema::create('mission_vessel', function (Blueprint $table) {
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->primary(['mission_id', 'vessel_id']);
        });

        Schema::create('mission_user', function (Blueprint $table) {
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['mission_id', 'user_id']);
        });
    }

    private function copyLegacyMissions(): void
    {
        $zones = DB::table('zones')->pluck('name', 'id');

        foreach (DB::table('legacy_missions')->orderBy('id')->get() as $legacy) {
            DB::table('missions')->insert([
                'id' => $legacy->id,
                'code' => substr($legacy->reference, 0, 20),
                'nature' => $legacy->type === 'joint_operation' ? 'joint' : 'internal',
                'type' => self::TYPE_MAP[$legacy->type] ?? 'maritime',
                'objectives' => $legacy->objective,
                'planned_start_at' => $legacy->starts_at,
                'planned_end_at' => $legacy->ends_at,
                'actual_start_at' => in_array($legacy->status, ['in_progress', 'completed'], true) ? $legacy->starts_at : null,
                'actual_end_at' => $legacy->status === 'completed' ? $legacy->ends_at : null,
                'zones' => $legacy->zone_id ? json_encode([['id' => $legacy->zone_id, 'name' => $zones[$legacy->zone_id] ?? null]]) : null,
                'status' => self::STATUS_MAP[$legacy->status] ?? 'scheduled',
                'created_at' => $legacy->created_at,
                'updated_at' => $legacy->updated_at,
            ]);

            if ($legacy->results) {
                DB::table('mission_debriefings')->insert(['mission_id' => $legacy->id, 'held_at' => $legacy->ends_at, 'notes' => $legacy->results]);
            }
        }

        DB::statement("SELECT setval(pg_get_serial_sequence('missions', 'id'), COALESCE((SELECT MAX(id) FROM missions), 1), (SELECT COUNT(*) > 0 FROM missions))");

        $this->copyLegacyParticipants();
        $this->copyLegacyVessels();
    }

    private function copyLegacyParticipants(): void
    {
        foreach (DB::table('mission_user')->get() as $link) {
            $agentId = DB::table('agents')->where('user_id', $link->user_id)->value('id');

            if ($agentId === null) {
                $user = DB::table('users')->where('id', $link->user_id)->first();
                $agentId = DB::table('agents')->insertGetId([
                    'user_id' => $link->user_id,
                    'name' => $user->name,
                    'agency' => 'CSP',
                    'is_inspector' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $agent = DB::table('agents')->where('id', $agentId)->first();

            DB::table('mission_participants')->insert([
                'mission_id' => $link->mission_id,
                'agent_id' => $agentId,
                'agency' => $agent->agency,
                'grade' => $agent->grade,
                'is_mission_lead' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function copyLegacyVessels(): void
    {
        foreach (DB::table('mission_vessel')->get() as $link) {
            $vessel = DB::table('vessels')->where('id', $link->vessel_id)->first();
            $mission = DB::table('missions')->where('id', $link->mission_id)->first();

            if ($vessel->category === 'patrol') {
                $assetId = DB::table('assets')->where('label', $vessel->name)->value('id')
                    ?? DB::table('assets')->insertGetId(['type' => 'patrol_boat', 'label' => $vessel->name, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

                DB::table('mission_assets')->insert(['mission_id' => $link->mission_id, 'asset_id' => $assetId, 'created_at' => now(), 'updated_at' => now()]);

                continue;
            }

            DB::table('observed_units')->insert([
                'mission_id' => $link->mission_id,
                'vessel_id' => $link->vessel_id,
                'level' => 'approached',
                'recorded_name' => $vessel->name,
                'observed_at' => $mission->actual_start_at ?? $mission->planned_start_at,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
