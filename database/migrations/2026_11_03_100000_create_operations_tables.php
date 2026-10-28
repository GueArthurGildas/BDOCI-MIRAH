<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('label', 80);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE operation_categories ADD CONSTRAINT operation_categories_code_check CHECK (code IN ('vms', 'ers', 'mep', 'inn', 'div'))");

        Schema::create('operation_counters', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('operation_categories');
            $table->smallInteger('year');
            $table->integer('last_number')->default(0);

            $table->primary(['category_id', 'year']);
        });

        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 190)->unique();
            $table->foreignId('category_id')->constrained('operation_categories');
            $table->integer('number');
            $table->smallInteger('year');
            $table->string('name', 120);
            $table->string('status', 40)->default('open');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('origin', 60)->nullable();
            $table->text('summary')->nullable();
            $table->text('result')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->unique(['category_id', 'year', 'number']);
        });

        DB::statement("ALTER TABLE operations ADD CONSTRAINT operations_status_check CHECK (status IN ('open', 'in_progress', 'closed', 'archived'))");

        Schema::create('operation_contexts', function (Blueprint $table) {
            $table->foreignId('operation_id')->primary()->constrained();
            $table->text('first_event')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->decimal('pressure', 6, 1)->nullable();
            $table->string('visibility', 40)->nullable();
            $table->string('cloud_cover', 40)->nullable();
            $table->smallInteger('wind_beaufort')->nullable();
            $table->string('sea_state', 40)->nullable();
        });

        Schema::create('operation_vessel', function (Blueprint $table) {
            $table->foreignId('operation_id')->constrained();
            $table->foreignId('vessel_id')->constrained();

            $table->primary(['operation_id', 'vessel_id']);
        });

        Schema::create('operation_user', function (Blueprint $table) {
            $table->foreignId('operation_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('role', 60)->nullable();

            $table->primary(['operation_id', 'user_id']);
        });

        Schema::create('operation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained();
            $table->timestamp('occurred_at');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('operation_instructions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained();
            $table->text('body');
            $table->foreignId('given_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operation_instructions');
        Schema::dropIfExists('operation_events');
        Schema::dropIfExists('operation_user');
        Schema::dropIfExists('operation_vessel');
        Schema::dropIfExists('operation_contexts');
        Schema::dropIfExists('operations');
        Schema::dropIfExists('operation_counters');
        Schema::dropIfExists('operation_categories');
    }
};
