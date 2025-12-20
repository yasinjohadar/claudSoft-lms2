<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('frontend_courses', function (Blueprint $table) {
            // Open Graph (Facebook, LinkedIn, etc.)
            $table->string('og_title')->nullable()->after('meta_keywords');
            $table->text('og_description')->nullable()->after('og_title');
            $table->string('og_image')->nullable()->after('og_description');
            $table->string('og_type')->default('article')->after('og_image');

            // Twitter Card
            $table->string('twitter_card')->default('summary_large_image')->after('og_type');
            $table->string('twitter_title')->nullable()->after('twitter_card');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->string('twitter_image')->nullable()->after('twitter_description');

            // Advanced SEO
            $table->string('canonical_url')->nullable()->after('twitter_image');
            $table->string('robots')->default('index, follow')->after('canonical_url');
            $table->string('author')->nullable()->after('robots');

            // Structured Data (Schema.org)
            $table->json('schema_markup')->nullable()->after('author')->comment('JSON-LD structured data');

            // Additional SEO
            $table->string('focus_keyword')->nullable()->after('schema_markup');
            $table->text('seo_score')->nullable()->after('focus_keyword')->comment('SEO analysis score');
            $table->integer('reading_time')->nullable()->after('seo_score')->comment('Estimated reading time in minutes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frontend_courses', function (Blueprint $table) {
            $table->dropColumn([
                'og_title',
                'og_description',
                'og_image',
                'og_type',
                'twitter_card',
                'twitter_title',
                'twitter_description',
                'twitter_image',
                'canonical_url',
                'robots',
                'author',
                'schema_markup',
                'focus_keyword',
                'seo_score',
                'reading_time',
            ]);
        });
    }
};
