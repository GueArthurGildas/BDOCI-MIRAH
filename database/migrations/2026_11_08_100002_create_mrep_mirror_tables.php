<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mrep_dpeps', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 80)->unique();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->string('number', 80)->nullable();
            $table->foreignId('port_id')->nullable()->constrained();
            $table->timestamp('planned_arrival_at')->nullable();
            $table->string('purpose', 40)->nullable();
            $table->string('last_port_call', 160)->nullable();
            $table->jsonb('catches_on_board')->nullable();
            $table->jsonb('catches_to_land')->nullable();
            $table->boolean('is_complete')->nullable();
            $table->boolean('is_72h_deadline_met')->nullable();
            $table->jsonb('raw')->nullable();
            $table->timestamp('read_at');
            $table->timestamps();

            $table->index('vessel_id');
        });

        DB::statement("ALTER TABLE mrep_dpeps ADD CONSTRAINT mrep_dpeps_purpose_check CHECK (purpose IN ('lan', 'trx', 'pro', 'other'))");

        Schema::create('mrep_entry_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpep_id')->constrained('mrep_dpeps');
            $table->string('decision', 40);
            $table->timestamp('decided_at');
            $table->string('decision_maker', 160)->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE mrep_entry_decisions ADD CONSTRAINT mrep_entry_decisions_decision_check CHECK (decision IN ('authorization', 'refusal', 'inspection_only'))");

        Schema::create('mrep_port_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpep_id')->constrained('mrep_dpeps');
            $table->timestamp('arrival')->nullable();
            $table->timestamp('departure')->nullable();
            $table->boolean('is_inspected')->nullable();
            $table->boolean('is_infraction_recorded')->nullable();
            $table->unsignedBigInteger('inspection_id')->nullable();
            $table->timestamps();
        });

        Schema::create('mrep_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dpep_id')->constrained('mrep_dpeps');
            $table->string('recipient', 190)->nullable();
            $table->string('channel', 40)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->string('status', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('mrep_gies_refusals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->string('reference', 80)->nullable();
            $table->date('refused_on')->nullable();
            $table->timestamps();
        });

        Schema::create('mrep_targetings', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year');
            $table->decimal('objective_pct', 5, 2);
            $table->decimal('achieved_pct', 5, 2)->nullable();
            $table->string('target', 40);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mrep_targetings');
        Schema::dropIfExists('mrep_gies_refusals');
        Schema::dropIfExists('mrep_notifications');
        Schema::dropIfExists('mrep_port_calls');
        Schema::dropIfExists('mrep_entry_decisions');
        Schema::dropIfExists('mrep_dpeps');
    }
};
