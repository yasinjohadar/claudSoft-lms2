<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentation_page_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documentation_page_id')
                ->constrained('documentation_pages')
                ->cascadeOnDelete();
            $table->string('linkable_type');
            $table->unsignedBigInteger('linkable_id');
            $table->enum('placement', ['reference', 'curriculum'])->default('reference');
            $table->foreignId('course_module_id')
                ->nullable()
                ->constrained('course_modules')
                ->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['linkable_type', 'linkable_id']);
            $table->index(['documentation_page_id', 'placement']);
            $table->unique(
                ['documentation_page_id', 'linkable_type', 'linkable_id', 'placement'],
                'doc_page_links_unique_target'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentation_page_links');
    }
};
