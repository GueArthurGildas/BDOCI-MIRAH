<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shift_handovers', function (Blueprint $table) {
            $table->foreignId('rotation_id')->nullable()->change();
            $table->foreignId('logbook_day_id')->nullable()->after('rotation_id')->constrained('logbook_days');
            $table->timestamp('handed_over_at')->nullable()->after('started_at');
            $table->foreignId('handed_over_by')->nullable()->after('handed_over_at')->constrained('users');
            $table->foreignId('taken_over_by')->nullable()->after('handed_over_by')->constrained('users');
            $table->foreignId('supervised_by')->nullable()->after('taken_over_by')->constrained('users');
            $table->jsonb('operation_counters')->nullable()->after('supervised_by');
            $table->jsonb('open_operations')->nullable()->after('operation_counters');
            $table->text('instructions')->nullable()->after('open_operations');

            $table->index(['logbook_day_id', 'handed_over_at']);
        });

        Schema::table('logbook_closings', function (Blueprint $table) {
            $table->text('instructions')->nullable()->after('counters');
        });
    }

    public function down(): void
    {
        Schema::table('logbook_closings', function (Blueprint $table) {
            $table->dropColumn('instructions');
        });

        Schema::table('shift_handovers', function (Blueprint $table) {
            $table->dropIndex(['logbook_day_id', 'handed_over_at']);
            $table->dropConstrainedForeignId('supervised_by');
            $table->dropConstrainedForeignId('taken_over_by');
            $table->dropConstrainedForeignId('handed_over_by');
            $table->dropConstrainedForeignId('logbook_day_id');
            $table->dropColumn(['handed_over_at', 'operation_counters', 'open_operations', 'instructions']);
        });
    }
};
