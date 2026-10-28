<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('external_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 80)->unique();
            $table->string('reference', 80)->nullable();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->string('location_type', 40)->nullable();
            $table->foreignId('port_id')->nullable()->constrained();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('control_type', 60)->nullable();
            $table->string('result', 60)->nullable();
            $table->string('captain_signature_status', 40)->nullable();
            $table->boolean('is_copy_handed_over')->nullable();
            $table->jsonb('raw')->nullable();
            $table->timestamp('read_at');
            $table->timestamps();

            $table->index(['vessel_id', 'started_at']);
        });

        DB::statement("ALTER TABLE external_inspections ADD CONSTRAINT external_inspections_location_type_check CHECK (location_type IN ('port', 'sea'))");
        DB::statement("ALTER TABLE external_inspections ADD CONSTRAINT external_inspections_captain_signature_status_check CHECK (captain_signature_status IN ('signed', 'refused', 'absent'))");

        Schema::table('mrep_port_calls', function (Blueprint $table) {
            $table->foreign('inspection_id')->references('id')->on('external_inspections');
        });

        Schema::create('inspection_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('external_inspections');
            $table->string('type', 40);
            $table->boolean('is_compliant')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE inspection_documents ADD CONSTRAINT inspection_documents_type_check CHECK (type IN ('license', 'certificate', 'fishing_log', 'hold_plan', 'other'))");

        Schema::create('inspection_gears', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('external_inspections');
            $table->foreignId('gear_id')->nullable()->constrained();
            $table->decimal('mesh_mm', 6, 1)->nullable();
            $table->boolean('is_marking_compliant')->nullable();
            $table->boolean('is_compliant')->nullable();
            $table->timestamps();
        });

        Schema::create('declared_observed_catches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('external_inspections');
            $table->foreignId('species_id')->constrained();
            $table->foreignId('presentation_id')->nullable()->constrained();
            $table->decimal('declared_weight_kg', 12, 2)->nullable();
            $table->decimal('observed_weight_kg', 12, 2)->nullable();
            $table->decimal('net_weight_kg', 12, 2)->nullable();
            $table->decimal('conversion_factor', 6, 3)->nullable();
            $table->decimal('variance_pct', 7, 2)->nullable();
            $table->boolean('exceeds_csp_threshold')->default(false);
            $table->timestamps();
        });

        Schema::create('inspection_infractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('external_inspections');
            $table->string('provision', 255)->nullable();
            $table->string('stamp', 80)->nullable();
            $table->text('measures')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_infractions');
        Schema::dropIfExists('declared_observed_catches');
        Schema::dropIfExists('inspection_gears');
        Schema::dropIfExists('inspection_documents');

        Schema::table('mrep_port_calls', function (Blueprint $table) {
            $table->dropForeign(['inspection_id']);
        });

        Schema::dropIfExists('external_inspections');
    }
};
