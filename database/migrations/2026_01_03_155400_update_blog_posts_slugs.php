<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\BlogPost;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update slugs for all blog posts that have empty or incorrect slugs
        $posts = BlogPost::all();
        
        foreach ($posts as $post) {
            // Generate new slug from title if slug is empty or seems incorrect
            if (empty($post->slug) || strlen($post->slug) < 3) {
                $newSlug = Str::slug($post->title, '-', 'ar');
                
                // If slug is still empty, use fallback
                if (empty($newSlug)) {
                    $newSlug = 'post-' . $post->id;
                }
                
                // Check for unique slug
                $counter = 1;
                $originalSlug = $newSlug;
                while (BlogPost::where('slug', $newSlug)->where('id', '!=', $post->id)->exists()) {
                    $newSlug = $originalSlug . '-' . $counter++;
                }
                
                $post->slug = $newSlug;
                $post->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse slug updates as we don't know the original slugs
        // This migration is irreversible
    }
};
