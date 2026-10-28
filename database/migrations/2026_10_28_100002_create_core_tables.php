<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_modules', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('slug', 40)->unique();
            $table->string('version', 20)->default('1.0.0');
            $table->boolean('is_active')->default(true);
            $table->jsonb('dependencies')->default('[]');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });

        Schema::create('reference_lists', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->timestamps();
        });

        Schema::create('reference_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reference_list_id')->constrained()->cascadeOnDelete();
            $table->string('code', 60);
            $table->jsonb('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['reference_list_id', 'code']);
        });

        Schema::create('custom_field_definitions', function (Blueprint $table) {
            $table->id();
            $table->string('subject_type', 80);
            $table->string('code', 60);
            $table->jsonb('label');
            $table->string('type');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['subject_type', 'code']);
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_field_definition_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('subject_id');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['custom_field_definition_id', 'subject_id'], 'custom_field_values_unique');
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mime_type')->nullable();
            $table->string('disk', 40)->default('local');
            $table->string('path');
            $table->char('sha256', 64)->nullable();
            $table->boolean('is_frozen')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('documentables', function (Blueprint $table) {
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('documentable_type', 80);
            $table->unsignedBigInteger('documentable_id');
            $table->primary(['document_id', 'documentable_type', 'documentable_id']);
            $table->index(['documentable_type', 'documentable_id']);
        });

        Schema::create('vessel_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->timestampTz('occurred_at');
            $table->string('module', 40);
            $table->string('type', 60);
            $table->string('subject_type', 80)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('summary', 500);
            $table->foreignId('zone_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 30)->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['vessel_id', 'occurred_at']);
            $table->index(['module', 'type']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('vessel_current_states', function (Blueprint $table) {
            $table->foreignId('vessel_id')->constrained()->cascadeOnDelete();
            $table->primary('vessel_id');
            $table->string('license_status')->nullable();
            $table->date('license_expires_on')->nullable();
            $table->boolean('beacon_fault_open')->default(false);
            $table->timestampTz('last_position_at')->nullable();
            $table->boolean('enhanced_monitoring')->default(false);
            $table->unsignedInteger('open_infractions')->default(0);
            $table->unsignedInteger('verification_count')->default(0);
            $table->string('traffic_light')->default('compliant');
            $table->timestampTz('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vessel_current_states');
        Schema::dropIfExists('vessel_events');
        Schema::dropIfExists('documentables');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_field_definitions');
        Schema::dropIfExists('reference_values');
        Schema::dropIfExists('reference_lists');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('app_modules');
    }
};
