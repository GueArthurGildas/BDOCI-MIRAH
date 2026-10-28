<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_registry', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('label', 120);
            $table->string('version', 20)->default('1.0.0');
            $table->boolean('is_active')->default(true);
            $table->jsonb('dependencies')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_registry');
    }
};
