<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('communication_channels', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->boolean('is_auto_dispatch_enabled')->default(false);
            $table->boolean('is_consent_required')->default(false);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE communication_channels ADD CONSTRAINT communication_channels_code_check CHECK (code IN ('in_app', 'email', 'whatsapp'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('communication_channels');
    }
};
