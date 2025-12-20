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
        Schema::create('rewards_catalog', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name')->comment('اسم المكافأة');
            $table->string('slug')->unique()->comment('معرف فريد');
            $table->text('description')->nullable()->comment('الوصف');
            $table->string('image')->nullable()->comment('صورة المكافأة');

            // Category & Type
            $table->enum('category', ['educational', 'digital', 'physical', 'privilege', 'real_world'])
                  ->default('educational')
                  ->comment('الفئة');
            $table->string('type')->nullable()->comment('النوع الفرعي');

            // Cost
            $table->integer('points_cost')->unsigned()->default(0)->comment('تكلفة النقاط');
            $table->integer('level_required')->unsigned()->default(1)->comment('المستوى المطلوب');

            // Availability
            $table->boolean('is_available')->default(true)->comment('متاح؟');
            $table->boolean('is_limited')->default(false)->comment('محدود العدد؟');
            $table->integer('stock_quantity')->unsigned()->nullable()->comment('الكمية المتوفرة');
            $table->integer('purchased_count')->unsigned()->default(0)->comment('عدد المشتريات');
            $table->integer('max_per_user')->unsigned()->nullable()->comment('أقصى عدد للمستخدم');

            // Validity
            $table->timestamp('available_from')->nullable()->comment('متاح من');
            $table->timestamp('available_until')->nullable()->comment('متاح حتى');
            $table->integer('validity_days')->unsigned()->nullable()->comment('صلاحية المكافأة بالأيام');

            // Rarity & Value
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->integer('actual_value')->unsigned()->nullable()->comment('القيمة الفعلية');

            // Delivery Info
            $table->enum('delivery_type', ['instant', 'manual', 'code', 'physical'])->default('instant');
            $table->text('delivery_instructions')->nullable()->comment('تعليمات التسليم');

            // Display
            $table->boolean('is_featured')->default(false)->comment('مميز؟');
            $table->integer('sort_order')->default(0)->comment('ترتيب العرض');
            $table->string('badge_color', 7)->nullable()->comment('لون الشارة');

            // Metadata
            $table->json('metadata')->nullable()->comment('بيانات إضافية');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('category');
            $table->index('is_available');
            $table->index('points_cost');
            $table->index('level_required');
            $table->index('is_featured');
            $table->index(['available_from', 'available_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards_catalog');
    }
};
