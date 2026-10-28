<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('license_suspensions', fn (Blueprint $table) => $table->foreign('vms_failure_id')->references('id')->on('vms_failures')->nullOnDelete());
    }

    public function down(): void
    {
        Schema::table('license_suspensions', fn (Blueprint $table) => $table->dropForeign(['vms_failure_id']));
    }
};
