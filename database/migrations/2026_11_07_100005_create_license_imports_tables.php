<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('license_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained();
            $table->string('status', 40)->default('preview');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
        });

        DB::statement("ALTER TABLE license_imports ADD CONSTRAINT license_imports_status_check CHECK (status IN ('preview', 'valid', 'cancelled'))");

        Schema::create('license_import_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_import_id')->constrained();
            $table->foreignId('vessel_id')->nullable()->constrained();
            $table->jsonb('data');
            $table->string('status', 40)->default('to_validate');
            $table->string('rejection_reason')->nullable();
            $table->foreignId('license_id')->nullable()->constrained();
            $table->timestamps();

            $table->index('license_import_id');
        });

        DB::statement("ALTER TABLE license_import_lines ADD CONSTRAINT license_import_lines_status_check CHECK (status IN ('to_validate', 'valid', 'rejected'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('license_import_lines');
        Schema::dropIfExists('license_imports');
    }
};
