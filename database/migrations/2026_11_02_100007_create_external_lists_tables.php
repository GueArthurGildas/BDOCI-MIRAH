<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained();
            $table->string('type', 40);
            $table->string('label', 160);
            $table->timestamps();
        });

        Schema::create('external_list_vessel', function (Blueprint $table) {
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('external_list_id')->constrained()->cascadeOnDelete();
            $table->date('listed_from')->nullable();
            $table->date('listed_until')->nullable();

            $table->primary(['vessel_id', 'external_list_id']);
        });

        DB::statement("ALTER TABLE external_lists ADD CONSTRAINT external_lists_type_check CHECK (type IN ('global_record', 'rfmo', 'inn'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('external_list_vessel');
        Schema::dropIfExists('external_lists');
    }
};
