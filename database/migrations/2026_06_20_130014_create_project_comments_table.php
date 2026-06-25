<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_comments')) {
            return;
        }

        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_showcase_id')->constrained('project_showcases', 'id', 'pc_comments_showcase_fk')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'id', 'pc_comments_user_fk')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('project_comments', 'id', 'pc_comments_parent_fk')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_edited')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['project_showcase_id', 'created_at'], 'pc_comments_showcase_date_idx');
            $table->index('parent_id', 'pc_comments_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_comments');
    }
};
