<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_agent_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_id')->constrained('action_catalog');
            $table->foreignId('requested_by')->constrained('users');
            $table->text('instruction')->nullable();
            $table->jsonb('parameters')->nullable();
            $table->string('status', 40)->default('requested');
            $table->timestamp('executed_at')->nullable();
            $table->string('result_object_type', 80)->nullable();
            $table->unsignedBigInteger('result_object_id')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE ai_agent_actions ADD CONSTRAINT ai_agent_actions_status_check CHECK (status IN ('requested', 'executed', 'refused', 'failed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agent_actions');
    }
};
