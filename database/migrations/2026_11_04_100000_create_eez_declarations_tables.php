<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eez_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->string('type', 40);
            $table->timestamp('received_at');
            $table->foreignId('reception_channel_id')->nullable()->constrained('reference_list_values');
            $table->string('entry_origin', 40);
            $table->foreignId('entered_by')->nullable()->constrained('users');
            $table->timestamp('crossed_at')->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->string('route', 120)->nullable();
            $table->decimal('speed', 5, 2)->nullable();
            $table->string('destination', 160)->nullable();
            $table->string('reason', 40)->nullable();
            $table->string('declared_name', 190)->nullable();
            $table->string('declared_imo', 20)->nullable();
            $table->string('declared_mmsi', 20)->nullable();
            $table->string('declared_ircs', 20)->nullable();
            $table->string('declared_flag', 80)->nullable();
            $table->string('declared_owner', 190)->nullable();
            $table->string('status', 40)->default('received');
            $table->timestamps();

            $table->index(['vessel_id', 'type', 'received_at']);
        });

        DB::statement("ALTER TABLE eez_declarations ADD CONSTRAINT eez_declarations_type_check CHECK (type IN ('entry', 'exit'))");
        DB::statement("ALTER TABLE eez_declarations ADD CONSTRAINT eez_declarations_entry_origin_check CHECK (entry_origin IN ('manual', 'scraping'))");
        DB::statement("ALTER TABLE eez_declarations ADD CONSTRAINT eez_declarations_reason_check CHECK (reason IN ('eez_transit', 'eez_fishing', 'ivorian_port_transit', 'high_seas_fishing', 'foreign_eez_fishing'))");
        DB::statement("ALTER TABLE eez_declarations ADD CONSTRAINT eez_declarations_status_check CHECK (status IN ('received', 'verified', 'acknowledged', 'variance', 'closed'))");

        Schema::create('eez_declaration_catches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eez_declaration_id')->constrained();
            $table->foreignId('species_id')->constrained();
            $table->decimal('weight_kg', 12, 2);
            $table->timestamps();
        });

        Schema::create('eez_declaration_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eez_declaration_id')->constrained();
            $table->string('check_type', 40);
            $table->string('result', 40);
            $table->string('comment', 255)->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE eez_declaration_checks ADD CONSTRAINT eez_declaration_checks_check_type_check CHECK (check_type IN ('vms', 'ais', 'license', 'designated_port', 'dpep_72h', 'access', 'inn_lists', 'consistency'))");
        DB::statement("ALTER TABLE eez_declaration_checks ADD CONSTRAINT eez_declaration_checks_result_check CHECK (result IN ('ok', 'ko', 'na'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('eez_declaration_checks');
        Schema::dropIfExists('eez_declaration_catches');
        Schema::dropIfExists('eez_declarations');
    }
};
