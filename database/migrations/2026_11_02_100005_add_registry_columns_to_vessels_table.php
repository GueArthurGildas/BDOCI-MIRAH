<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vessels', function (Blueprint $table) {
            $table->string('record_status', 40)->default('validated')->after('name');
            $table->string('creation_origin', 40)->default('manual_entry')->after('record_status');
            $table->string('fleet_segment', 40)->nullable()->after('category');
            $table->foreignId('vessel_type_id')->nullable()->after('fleet_segment')->constrained();
            $table->string('fleet_type', 40)->nullable()->after('vessel_type_id');
            $table->boolean('is_tuna_vessel')->default(false)->after('fleet_type');
            $table->string('legal_status', 40)->nullable()->after('is_tuna_vessel');
            $table->foreignId('flag_country_id')->nullable()->after('legal_status')->constrained('countries');
            $table->foreignId('home_port_id')->nullable()->after('flag_country_id')->constrained('ports');
            $table->smallInteger('construction_year')->nullable()->after('home_port_id');
            $table->decimal('beam_m', 6, 2)->nullable()->after('length_meters');
            $table->decimal('depth_over_keel_m', 6, 2)->nullable()->after('beam_m');
            $table->decimal('net_tonnage', 10, 2)->nullable()->after('gross_tonnage');
            $table->string('propulsion', 120)->nullable()->after('net_tonnage');
            $table->decimal('hold_capacity_m3', 10, 2)->nullable()->after('propulsion');
            $table->string('registration_certificate', 80)->nullable()->after('hold_capacity_m3');
            $table->string('fao_identifier', 40)->nullable()->unique()->after('registration_certificate');
            $table->boolean('license_not_provided')->default(true)->after('fao_identifier');
        });

        DB::statement('ALTER TABLE vessels ALTER COLUMN registration_number DROP NOT NULL');
        DB::statement('ALTER TABLE vessels RENAME COLUMN length_meters TO length_m');
        DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_record_status_check CHECK (record_status IN ('draft', 'to_match', 'validated'))");
        DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_creation_origin_check CHECK (creation_origin IN ('manual_entry', 'themis', 'neno', 'e_inspection', 'dpep', 'mrep'))");
        DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_fleet_segment_check CHECK (fleet_segment IS NULL OR fleet_segment IN ('patrol_vessel', 'industrial', 'semi_industrial', 'artisanal', 'authorized_foreign', 'known_unauthorized'))");
        DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_fleet_type_check CHECK (fleet_type IS NULL OR fleet_type IN ('fishing', 'support', 'reefer', 'other'))");
        DB::statement("ALTER TABLE vessels ADD CONSTRAINT vessels_legal_status_check CHECK (legal_status IS NULL OR legal_status IN ('ivorian', 'chartered', 'foreign'))");

        Schema::table('vessels', function (Blueprint $table) {
            $table->dropColumn(['flag', 'owner']);
        });
    }

    public function down(): void
    {
        Schema::table('vessels', function (Blueprint $table) {
            $table->char('flag', 2)->default('CI');
            $table->string('owner')->nullable();
        });

        foreach (['record_status', 'creation_origin', 'fleet_segment', 'fleet_type', 'legal_status'] as $column) {
            DB::statement("ALTER TABLE vessels DROP CONSTRAINT IF EXISTS vessels_{$column}_check");
        }

        DB::statement('ALTER TABLE vessels RENAME COLUMN length_m TO length_meters');

        Schema::table('vessels', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vessel_type_id');
            $table->dropConstrainedForeignId('flag_country_id');
            $table->dropConstrainedForeignId('home_port_id');
            $table->dropColumn([
                'record_status', 'creation_origin', 'fleet_segment', 'fleet_type', 'is_tuna_vessel', 'legal_status',
                'construction_year', 'beam_m', 'depth_over_keel_m', 'net_tonnage', 'propulsion', 'hold_capacity_m3',
                'registration_certificate', 'fao_identifier', 'license_not_provided',
            ]);
        });
    }
};
