<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SOURCES = "'manual_entry', 'themis', 'neno', 'e_inspection', 'dpep', 'mrep'";

    public function up(): void
    {
        Schema::create('vessel_identifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('value', 60);
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();

            $table->unique(['type', 'value', 'valid_from']);
            $table->index(['type', 'value']);
        });

        Schema::create('vessel_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('former_name', 190)->nullable();
            $table->foreignId('former_flag_country_id')->nullable()->constrained('countries');
            $table->date('changed_on')->nullable();
            $table->timestamps();
        });

        Schema::create('vessel_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('source', 40);
            $table->string('external_id', 120)->nullable();
            $table->timestamp('last_import_at')->nullable();
            $table->jsonb('raw_data')->nullable();
            $table->string('matching_status', 40)->default('new');
            $table->foreignId('candidate_vessel_id')->nullable()->constrained('vessels');
            $table->string('matching_criterion', 40)->nullable();
            $table->foreignId('settled_by')->nullable()->constrained('users');
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();

            $table->unique(['source', 'external_id']);
            $table->index('matching_status');
        });

        Schema::create('vessel_field_provenance', function (Blueprint $table) {
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('field', 60);
            $table->string('source', 40);
            $table->timestamp('updated_at');

            $table->primary(['vessel_id', 'field']);
        });

        Schema::create('source_priorities', function (Blueprint $table) {
            $table->string('field', 60);
            $table->string('source', 40);
            $table->smallInteger('rank');

            $table->primary(['field', 'source']);
        });

        Schema::create('vessel_actors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->string('role', 40);
            $table->foreignId('legal_entity_id')->nullable()->constrained();
            $table->foreignId('person_id')->nullable()->constrained('people');
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->timestamps();

            $table->index(['vessel_id', 'role']);
        });

        DB::statement("ALTER TABLE vessel_identifiers ADD CONSTRAINT vessel_identifiers_type_check CHECK (type IN ('imo', 'mmsi', 'ircs', 'registration', 'iccat', 'eu', 'beacon', 'other'))");
        DB::statement('CREATE UNIQUE INDEX vessel_identifiers_active_value_unique ON vessel_identifiers (type, value) WHERE valid_to IS NULL');
        DB::statement('ALTER TABLE vessel_sources ADD CONSTRAINT vessel_sources_source_check CHECK (source IN ('.self::SOURCES.'))');
        DB::statement("ALTER TABLE vessel_sources ADD CONSTRAINT vessel_sources_matching_status_check CHECK (matching_status IN ('matched', 'possible_duplicate', 'new', 'rejected'))");
        DB::statement("ALTER TABLE vessel_sources ADD CONSTRAINT vessel_sources_matching_criterion_check CHECK (matching_criterion IS NULL OR matching_criterion IN ('imo', 'mmsi', 'ircs', 'registration', 'name_flag'))");
        DB::statement('ALTER TABLE vessel_field_provenance ADD CONSTRAINT vessel_field_provenance_source_check CHECK (source IN ('.self::SOURCES.'))');
        DB::statement('ALTER TABLE source_priorities ADD CONSTRAINT source_priorities_source_check CHECK (source IN ('.self::SOURCES.'))');
        DB::statement("ALTER TABLE vessel_actors ADD CONSTRAINT vessel_actors_role_check CHECK (role IN ('owner', 'shipowner', 'beneficiary', 'local_agent', 'captain', 'fishing_captain'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_actors');
        Schema::dropIfExists('source_priorities');
        Schema::dropIfExists('vessel_field_provenance');
        Schema::dropIfExists('vessel_sources');
        Schema::dropIfExists('vessel_history');
        Schema::dropIfExists('vessel_identifiers');
    }
};
