<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbook_days', function (Blueprint $table) {
            $table->id();
            $table->date('day')->unique();
            $table->timestamp('shift_start_at');
            $table->timestamp('shift_end_at');
            $table->string('status', 40)->default('open');
            $table->foreignId('pdf_document_id')->nullable()->constrained('documents');
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE logbook_days ADD CONSTRAINT logbook_days_status_check CHECK (status IN ('open', 'closed', 'locked'))");

        Schema::create('logbook_closings', function (Blueprint $table) {
            $table->foreignId('logbook_day_id')->primary()->constrained('logbook_days');
            $table->jsonb('counters');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamp('closed_at')->nullable();
        });

        Schema::create('logbook_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('logbook_day_id')->constrained('logbook_days');
            $table->timestamp('occurred_at');
            $table->foreignId('channel_id')->constrained('reference_list_values');
            $table->text('content');
            $table->string('direction', 40)->default('received');
            $table->boolean('is_order_or_decision')->default(false);
            $table->foreignId('operation_id')->nullable()->constrained('operations');
            $table->string('handling_status', 40)->default('to_handle');
            $table->foreignId('corrects_entry_id')->nullable()->constrained('logbook_entries');
            $table->foreignId('entered_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['logbook_day_id', 'occurred_at']);
        });

        DB::statement("ALTER TABLE logbook_entries ADD CONSTRAINT logbook_entries_direction_check CHECK (direction IN ('received', 'sent'))");
        DB::statement("ALTER TABLE logbook_entries ADD CONSTRAINT logbook_entries_handling_status_check CHECK (handling_status IN ('to_handle', 'in_progress', 'closed'))");

        Schema::create('logbook_entry_vessel', function (Blueprint $table) {
            $table->foreignId('logbook_entry_id')->constrained('logbook_entries');
            $table->foreignId('vessel_id')->constrained();

            $table->primary(['logbook_entry_id', 'vessel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook_entry_vessel');
        Schema::dropIfExists('logbook_entries');
        Schema::dropIfExists('logbook_closings');
        Schema::dropIfExists('logbook_days');
    }
};
