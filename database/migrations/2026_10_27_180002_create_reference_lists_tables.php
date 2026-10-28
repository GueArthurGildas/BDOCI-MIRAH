<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_lists', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('label', 120);
            $table->timestamps();
        });

        Schema::create('reference_list_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reference_list_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60);
            $table->string('label', 160);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['reference_list_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_list_values');
        Schema::dropIfExists('reference_lists');
    }
};
