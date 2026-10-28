<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('label', 120)->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permission_group_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_group_id')->constrained()->cascadeOnDelete();

            $table->primary(['user_id', 'permission_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_group_user');
        Schema::dropIfExists('permission_groups');
    }
};
