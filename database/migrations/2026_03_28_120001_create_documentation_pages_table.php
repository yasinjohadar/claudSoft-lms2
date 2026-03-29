<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documentation_category_id')
                ->constrained('documentation_categories')
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('documentation_pages')
                ->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 32)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_indexable')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['documentation_category_id', 'parent_id', 'sort_order'], 'doc_pages_cat_parent_sort');
            $table->index(['documentation_category_id', 'slug'], 'doc_pages_cat_slug');
            $table->index('status', 'doc_pages_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_pages');
    }
};
