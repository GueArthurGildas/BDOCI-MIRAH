<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('object_type', 80);
            $table->string('code', 60);
            $table->string('label', 120);
            $table->string('type', 40);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['object_type', 'code']);
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_definition_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('object_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['custom_field_definition_id', 'object_id']);
        });

        DB::statement("ALTER TABLE custom_field_definitions ADD CONSTRAINT custom_field_definitions_type_check CHECK (type IN ('text', 'number', 'date', 'boolean', 'list'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
    }
};
