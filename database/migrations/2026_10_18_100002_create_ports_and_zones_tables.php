<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->json('geometry');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('locality');
            $table->decimal('latitude', 9, 6);
            $table->decimal('longitude', 9, 6);
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ports');
        Schema::dropIfExists('zones');
    }
};
