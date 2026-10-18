<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('port_id')->constrained();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction');
            $table->timestamp('occurred_at');
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('occurred_at');
        });

        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('observed_at');
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->text('description');
            $table->string('level');
            $table->string('status');
            $table->text('follow_up')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'observed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observations');
        Schema::dropIfExists('movements');
    }
};
