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
        Schema::table('gamification_badges', function (Blueprint $table) {
            // Add missing columns from BadgeController
            $table->string('slug')->unique()->nullable()->after('name')->comment('معرف فريد للوسام');
            $table->string('category', 100)->nullable()->after('type')->comment('تصنيف فرعي');
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary', 'mythic'])
                  ->default('common')
                  ->after('category')
                  ->comment('ندرة الوسام');
            $table->json('criteria')->nullable()->after('rarity')->comment('معايير الحصول على الوسام');
            $table->integer('points_value')->unsigned()->default(0)->after('criteria')->comment('قيمة النقاط عند الحصول');
            $table->boolean('is_visible')->default(true)->after('is_active')->comment('هل الوسام ظاهر في القائمة؟');
            $table->boolean('is_hidden')->default(false)->after('is_visible')->comment('وسام مخفي حتى يتم الحصول عليه');
            $table->integer('sort_order')->default(0)->after('is_hidden')->comment('ترتيب العرض');
            $table->string('color_code', 7)->nullable()->after('sort_order')->comment('لون الوسام (hex)');
            $table->integer('awarded_count')->unsigned()->default(0)->after('color_code')->comment('عدد مرات منح الوسام');

            // Add indexes for better performance
            $table->index('rarity');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gamification_badges', function (Blueprint $table) {
            $table->dropIndex(['rarity']);
            $table->dropIndex(['sort_order']);
            $table->dropColumn([
                'slug',
                'category',
                'rarity',
                'criteria',
                'points_value',
                'is_visible',
                'is_hidden',
                'sort_order',
                'color_code',
                'awarded_count'
            ]);
        });
    }
};
