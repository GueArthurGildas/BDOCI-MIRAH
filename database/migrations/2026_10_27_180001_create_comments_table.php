<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('object_type', 80);
            $table->unsignedBigInteger('object_id');
            $table->foreignId('user_id')->constrained();
            $table->string('kind', 40)->nullable();
            $table->text('body');
            $table->timestamp('created_at')->nullable();

            $table->index(['object_type', 'object_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
