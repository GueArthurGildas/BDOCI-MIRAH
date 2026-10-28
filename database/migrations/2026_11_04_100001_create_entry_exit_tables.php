<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_exit_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_declaration_id')->constrained('eez_declarations');
            $table->foreignId('exit_declaration_id')->constrained('eez_declarations');
            $table->decimal('variance_kg', 12, 2)->nullable();
            $table->string('explanation', 40)->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE entry_exit_links ADD CONSTRAINT entry_exit_links_explanation_check CHECK (explanation IN ('catch_document', 'transshipment', 'landing', 'unexplained'))");

        Schema::create('missed_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->timestamp('detected_at');
            $table->integer('reminders')->default(0);
            $table->string('qualification', 120)->nullable();
            $table->timestamps();
        });

        Schema::create('entry_exit_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->foreignId('eez_declaration_id')->nullable()->constrained();
            $table->string('direction', 40);
            $table->timestamp('alerted_at');
            $table->string('status', 40)->default('open');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE entry_exit_alerts ADD CONSTRAINT entry_exit_alerts_direction_check CHECK (direction IN ('entry', 'exit'))");
        DB::statement("ALTER TABLE entry_exit_alerts ADD CONSTRAINT entry_exit_alerts_status_check CHECK (status IN ('open', 'closed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_exit_alerts');
        Schema::dropIfExists('missed_declarations');
        Schema::dropIfExists('entry_exit_links');
    }
};
