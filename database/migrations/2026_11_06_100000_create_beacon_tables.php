<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beacons', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number', 80)->unique();
            $table->string('model', 80)->nullable();
            $table->string('manufacturer', 80)->nullable();
            $table->string('supplier', 80)->nullable();
            $table->string('modem', 40)->nullable();
            $table->string('subscription', 80)->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE beacons ADD CONSTRAINT beacons_modem_check CHECK (modem IN ('gnss', 'satellite', 'gprs'))");

        Schema::create('beacon_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->foreignId('beacon_id')->constrained();
            $table->date('started_on');
            $table->date('ended_on')->nullable();
            $table->string('replacement_reason')->nullable();
            $table->timestamps();

            $table->index(['vessel_id', 'started_on']);
        });

        DB::statement('CREATE UNIQUE INDEX beacon_assignments_active_beacon_unique ON beacon_assignments (beacon_id) WHERE ended_on IS NULL');
        DB::statement('CREATE UNIQUE INDEX beacon_assignments_active_vessel_unique ON beacon_assignments (vessel_id) WHERE ended_on IS NULL');

        Schema::create('approved_installers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_entity_id')->constrained();
            $table->date('approved_until')->nullable();
            $table->timestamps();
        });

        Schema::create('vms_assessment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->date('requested_on');
            $table->string('status', 40);
            $table->timestamps();
        });

        Schema::create('new_beacon_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->date('requested_on');
            $table->string('status', 40);
            $table->timestamps();
        });

        Schema::create('compatibility_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_request_id')->constrained('vms_assessment_requests');
            $table->string('result', 40);
            $table->date('assessed_on');
            $table->timestamps();
        });

        Schema::create('beacon_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_beacon_request_id')->constrained('new_beacon_requests');
            $table->foreignId('installer_id')->constrained('approved_installers');
            $table->date('installed_on');
            $table->foreignId('report_document_id')->nullable()->constrained('documents');
            $table->timestamps();
        });

        Schema::create('vessel_reporting_obligations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained();
            $table->boolean('requires_vms')->default(false);
            $table->boolean('requires_ais')->default(false);
            $table->boolean('requires_ers')->default(false);
            $table->boolean('requires_eez_declaration')->default(false);
            $table->boolean('requires_fishing_log')->default(false);
            $table->timestamps();

            $table->unique('vessel_id');
        });

        DB::statement("ALTER TABLE vms_assessment_requests ADD CONSTRAINT vms_assessment_requests_status_check CHECK (status IN ('requested', 'assessed', 'closed'))");
        DB::statement("ALTER TABLE new_beacon_requests ADD CONSTRAINT new_beacon_requests_status_check CHECK (status IN ('requested', 'installed', 'closed'))");
        DB::statement("ALTER TABLE compatibility_assessments ADD CONSTRAINT compatibility_assessments_result_check CHECK (result IN ('compatible', 'incompatible', 'to_complete'))");

        $this->importLegacyDevices();
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_reporting_obligations');
        Schema::dropIfExists('beacon_installations');
        Schema::dropIfExists('compatibility_assessments');
        Schema::dropIfExists('new_beacon_requests');
        Schema::dropIfExists('vms_assessment_requests');
        Schema::dropIfExists('approved_installers');
        Schema::dropIfExists('beacon_assignments');
        Schema::dropIfExists('beacons');
    }

    private function importLegacyDevices(): void
    {
        $devices = DB::table('tracking_devices')->where('system', 'vms')->orderBy('id')->get();
        $now = now();

        foreach ($devices as $device) {
            $beaconId = DB::table('beacons')->insertGetId([
                'serial_number' => $device->identifier,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('beacon_assignments')->insert([
                'vessel_id' => $device->vessel_id,
                'beacon_id' => $beaconId,
                'started_on' => $device->installed_at,
                'ended_on' => $device->removed_at,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
