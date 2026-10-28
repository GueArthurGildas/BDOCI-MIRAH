<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('infraction_types', function (Blueprint $table) {
            $table->renameColumn('name', 'label');
        });

        Schema::table('infraction_types', function (Blueprint $table) {
            $table->string('code', 40)->change();
            $table->string('severity', 40)->nullable()->after('legal_basis');
            $table->string('potential_penalty')->nullable()->after('severity');
            $table->boolean('is_active')->default(true)->after('potential_penalty');
        });
    }

    public function down(): void
    {
        Schema::table('infraction_types', function (Blueprint $table) {
            $table->dropColumn(['severity', 'potential_penalty', 'is_active']);
        });

        Schema::table('infraction_types', function (Blueprint $table) {
            $table->renameColumn('label', 'name');
        });
    }
};
