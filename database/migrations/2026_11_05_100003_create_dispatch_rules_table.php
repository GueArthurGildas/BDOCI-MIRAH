<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatch_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_template_id')->constrained();
            $table->string('trigger_event', 60);
            $table->jsonb('conditions')->nullable();
            $table->integer('frequency_minutes')->nullable();
            $table->integer('daily_cap')->nullable();
            $table->string('stop_condition', 120)->nullable();
            $table->string('mode', 40)->default('with_validation');
            $table->boolean('is_active')->default(false);
            $table->foreignId('editable_by_permission_group_id')->nullable()->constrained('permission_groups');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE dispatch_rules ADD CONSTRAINT dispatch_rules_mode_check CHECK (mode IN ('automatic', 'with_validation'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_rules');
    }
};
