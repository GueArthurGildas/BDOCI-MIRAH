<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('port_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_reference')->nullable()->unique();
            $table->string('type');
            $table->timestamp('inspected_at');
            $table->string('inspectors');
            $table->string('result');
            $table->text('findings')->nullable();
            $table->timestamps();

            $table->index('inspected_at');
        });

        Schema::create('infraction_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('legal_basis')->nullable();
            $table->timestamps();
        });

        Schema::create('infractions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('infraction_type_id')->constrained();
            $table->timestamp('detected_at');
            $table->text('facts');
            $table->text('evidence')->nullable();
            $table->string('status');
            $table->text('decision')->nullable();
            $table->string('sanction')->nullable();
            $table->timestamps();

            $table->index(['status', 'detected_at']);
        });

        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('fao_code', 3)->unique();
            $table->string('name');
            $table->string('scientific_name');
            $table->timestamps();
        });

        Schema::create('catch_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('species_id')->constrained();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('quantity_kg', 12, 2);
            $table->string('source');
            $table->timestamps();

            $table->index('period_start');
        });

        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->text('objective');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->string('status');
            $table->text('results')->nullable();
            $table->timestamps();
        });

        Schema::create('mission_vessel', function (Blueprint $table) {
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->primary(['mission_id', 'vessel_id']);
        });

        Schema::create('mission_user', function (Blueprint $table) {
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['mission_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_user');
        Schema::dropIfExists('mission_vessel');
        Schema::dropIfExists('missions');
        Schema::dropIfExists('catch_declarations');
        Schema::dropIfExists('species');
        Schema::dropIfExists('infractions');
        Schema::dropIfExists('infraction_types');
        Schema::dropIfExists('inspections');
    }
};
