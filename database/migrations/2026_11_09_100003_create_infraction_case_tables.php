<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUS_MAP = ['reported' => 'open', 'under_investigation' => 'instruction', 'sanctioned' => 'decision', 'closed' => 'closed', 'dismissed' => 'closed'];

    public function up(): void
    {
        Schema::create('infraction_cases', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 60)->unique();
            $table->foreignId('vessel_id')->constrained();
            $table->foreignId('captain_id')->nullable()->constrained('people');
            $table->foreignId('shipowner_id')->nullable()->constrained('legal_entities');
            $table->foreignId('operation_id')->nullable()->constrained();
            $table->foreignId('inspection_id')->nullable()->constrained('external_inspections');
            $table->foreignId('mission_id')->nullable()->constrained();
            $table->unsignedBigInteger('alert_id')->nullable();
            $table->foreignId('witness_report_id')->nullable()->constrained('witness_reports');
            $table->string('qualification', 40)->default('presumed');
            $table->text('facts')->nullable();
            $table->text('captain_observations')->nullable();
            $table->string('status', 40)->default('open');
            $table->foreignId('opened_by')->nullable()->constrained('users');
            $table->timestamp('opened_at');
            $table->timestamps();

            $table->index(['status', 'opened_at']);
            $table->index('captain_id');
            $table->index('shipowner_id');
        });

        DB::statement("ALTER TABLE infraction_cases ADD CONSTRAINT infraction_cases_qualification_check CHECK (qualification IN ('presumed', 'established'))");
        DB::statement("ALTER TABLE infraction_cases ADD CONSTRAINT infraction_cases_status_check CHECK (status IN ('open', 'instruction', 'decision', 'closed'))");

        Schema::create('upheld_infractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraction_case_id')->constrained();
            $table->foreignId('infraction_type_id')->constrained();
            $table->timestamp('upheld_at');
            $table->timestamps();
        });

        Schema::create('evidence_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraction_case_id')->constrained();
            $table->string('description');
            $table->foreignId('document_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::create('seals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraction_case_id')->constrained();
            $table->string('number', 60);
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('precautionary_measures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraction_case_id')->constrained();
            $table->string('type', 40);
            $table->foreignId('proposed_by')->nullable()->constrained('users');
            $table->foreignId('decided_by')->nullable()->constrained('users');
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE precautionary_measures ADD CONSTRAINT precautionary_measures_type_check CHECK (type IN ('immobilization', 'port_services_refusal', 'gear_seizure', 'catch_seizure', 'license_suspension', 'license_withdrawal', 'diversion', 'seals', 'cessation_order'))");

        Schema::create('seizures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraction_case_id')->constrained();
            $table->string('item');
            $table->string('decision', 40)->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE seizures ADD CONSTRAINT seizures_decision_check CHECK (decision IN ('return', 'destruction', 'confiscation'))");

        Schema::create('violation_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraction_case_id')->unique()->constrained();
            $table->string('number', 60);
            $table->timestamp('notified_at')->nullable();
            $table->boolean('is_signature_refused')->default(false);
            $table->foreignId('document_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::create('litigation_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infraction_case_id')->constrained();
            $table->string('type', 40);
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE litigation_follow_ups ADD CONSTRAINT litigation_follow_ups_type_check CHECK (type IN ('warning', 'administrative_penalty', 'settlement', 'forward_to_hierarchy', 'forward_to_court'))");

        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follow_up_id')->constrained('litigation_follow_ups');
            $table->string('label');
            $table->foreignId('decided_by')->constrained('users');
            $table->timestamp('decided_at');
            $table->boolean('is_penalty')->default(false);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained();
            $table->decimal('amount', 14, 2);
            $table->char('currency', 3)->default('XOF');
            $table->date('paid_on')->nullable();
            $table->timestamps();
        });

        Schema::create('appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decision_id')->constrained();
            $table->date('filed_on');
            $table->string('outcome')->nullable();
            $table->timestamps();
        });

        DB::statement('CREATE OR REPLACE VIEW v_repeat_offenses AS SELECT d.vessel_id, d.captain_id, d.shipowner_id, COUNT(*) AS cases_count FROM infraction_cases d GROUP BY d.vessel_id, d.captain_id, d.shipowner_id');

        DB::table('license_suspensions')->update(['infraction_case_id' => null]);
        DB::table('license_revocations')->update(['infraction_case_id' => null]);

        Schema::table('license_suspensions', function (Blueprint $table) {
            $table->foreign('infraction_case_id')->references('id')->on('infraction_cases');
        });

        Schema::table('license_revocations', function (Blueprint $table) {
            $table->foreign('infraction_case_id')->references('id')->on('infraction_cases');
        });

        $this->copyLegacyInfractions();

        Schema::dropIfExists('infractions');
        Schema::dropIfExists('inspections');
    }

    public function down(): void
    {
        Schema::table('license_revocations', function (Blueprint $table) {
            $table->dropForeign(['infraction_case_id']);
        });

        Schema::table('license_suspensions', function (Blueprint $table) {
            $table->dropForeign(['infraction_case_id']);
        });

        DB::table('license_suspensions')->update(['infraction_case_id' => null]);
        DB::table('license_revocations')->update(['infraction_case_id' => null]);

        DB::statement('DROP VIEW IF EXISTS v_repeat_offenses');

        foreach (['appeals', 'payments', 'decisions', 'litigation_follow_ups', 'violation_reports', 'seizures', 'precautionary_measures', 'seals', 'evidence_items', 'upheld_infractions', 'infraction_cases'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('port_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_reference')->nullable()->unique();
            $table->string('type');
            $table->timestamp('inspected_at');
            $table->string('inspectors');
            $table->string('result');
            $table->text('findings')->nullable();
            $table->timestamps();

            $table->index('inspected_at');
        });

        Schema::create('infractions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('infraction_type_id')->constrained();
            $table->timestamp('detected_at');
            $table->text('facts');
            $table->text('evidence')->nullable();
            $table->string('status');
            $table->text('decision')->nullable();
            $table->string('sanction')->nullable();
            $table->timestamps();

            $table->index(['status', 'detected_at']);
        });
    }

    private function copyLegacyInfractions(): void
    {
        $deciderId = DB::table('users')->orderBy('id')->value('id');
        $inspections = DB::table('inspections')->pluck('external_reference', 'id');

        foreach (DB::table('infractions')->orderBy('id')->get() as $legacy) {
            $status = self::STATUS_MAP[$legacy->status] ?? 'open';
            $inspectionId = isset($inspections[$legacy->inspection_id])
                ? DB::table('external_inspections')->where('reference', $inspections[$legacy->inspection_id])->orWhere('external_id', $inspections[$legacy->inspection_id])->value('id')
                : null;

            DB::table('infraction_cases')->insert([
                'id' => $legacy->id,
                'reference' => $legacy->reference,
                'vessel_id' => $legacy->vessel_id,
                'inspection_id' => $inspectionId,
                'qualification' => in_array($legacy->status, ['sanctioned', 'closed'], true) ? 'established' : 'presumed',
                'facts' => $legacy->facts,
                'status' => $status,
                'opened_at' => $legacy->detected_at,
                'created_at' => $legacy->created_at,
                'updated_at' => $legacy->updated_at,
            ]);

            DB::table('upheld_infractions')->insert([
                'infraction_case_id' => $legacy->id,
                'infraction_type_id' => $legacy->infraction_type_id,
                'upheld_at' => $legacy->detected_at,
                'created_at' => $legacy->created_at,
                'updated_at' => $legacy->updated_at,
            ]);

            if (filled($legacy->evidence)) {
                DB::table('evidence_items')->insert([
                    'infraction_case_id' => $legacy->id,
                    'description' => mb_substr($legacy->evidence, 0, 255),
                    'created_at' => $legacy->created_at,
                    'updated_at' => $legacy->updated_at,
                ]);
            }

            if ($deciderId !== null && (filled($legacy->decision) || filled($legacy->sanction))) {
                $followUpId = DB::table('litigation_follow_ups')->insertGetId([
                    'infraction_case_id' => $legacy->id,
                    'type' => $legacy->status === 'sanctioned' ? 'administrative_penalty' : 'warning',
                    'opened_at' => $legacy->detected_at,
                    'closed_at' => $status === 'closed' ? $legacy->updated_at : null,
                    'created_at' => $legacy->created_at,
                    'updated_at' => $legacy->updated_at,
                ]);

                DB::table('decisions')->insert([
                    'follow_up_id' => $followUpId,
                    'label' => mb_substr(trim(($legacy->decision ?? '').' '.($legacy->sanction ?? '')), 0, 255),
                    'decided_by' => $deciderId,
                    'decided_at' => $legacy->updated_at,
                    'is_penalty' => $legacy->status === 'sanctioned',
                    'created_at' => $legacy->created_at,
                    'updated_at' => $legacy->updated_at,
                ]);
            }
        }

        DB::statement("SELECT setval(pg_get_serial_sequence('infraction_cases', 'id'), COALESCE((SELECT MAX(id) FROM infraction_cases), 1), (SELECT COUNT(*) > 0 FROM infraction_cases))");
    }
};
