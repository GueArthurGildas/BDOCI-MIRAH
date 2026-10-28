<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sent_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_template_id')->nullable()->constrained();
            $table->foreignId('dispatch_rule_id')->nullable()->constrained();
            $table->foreignId('communication_channel_id')->constrained();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->unsignedBigInteger('logbook_entry_id')->nullable();
            $table->string('recipient', 190);
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('status', 40);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->string('triggered_by', 40);
            $table->foreignId('user_id')->nullable()->constrained();
            $table->boolean('reply_received')->default(false);
            $table->timestamps();

            $table->index(['vessel_id', 'sent_at']);
            $table->index(['dispatch_rule_id', 'vessel_id']);
        });

        DB::statement("ALTER TABLE sent_messages ADD CONSTRAINT sent_messages_status_check CHECK (status IN ('prepared', 'sent', 'acknowledged', 'bounced', 'failed'))");
        DB::statement("ALTER TABLE sent_messages ADD CONSTRAINT sent_messages_triggered_by_check CHECK (triggered_by IN ('user', 'rule', 'ai_agent'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('sent_messages');
    }
};
