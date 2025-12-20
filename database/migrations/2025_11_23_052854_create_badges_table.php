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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name')->comment('اسم الوسام');
            $table->string('slug')->unique()->comment('معرف فريد للوسام');
            $table->text('description')->nullable()->comment('وصف الوسام');
            $table->string('icon')->nullable()->comment('أيقونة الوسام (مسار الصورة)');

            // Categorization
            $table->enum('type', ['achievement', 'progress', 'performance', 'engagement', 'special', 'event', 'social'])
                  ->default('achievement')
                  ->comment('نوع الوسام');
            $table->string('category', 100)->nullable()->comment('تصنيف فرعي');

            // Rarity System (5 مستويات)
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary', 'mythic'])
                  ->default('common')
                  ->comment('ندرة الوسام');

            // Criteria & Requirements
            $table->json('criteria')->nullable()->comment('معايير الحصول على الوسام (JSON)');

            // Points & Rewards
            $table->integer('points_value')->unsigned()->default(0)->comment('قيمة النقاط عند الحصول على الوسام');

            // Status & Visibility
            $table->boolean('is_active')->default(true)->comment('هل الوسام مفعل؟');
            $table->boolean('is_hidden')->default(false)->comment('وسام مخفي (لا يظهر حتى يتم الحصول عليه)');

            // Order & Display
            $table->integer('sort_order')->default(0)->comment('ترتيب العرض');
            $table->string('color_code', 7)->nullable()->comment('لون الوسام (hex)');

            // Statistics (سيتم تحديثها عبر queries)
            $table->integer('awarded_count')->unsigned()->default(0)->comment('عدد مرات منح الوسام');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('rarity');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
