<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all published blog posts to be indexable
        DB::table('blog_posts')
            ->where('status', 'published')
            ->where(function($query) {
                $query->whereNull('is_indexable')
                      ->orWhere('is_indexable', false);
            })
            ->update(['is_indexable' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: Set is_indexable to false for published posts
        // Note: This is a destructive operation, use with caution
        DB::table('blog_posts')
            ->where('status', 'published')
            ->where('is_indexable', true)
            ->update(['is_indexable' => false]);
    }
};
