<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->foreignId('port_id')->nullable()->constrained();
            $table->timestamp('landed_at');
            $table->string('origin', 40);
            $table->jsonb('lines')->nullable();
            $table->timestamps();

            $table->index(['vessel_id', 'landed_at']);
        });

        DB::statement("ALTER TABLE landings ADD CONSTRAINT landings_origin_check CHECK (origin IN ('e_inspection', 'fallback_entry'))");

        Schema::create('transshipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->foreignId('partner_vessel_id')->nullable()->constrained('vessels');
            $table->timestamp('transshipped_at');
            $table->string('origin', 40);
            $table->jsonb('lines')->nullable();
            $table->timestamps();

            $table->index(['vessel_id', 'transshipped_at']);
        });

        DB::statement("ALTER TABLE transshipments ADD CONSTRAINT transshipments_origin_check CHECK (origin IN ('e_inspection', 'fallback_entry'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('transshipments');
        Schema::dropIfExists('landings');
    }
};
