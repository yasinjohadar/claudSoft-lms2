<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_comment_likes')) {
            return;
        }

        Schema::create('project_comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_comment_id')->constrained('project_comments', 'id', 'pc_clikes_comment_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id', 'pc_clikes_user_fk')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_comment_id', 'user_id'], 'pc_clikes_comment_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_comment_likes');
    }
};
