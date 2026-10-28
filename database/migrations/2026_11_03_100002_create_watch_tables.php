<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained();
            $table->date('day');
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('18:00:00');
            $table->timestamps();
        });

        Schema::create('shift_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rotation_id')->constrained();
            $table->timestamp('started_at')->nullable();
            $table->timestamps();
        });

        Schema::create('surveillance_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->unsignedBigInteger('slot_position_id')->nullable();
            $table->foreignId('logbook_entry_id')->nullable()->constrained('logbook_entries');
            $table->text('body')->nullable();
            $table->string('status', 40)->default('monitoring');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamp('created_at');
        });

        DB::statement("ALTER TABLE surveillance_observations ADD CONSTRAINT surveillance_observations_status_check CHECK (status IN ('monitoring', 'closed', 'operation'))");

        Schema::create('relays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_handover_id')->constrained();
            $table->foreignId('surveillance_observation_id')->nullable()->constrained();
            $table->foreignId('next_team_id')->nullable()->constrained('teams');
            $table->foreignId('read_acknowledged_by')->nullable()->constrained('users');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });

        Schema::create('standing_instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->text('body');
            $table->date('started_on');
            $table->date('ended_on')->nullable();
            $table->timestamps();
        });

        Schema::create('zone_counts', function (Blueprint $table) {
            $table->unsignedBigInteger('control_slot_id');
            $table->foreignId('zone_id')->constrained();
            $table->integer('vessels_count');

            $table->primary(['control_slot_id', 'zone_id']);
        });

        Schema::create('consistency_rules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('description');
            $table->jsonb('parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('check_counters', function (Blueprint $table) {
            $table->foreignId('vessel_id')->primary()->constrained();
            $table->integer('checks_count')->default(0);
            $table->timestamp('last_checked_at')->nullable();
            $table->boolean('is_alert_raised')->default(false);
        });

        Schema::create('watch_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->string('origin', 40);
            $table->timestamp('started_at');
            $table->string('status', 40)->default('open');
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('logbook_entry_id')->nullable()->constrained('logbook_entries');
            $table->foreignId('operation_id')->nullable()->constrained();
            $table->unsignedBigInteger('entry_exit_alert_id')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['status', 'origin']);
        });

        DB::statement("ALTER TABLE watch_entries ADD CONSTRAINT watch_entries_origin_check CHECK (origin IN ('director_list_themis', 'director_list_manual', 'logbook', 'eez_entry', 'eez_exit', 'enhanced_monitoring', 'more_than_3_checks'))");
        DB::statement("ALTER TABLE watch_entries ADD CONSTRAINT watch_entries_status_check CHECK (status IN ('open', 'closed'))");

        Schema::table('surveillance_observations', function (Blueprint $table) {
            $table->foreignId('legacy_observation_id')->nullable()->unique()->constrained('observations');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watch_entries');
        Schema::dropIfExists('check_counters');
        Schema::dropIfExists('consistency_rules');
        Schema::dropIfExists('zone_counts');
        Schema::dropIfExists('standing_instructions');
        Schema::dropIfExists('relays');
        Schema::dropIfExists('surveillance_observations');
        Schema::dropIfExists('shift_handovers');
        Schema::dropIfExists('rotations');
    }
};
