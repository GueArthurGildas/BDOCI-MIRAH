<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 190);
            $table->string('address')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 190)->nullable();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name', 190);
            $table->string('first_name', 120)->nullable();
            $table->foreignId('nationality_country_id')->nullable()->constrained('countries');
            $table->string('address')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 190)->nullable();
            $table->foreignId('legal_entity_id')->nullable()->constrained();
            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
        Schema::dropIfExists('legal_entities');
    }
};
