<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('kind')->default('human')->after('email');
            $table->string('phone')->nullable()->after('kind');
            $table->string('whatsapp_number')->nullable()->after('phone');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('lead_id')->nullable()->after('name')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['kind', 'phone', 'whatsapp_number']);
        });
    }
};
