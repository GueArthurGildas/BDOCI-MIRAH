<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('type', 20)->default('human')->after('id');
            $table->string('job_title', 120)->nullable()->after('email');
            $table->string('phone', 40)->nullable()->after('job_title');
            $table->string('whatsapp_number', 40)->nullable()->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
            $table->string('password')->nullable()->change();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('lead_user_id')->nullable()->constrained('users')->nullOnDelete();
        });

        DB::statement("ALTER TABLE users ADD CONSTRAINT users_type_check CHECK (type IN ('human', 'ai_agent', 'system'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_type_check');

        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['type', 'job_title', 'phone', 'whatsapp_number', 'last_login_at']);
        });
    }
};
