<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('category', 80)->nullable();
            $table->foreignId('country_id')->nullable()->constrained();
            $table->foreignId('institution_id')->nullable()->constrained();
            $table->string('organization', 160)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('phone', 40)->nullable();
            $table->timestamps();
        });

        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('name', 190);
            $table->string('grade', 80)->nullable();
            $table->string('agency', 120)->nullable();
            $table->boolean('is_inspector')->default(false);
            $table->timestamps();
        });

        Schema::create('accreditations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained();
            $table->string('card_number', 60)->nullable();
            $table->string('decision', 120)->nullable();
            $table->date('returned_on')->nullable();
            $table->text('trainings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accreditations');
        Schema::dropIfExists('agents');
        Schema::dropIfExists('contacts');
    }
};
