<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internal_notes', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->string('urgency', 40)->default('normal');
            $table->foreignId('recipient_user_id')->nullable()->constrained('users');
            $table->foreignId('recipient_team_id')->nullable()->constrained('teams');
            $table->timestamp('due_date')->nullable();
            $table->text('free_text')->nullable();
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('ai_agent_action_id')->nullable()->constrained('ai_agent_actions');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('relayed_by', 40)->nullable();
            $table->unsignedBigInteger('logbook_entry_id')->nullable();
            $table->timestamp('created_at');

            $table->index(['recipient_user_id', 'is_read']);
        });

        DB::statement("ALTER TABLE internal_notes ADD CONSTRAINT internal_notes_type_check CHECK (type IN ('to_check', 'suspect', 'reminder', 'to_forward'))");
        DB::statement("ALTER TABLE internal_notes ADD CONSTRAINT internal_notes_urgency_check CHECK (urgency IN ('low', 'normal', 'high'))");
        DB::statement("ALTER TABLE internal_notes ADD CONSTRAINT internal_notes_relayed_by_check CHECK (relayed_by IN ('email', 'whatsapp'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('internal_notes');
    }
};
