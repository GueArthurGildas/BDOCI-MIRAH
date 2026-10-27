<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_source_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('received_count')->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['data_source_id', 'started_at']);
        });

        Schema::create('data_source_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('data_source_sync_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('occurred_at');
            $table->string('code');
            $table->text('message');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['data_source_id', 'resolved_at']);
        });

        Schema::create('data_source_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('data_source_sync_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_ref')->nullable();
            $table->string('reason');
            $table->text('details')->nullable();
            $table->jsonb('payload');
            $table->string('status')->default('pending');
            $table->timestamp('received_at');
            $table->timestamp('handled_at')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['data_source_id', 'status']);
        });

        Schema::create('data_source_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind');
            $table->text('body');
            $table->timestamps();

            $table->index(['data_source_id', 'created_at']);
        });

        Schema::table('data_sources', function (Blueprint $table) {
            $table->dropColumn(['error_count', 'rejected_count']);
        });
    }

    public function down(): void
    {
        Schema::table('data_sources', function (Blueprint $table) {
            $table->unsignedInteger('error_count')->default(0);
            $table->unsignedInteger('rejected_count')->default(0);
        });

        Schema::dropIfExists('data_source_comments');
        Schema::dropIfExists('data_source_rejections');
        Schema::dropIfExists('data_source_errors');
        Schema::dropIfExists('data_source_syncs');
    }
};
