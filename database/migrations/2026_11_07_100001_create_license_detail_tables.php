<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_zone', function (Blueprint $table) {
            $table->foreignId('license_id')->constrained();
            $table->foreignId('zone_id')->constrained();
            $table->primary(['license_id', 'zone_id']);
        });

        Schema::create('gear_license', function (Blueprint $table) {
            $table->foreignId('license_id')->constrained();
            $table->foreignId('gear_id')->constrained();
            $table->primary(['license_id', 'gear_id']);
        });

        Schema::create('license_species', function (Blueprint $table) {
            $table->foreignId('license_id')->constrained();
            $table->foreignId('species_id')->constrained();
            $table->primary(['license_id', 'species_id']);
        });

        Schema::create('license_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained();
            $table->string('body', 500);
            $table->timestamps();
        });

        Schema::create('license_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained();
            $table->foreignId('license_id')->nullable()->constrained();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_documents');
        Schema::dropIfExists('license_conditions');
        Schema::dropIfExists('license_species');
        Schema::dropIfExists('gear_license');
        Schema::dropIfExists('license_zone');
    }
};
