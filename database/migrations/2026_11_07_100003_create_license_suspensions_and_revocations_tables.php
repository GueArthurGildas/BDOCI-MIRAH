<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_suspensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained();
            $table->string('reason', 40);
            $table->date('started_on');
            $table->date('planned_end_on')->nullable();
            $table->date('lifted_on')->nullable();
            $table->unsignedBigInteger('infraction_case_id')->nullable();
            $table->unsignedBigInteger('vms_failure_id')->nullable();
            $table->foreignId('operation_id')->nullable()->constrained();
            $table->foreignId('proposed_by')->nullable()->constrained('users');
            $table->foreignId('decided_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('license_id');
        });

        DB::statement("ALTER TABLE license_suspensions ADD CONSTRAINT license_suspensions_reason_check CHECK (reason IN ('force_majeure', 'stocks', 'art_105', 'art_106', 'vms_overdue', 'other'))");

        Schema::create('license_revocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->unique()->constrained();
            $table->string('reason');
            $table->date('revoked_on');
            $table->foreignId('decided_by')->nullable()->constrained('users');
            $table->unsignedBigInteger('infraction_case_id')->nullable();
            $table->boolean('is_repeat_offense')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_revocations');
        Schema::dropIfExists('license_suspensions');
    }
};
