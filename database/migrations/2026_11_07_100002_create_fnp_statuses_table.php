<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fnp_statuses', function (Blueprint $table) {
            $table->foreignId('vessel_id')->primary()->constrained();
            $table->string('status', 40);
            $table->string('deregistration_reason', 40)->nullable();
            $table->date('status_on')->nullable();
        });

        DB::statement("ALTER TABLE fnp_statuses ADD CONSTRAINT fnp_statuses_status_check CHECK (status IN ('registered', 'deregistered'))");
        DB::statement("ALTER TABLE fnp_statuses ADD CONSTRAINT fnp_statuses_deregistration_reason_check CHECK (deregistration_reason IN ('destruction', 'unauthorized_modification', 'flag_change', 'inn_list', 'five_years_without_license'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('fnp_statuses');
    }
};
