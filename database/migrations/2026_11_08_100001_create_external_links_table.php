<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_links', function (Blueprint $table) {
            $table->id();
            $table->string('source', 40)->unique();
            $table->string('model_url', 500)->nullable();
            $table->boolean('is_copy_fallback')->default(true);
            $table->timestamps();
        });

        DB::statement("ALTER TABLE external_links ADD CONSTRAINT external_links_source_check CHECK (source IN ('themis', 'neno', 'e_inspection'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('external_links');
    }
};
