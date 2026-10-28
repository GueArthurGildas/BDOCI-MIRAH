<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessel_current_states', function (Blueprint $table) {
            $table->foreignId('vessel_id')->primary()->constrained()->cascadeOnDelete();
            $table->string('license_status', 20)->default('not_provided');
            $table->date('license_expires_on')->nullable();
            $table->boolean('vms_failure_ongoing')->default(false);
            $table->timestamp('last_position_at')->nullable();
            $table->decimal('vms_silence_hours', 8, 2)->nullable();
            $table->boolean('is_enhanced_monitoring')->default(false);
            $table->unsignedInteger('open_infractions_count')->default(0);
            $table->unsignedInteger('checks_count')->default(0);
            $table->string('status_light', 20)->default('compliant');
            $table->timestamp('updated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_current_states');
    }
};
