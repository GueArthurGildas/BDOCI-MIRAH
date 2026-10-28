<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_connectors', function (Blueprint $table) {
            $table->id();
            $table->string('supplier', 80);
            $table->string('domain', 190)->nullable();
            $table->jsonb('parameters')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_connectors');
    }
};
