<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_researches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documentation_category_id')
                ->constrained('documentation_categories')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // No unique index on (category, slug) on purpose: this table soft-deletes,
            // and a unique index would block re-creating a research whose slug was
            // freed by a soft delete. The controller de-duplicates with withTrashed().
            $table->index(['documentation_category_id', 'sort_order'], 'doc_researches_cat_sort');
            $table->index(['documentation_category_id', 'slug'], 'doc_researches_cat_slug');
            $table->index('is_active', 'doc_researches_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_researches');
    }
};
