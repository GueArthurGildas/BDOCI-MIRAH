<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('iso3_code', 3)->unique();
            $table->char('iso2_code', 2)->nullable()->unique();
            $table->string('label', 100);
            $table->timestamps();
        });

        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 40);
            $table->string('name', 160);
            $table->foreignId('country_id')->nullable()->constrained();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE institutions ADD CONSTRAINT institutions_type_check CHECK (type IN ('rfmo', 'rfb', 'fao', 'state', 'authority', 'other'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('countries');
    }
};
