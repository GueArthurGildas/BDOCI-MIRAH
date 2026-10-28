<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('witnesses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 190);
            $table->string('category', 40);
            $table->string('email', 190)->nullable();
            $table->string('phone', 40)->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE witnesses ADD CONSTRAINT witnesses_category_check CHECK (category IN ('state_vessel', 'fisher', 'coaster', 'port_authority', 'other'))");

        Schema::create('witness_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamp('received_at');
            $table->foreignId('witness_id')->nullable()->constrained();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->string('channel', 40);
            $table->foreignId('operation_id')->nullable()->constrained();
            $table->foreignId('entered_by')->nullable()->constrained('users');
            $table->string('status', 40)->default('received');
            $table->timestamps();

            $table->index(['status', 'received_at']);
        });

        DB::statement("ALTER TABLE witness_reports ADD CONSTRAINT witness_reports_channel_check CHECK (channel IN ('email', 'fax', 'phone', 'mail', 'form'))");
        DB::statement("ALTER TABLE witness_reports ADD CONSTRAINT witness_reports_status_check CHECK (status IN ('received', 'verified', 'closed', 'forwarded'))");

        Schema::create('reported_vessels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('witness_report_id')->constrained('witness_reports');
            $table->smallInteger('rank');
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->string('name', 190)->nullable();
            $table->string('flag', 80)->nullable();
            $table->string('registration_port', 120)->nullable();
            $table->string('type', 80)->nullable();
            $table->string('ircs', 20)->nullable();
            $table->string('registration', 40)->nullable();
            $table->string('other_marks', 160)->nullable();
            $table->string('gear', 80)->nullable();
            $table->boolean('is_fishing')->nullable();
            $table->string('route', 120)->nullable();
            $table->boolean('is_drifting')->nullable();
            $table->boolean('is_resupplying')->nullable();
            $table->boolean('is_transshipping')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['witness_report_id', 'rank']);
        });

        DB::statement('ALTER TABLE reported_vessels ADD CONSTRAINT reported_vessels_rank_check CHECK (rank IN (1, 2))');

        Schema::create('report_usabilities', function (Blueprint $table) {
            $table->foreignId('witness_report_id')->primary()->constrained('witness_reports');
            $table->boolean('is_usable');
            $table->text('interview_report')->nullable();
        });

        Schema::create('report_investigations', function (Blueprint $table) {
            $table->foreignId('witness_report_id')->primary()->constrained('witness_reports');
            $table->jsonb('elements')->nullable();
            $table->text('notes')->nullable();
        });

        Schema::create('report_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('witness_report_id')->constrained('witness_reports');
            $table->string('decision', 40);
            $table->foreignId('decided_by')->constrained('users');
            $table->timestamp('decided_at');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE report_decisions ADD CONSTRAINT report_decisions_decision_check CHECK (decision IN ('dismissal', 'forward_to_instruction'))");

        Schema::create('authorized_list_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authorized_list_publication_id')->constrained('authorized_list_publications');
            $table->string('recipient', 190);
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorized_list_distributions');
        Schema::dropIfExists('report_decisions');
        Schema::dropIfExists('report_investigations');
        Schema::dropIfExists('report_usabilities');
        Schema::dropIfExists('reported_vessels');
        Schema::dropIfExists('witness_reports');
        Schema::dropIfExists('witnesses');
    }
};
