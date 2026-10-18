<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('registration_number')->unique();
            $table->string('imo_number')->nullable()->unique();
            $table->string('call_sign')->nullable();
            $table->char('flag', 2);
            $table->string('type');
            $table->string('category');
            $table->string('owner')->nullable();
            $table->decimal('length_meters', 6, 2)->nullable();
            $table->decimal('gross_tonnage', 9, 2)->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'status']);
        });

        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->string('type');
            $table->date('valid_from');
            $table->date('valid_until');
            $table->timestamp('suspended_at')->nullable();
            $table->timestamps();

            $table->index('valid_until');
        });

        Schema::create('tracking_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('system');
            $table->string('identifier');
            $table->date('installed_at');
            $table->date('removed_at')->nullable();
            $table->timestamps();

            $table->unique(['system', 'identifier']);
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tracking_device_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->string('system');
            $table->timestamp('recorded_at');
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->decimal('speed_knots', 5, 2)->nullable();
            $table->unsignedSmallInteger('course')->nullable();

            $table->index(['vessel_id', 'recorded_at']);
            $table->index('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
        Schema::dropIfExists('tracking_devices');
        Schema::dropIfExists('licenses');
        Schema::dropIfExists('vessels');
    }
};
