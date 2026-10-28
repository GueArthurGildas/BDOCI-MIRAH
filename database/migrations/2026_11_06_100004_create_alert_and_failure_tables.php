<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('type', 40);
            $table->decimal('threshold_hours', 5, 2)->nullable()->default(4.00);
            $table->jsonb('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE alert_rules ADD CONSTRAINT alert_rules_type_check CHECK (type IN ('geofencing', 'silence', 'speed', 'zone', 'other'))");

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->foreignId('alert_rule_id')->nullable()->constrained('alert_rules');
            $table->string('type', 40);
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->timestamp('triggered_at');
            $table->string('status', 40)->default('open');
            $table->foreignId('acknowledged_by')->nullable()->constrained('users');
            $table->foreignId('logbook_entry_id')->nullable()->constrained('logbook_entries');
            $table->foreignId('operation_id')->nullable()->constrained('operations');
            $table->timestamps();

            $table->index(['status', 'triggered_at']);
            $table->index(['vessel_id', 'status']);
        });

        DB::statement("ALTER TABLE alerts ADD CONSTRAINT alerts_status_check CHECK (status IN ('open', 'acknowledged', 'closed'))");

        Schema::create('alert_handlings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_id')->constrained('alerts');
            $table->jsonb('verification_questions')->nullable();
            $table->boolean('is_vessel_contacted')->default(false);
            $table->text('measures')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users');
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vms_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->string('category', 40);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->smallInteger('report_frequency_h')->default(4);
            $table->smallInteger('repair_deadline_days');
            $table->foreignId('operation_id')->nullable()->constrained('operations');
            $table->timestamps();

            $table->index(['vessel_id', 'started_at']);
        });

        DB::statement("ALTER TABLE vms_failures ADD CONSTRAINT vms_failures_category_check CHECK (category IN ('foreign', 'ivorian', 'chartered', 'tuna_vessel'))");

        Schema::create('vms_cutoff_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_position_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vms_failure_id')->constrained('vms_failures');
            $table->timestamp('received_at');
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->foreignId('entered_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('working_order_attestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vms_failure_id')->unique()->constrained('vms_failures');
            $table->foreignId('document_id')->nullable()->constrained('documents');
            $table->date('attested_on');
            $table->timestamps();
        });

        Schema::create('cessation_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vms_failure_id')->unique()->constrained('vms_failures');
            $table->timestamp('ordered_at');
            $table->boolean('includes_suspension')->default(false);
            $table->boolean('requires_port_return')->default(false);
            $table->foreignId('given_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('sweeps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('control_slot_id')->constrained('control_slots');
            $table->foreignId('operator_user_id')->nullable()->constrained('users');
            $table->integer('vessels_count')->nullable();
            $table->integer('non_emitting_count')->nullable();
            $table->timestamps();
        });

        Schema::create('sweep_anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sweep_id')->constrained('sweeps');
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->string('type', 40);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE sweep_anomalies ADD CONSTRAINT sweep_anomalies_type_check CHECK (type IN ('non_emitting', 'licensed_absent_themis', 'other'))");

        Schema::create('ers_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->timestamp('declared_at');
            $table->string('source', 40);
            $table->timestamps();

            $table->index(['vessel_id', 'declared_at']);
        });

        DB::statement("ALTER TABLE ers_declarations ADD CONSTRAINT ers_declarations_source_check CHECK (source IN ('import', 'api', 'manual_entry'))");

        Schema::create('ers_catch_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ers_declaration_id')->constrained('ers_declarations');
            $table->foreignId('species_id')->constrained();
            $table->foreignId('presentation_id')->nullable()->constrained();
            $table->decimal('conversion_factor', 6, 3)->nullable();
            $table->decimal('live_weight_kg', 12, 2);
            $table->string('fao_zone', 10)->nullable();
            $table->foreignId('gear_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ers_catch_lines');
        Schema::dropIfExists('ers_declarations');
        Schema::dropIfExists('sweep_anomalies');
        Schema::dropIfExists('sweeps');
        Schema::dropIfExists('cessation_orders');
        Schema::dropIfExists('working_order_attestations');
        Schema::dropIfExists('manual_position_reports');
        Schema::dropIfExists('vms_cutoff_authorizations');
        Schema::dropIfExists('vms_failures');
        Schema::dropIfExists('alert_handlings');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('alert_rules');
    }
};
