<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60);
            $table->string('frequency', 40);
            $table->string('format', 40);
            $table->foreignId('recipient_id')->constrained('users');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE scheduled_reports ADD CONSTRAINT scheduled_reports_frequency_check CHECK (frequency IN ('weekly', 'monthly', 'yearly'))");
        DB::statement("ALTER TABLE scheduled_reports ADD CONSTRAINT scheduled_reports_format_check CHECK (format IN ('pdf', 'excel'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
