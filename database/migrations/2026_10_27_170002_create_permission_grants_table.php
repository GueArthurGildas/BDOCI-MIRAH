<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('permission_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('action_id')->constrained('action_catalog')->cascadeOnDelete();
            $table->foreignId('zone_id')->nullable()->constrained();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->timestamps();

            $table->index('action_id');
        });

        DB::statement('ALTER TABLE permission_grants ADD CONSTRAINT permission_grants_one_holder_check CHECK ((role_id IS NOT NULL) <> (permission_group_id IS NOT NULL))');
        DB::statement('CREATE UNIQUE INDEX permission_grants_role_permanent_unique ON permission_grants (role_id, action_id) WHERE role_id IS NOT NULL AND zone_id IS NULL AND valid_from IS NULL AND valid_to IS NULL');
        DB::statement('CREATE UNIQUE INDEX permission_grants_group_permanent_unique ON permission_grants (permission_group_id, action_id) WHERE permission_group_id IS NOT NULL AND zone_id IS NULL AND valid_from IS NULL AND valid_to IS NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_grants');
    }
};
