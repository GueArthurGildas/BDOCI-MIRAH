<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('operation_events', function (Blueprint $table) {
            $table->foreignId('channel_id')->nullable()->after('occurred_at')->constrained('reference_list_values');
            $table->string('direction', 40)->default('received')->after('channel_id');
            $table->boolean('is_order_or_decision')->default(false)->after('body');
            $table->foreignId('entered_by')->nullable()->after('is_order_or_decision')->constrained('users');
        });

        DB::statement('ALTER TABLE operation_events DROP CONSTRAINT IF EXISTS operation_events_direction_check');
        DB::statement("ALTER TABLE operation_events ADD CONSTRAINT operation_events_direction_check CHECK (direction IN ('received', 'sent'))");

        Schema::table('operation_contexts', function (Blueprint $table) {
            $table->text('weather_notes')->nullable()->after('sea_state');
            $table->text('vessel_position')->nullable()->after('weather_notes');
            $table->text('vessel_activity')->nullable()->after('vessel_position');
            $table->text('comments')->nullable()->after('vessel_activity');
        });

        Schema::table('operations', function (Blueprint $table) {
            $table->text('infractions_noted')->nullable()->after('result');
        });

        Schema::table('logbook_entries', function (Blueprint $table) {
            $table->string('operation_milestone', 40)->nullable()->after('operation_id');
        });

        DB::statement('ALTER TABLE logbook_entries DROP CONSTRAINT IF EXISTS logbook_entries_operation_milestone_check');
        DB::statement("ALTER TABLE logbook_entries ADD CONSTRAINT logbook_entries_operation_milestone_check CHECK (operation_milestone IN ('opened', 'closed'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE logbook_entries DROP CONSTRAINT IF EXISTS logbook_entries_operation_milestone_check');

        Schema::table('logbook_entries', function (Blueprint $table) {
            $table->dropColumn('operation_milestone');
        });

        Schema::table('operations', function (Blueprint $table) {
            $table->dropColumn('infractions_noted');
        });

        Schema::table('operation_contexts', function (Blueprint $table) {
            $table->dropColumn(['weather_notes', 'vessel_position', 'vessel_activity', 'comments']);
        });

        DB::statement('ALTER TABLE operation_events DROP CONSTRAINT IF EXISTS operation_events_direction_check');

        Schema::table('operation_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entered_by');
            $table->dropConstrainedForeignId('channel_id');
            $table->dropColumn(['direction', 'is_order_or_decision']);
        });
    }
};
