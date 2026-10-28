<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('action_catalog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('module_registry');
            $table->string('code', 80)->unique();
            $table->string('verb', 30);
            $table->boolean('ai_agent_allowed')->default(false);
            $table->boolean('ai_agent_enabled')->default(false);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE action_catalog ADD CONSTRAINT action_catalog_verb_check CHECK (verb IN ('view', 'create', 'update', 'validate', 'close', 'send', 'export', 'trigger_ai_agent', 'other'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('action_catalog');
    }
};
