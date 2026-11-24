<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('witness_reports', function (Blueprint $table) {
            $table->foreignId('logbook_entry_id')->nullable()->constrained('logbook_entries');
        });

        Schema::table('observations', function (Blueprint $table) {
            $table->foreignId('logbook_entry_id')->nullable()->constrained('logbook_entries');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('logbook_entry_id');
        });

        Schema::table('witness_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('logbook_entry_id');
        });
    }
};
