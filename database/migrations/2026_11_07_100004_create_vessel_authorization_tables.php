<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vms_compatibility_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->date('issued_on');
            $table->date('expires_on');
            $table->timestamps();

            $table->index('vessel_id');
        });

        Schema::create('external_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->foreignId('institution_id')->constrained();
            $table->string('number', 80)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to');
            $table->timestamps();

            $table->index('vessel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_authorizations');
        Schema::dropIfExists('vms_compatibility_certificates');
    }
};
