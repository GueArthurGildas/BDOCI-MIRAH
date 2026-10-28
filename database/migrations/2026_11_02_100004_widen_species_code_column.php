<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->string('fao_code', 10)->change();
            $table->string('scientific_name')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('species', function (Blueprint $table) {
            $table->string('fao_code', 3)->change();
            $table->string('scientific_name')->nullable(false)->change();
        });
    }
};
