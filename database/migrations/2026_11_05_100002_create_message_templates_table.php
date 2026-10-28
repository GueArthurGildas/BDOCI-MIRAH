<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('channel', 40);
            $table->string('language', 40)->default('fr');
            $table->string('subject')->nullable();
            $table->text('body');
            $table->string('form', 40)->default('none');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT message_templates_channel_check CHECK (channel IN ('email', 'whatsapp'))");
        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT message_templates_language_check CHECK (language IN ('fr', 'en'))");
        DB::statement("ALTER TABLE message_templates ADD CONSTRAINT message_templates_form_check CHECK (form IN ('form_03_csp_04', 'form_04_csp_04', 'none'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
