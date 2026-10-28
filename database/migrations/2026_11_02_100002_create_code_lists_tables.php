<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vessel_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('label', 160);
            $table->timestamps();
        });

        Schema::create('gears', function (Blueprint $table) {
            $table->id();
            $table->string('code_fao', 10)->unique();
            $table->string('label', 160);
            $table->timestamps();
        });

        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('label', 160);
            $table->decimal('conversion_factor', 6, 3)->nullable();
            $table->timestamps();
        });

        Schema::create('zone_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained();
            $table->string('segment', 40);
            $table->smallInteger('nautical_miles');
            $table->timestamps();

        });

        DB::statement("ALTER TABLE zone_rules ADD CONSTRAINT zone_rules_segment_check CHECK (segment IN ('artisanal', 'small_pelagics', 'trawl', 'tuna_vessel'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('zone_rules');
        Schema::dropIfExists('presentations');
        Schema::dropIfExists('gears');
        Schema::dropIfExists('vessel_types');
    }
};
