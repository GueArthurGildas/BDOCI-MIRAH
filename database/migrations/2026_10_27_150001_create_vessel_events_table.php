<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->timestamp('occurred_at');
            $table->string('type', 60);
            $table->foreignId('module_id')->constrained('module_registry');
            $table->string('object_type', 80);
            $table->unsignedBigInteger('object_id');
            $table->string('summary_key', 120);
            $table->jsonb('summary_params')->nullable();
            $table->foreignId('zone_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 30)->nullable();
            $table->timestamps();

            $table->index(['vessel_id', 'occurred_at']);
            $table->index(['module_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_events');
    }
};
