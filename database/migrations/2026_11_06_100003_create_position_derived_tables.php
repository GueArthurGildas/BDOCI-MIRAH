<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('position_daily_summaries', function (Blueprint $table) {
            $table->foreignId('vessel_id')->constrained();
            $table->date('day');
            $table->integer('positions_count');
            $table->decimal('emission_hours', 5, 2)->nullable();
            $table->decimal('distance_nautical_miles', 8, 2)->nullable();
            $table->decimal('average_speed', 5, 2)->nullable();
            $table->jsonb('zones')->nullable();

            $table->primary(['vessel_id', 'day']);
        });

        Schema::create('control_slots', function (Blueprint $table) {
            $table->id();
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->foreignId('operator_user_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('started_at');
        });

        Schema::create('slot_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('control_slot_id')->constrained('control_slots');
            $table->foreignId('vessel_id')->constrained();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->decimal('speed', 5, 2)->nullable();
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->foreignId('locality_id')->nullable()->constrained('reference_list_values');
            $table->timestamp('last_emission_at')->nullable();
            $table->string('emission_test', 40)->default('to_confirm');
            $table->string('speed_activity_test', 40)->default('to_confirm');
            $table->string('trajectory_test', 40)->default('to_confirm');
            $table->string('result', 40)->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->string('loading_source', 40)->nullable();
            $table->timestamps();

            $table->unique(['control_slot_id', 'vessel_id']);
        });

        foreach (['emission_test', 'speed_activity_test', 'trajectory_test'] as $column) {
            DB::statement("ALTER TABLE slot_positions ADD CONSTRAINT slot_positions_{$column}_check CHECK ({$column} IN ('ok', 'ko', 'to_confirm'))");
        }

        DB::statement("ALTER TABLE slot_positions ADD CONSTRAINT slot_positions_result_check CHECK (result IN ('normal', 'suspect'))");
        DB::statement("ALTER TABLE slot_positions ADD CONSTRAINT slot_positions_loading_source_check CHECK (loading_source IN ('api', 'import', 'scraping'))");

        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->timestamps();

            $table->index(['vessel_id', 'started_at']);
        });

        DB::statement('ALTER TABLE tracks ADD COLUMN geometry geometry(LineString, 4326)');
        DB::statement('CREATE INDEX tracks_geometry_gist ON tracks USING gist (geometry)');

        Schema::create('screen_captures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->foreignId('document_id')->constrained('documents');
            $table->timestamp('taken_at');
            $table->timestamps();
        });

        Schema::create('identity_anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->string('detail');
            $table->timestamp('detected_at');
            $table->timestamps();
        });

        Schema::create('emission_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->boolean('is_technical_stop')->default(false);
            $table->timestamps();

            $table->index(['vessel_id', 'started_at']);
        });

        Schema::create('monthly_aggregates', function (Blueprint $table) {
            $table->char('year_month', 7);
            $table->foreignId('vessel_id')->constrained();
            $table->decimal('emission_rate', 5, 2)->nullable();
            $table->integer('silences_count')->nullable();

            $table->primary(['year_month', 'vessel_id']);
        });

        Schema::table('surveillance_observations', function (Blueprint $table) {
            $table->foreign('slot_position_id')->references('id')->on('slot_positions');
        });

        Schema::table('zone_counts', function (Blueprint $table) {
            $table->foreign('control_slot_id')->references('id')->on('control_slots');
        });
    }

    public function down(): void
    {
        Schema::table('zone_counts', function (Blueprint $table) {
            $table->dropForeign(['control_slot_id']);
        });

        Schema::table('surveillance_observations', function (Blueprint $table) {
            $table->dropForeign(['slot_position_id']);
        });

        Schema::dropIfExists('monthly_aggregates');
        Schema::dropIfExists('emission_cutoffs');
        Schema::dropIfExists('identity_anomalies');
        Schema::dropIfExists('screen_captures');
        Schema::dropIfExists('tracks');
        Schema::dropIfExists('slot_positions');
        Schema::dropIfExists('control_slots');
        Schema::dropIfExists('position_daily_summaries');
    }
};
