<?php

use App\Models\DataSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $objectType = (new DataSource)->getMorphClass();

        DB::table('data_source_comments')->whereNotNull('user_id')->orderBy('id')->each(function (object $comment) use ($objectType) {
            DB::table('comments')->insert([
                'object_type' => $objectType,
                'object_id' => $comment->data_source_id,
                'user_id' => $comment->user_id,
                'kind' => $comment->kind,
                'body' => $comment->body,
                'created_at' => $comment->created_at,
            ]);
        });

        Schema::dropIfExists('data_source_comments');
    }

    public function down(): void
    {
        Schema::create('data_source_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_source_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind');
            $table->text('body');
            $table->timestamps();

            $table->index(['data_source_id', 'created_at']);
        });
    }
};
