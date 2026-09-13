<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentation_pages', function (Blueprint $table) {
            $table->foreignId('documentation_research_id')
                ->nullable()
                ->after('documentation_category_id')
                ->constrained('documentation_researches')
                ->nullOnDelete();

            // Ordering inside a research is deliberately separate from sort_order:
            // sort_order drives the public docs tree, and dragging pages around in
            // the admin must never reshuffle what visitors see.
            $table->unsignedInteger('research_sort_order')->default(0)->after('sort_order');

            $table->index(['documentation_research_id', 'research_sort_order'], 'doc_pages_research_sort');
        });
    }

    public function down(): void
    {
        Schema::table('documentation_pages', function (Blueprint $table) {
            $table->dropIndex('doc_pages_research_sort');
            $table->dropConstrainedForeignId('documentation_research_id');
            $table->dropColumn('research_sort_order');
        });
    }
};
