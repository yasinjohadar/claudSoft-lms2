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
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name')->comment('اسم اللوحة');
            $table->string('slug')->unique()->comment('معرف فريد');
            $table->text('description')->nullable()->comment('الوصف');
            $table->string('icon')->nullable()->comment('الأيقونة');

            // Type & Scope
            $table->enum('type', ['global', 'course', 'weekly', 'monthly', 'speed', 'accuracy', 'streak', 'social'])
                  ->default('global')
                  ->comment('نوع اللوحة');

            // Related Entity (للوحات الكورسات)
            $table->foreignId('course_id')->nullable()->constrained('courses')->onDelete('cascade');

            // Time Period
            $table->enum('period', ['all_time', 'daily', 'weekly', 'monthly', 'yearly', 'season'])->default('all_time');
            $table->date('start_date')->nullable()->comment('تاريخ البداية');
            $table->date('end_date')->nullable()->comment('تاريخ النهاية');

            // Season Info
            $table->integer('season_number')->unsigned()->nullable()->comment('رقم الموسم');
            $table->string('season_name')->nullable()->comment('اسم الموسم');

            // Ranking Criteria
            $table->string('metric')->default('total_points')->comment('المقياس المستخدم للترتيب');
            $table->enum('sort_direction', ['asc', 'desc'])->default('desc');

            // Limits
            $table->integer('max_entries')->unsigned()->default(100)->comment('أقصى عدد للمداخل');
            $table->integer('min_score')->unsigned()->default(0)->comment('الحد الأدنى للنقاط للظهور');

            // Division System
            $table->boolean('has_divisions')->default(true)->comment('يحتوي على فئات؟');
            $table->json('division_thresholds')->nullable()->comment('حدود الفئات');

            // Status
            $table->boolean('is_active')->default(true)->comment('مفعل؟');
            $table->boolean('is_visible')->default(true)->comment('مرئي للطلاب؟');

            // Cache
            $table->timestamp('last_updated_at')->nullable()->comment('آخر تحديث للتصنيف');

            // Display
            $table->integer('sort_order')->default(0)->comment('ترتيب العرض');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('type');
            $table->index('period');
            $table->index('course_id');
            $table->index('is_active');
            $table->index(['start_date', 'end_date']);
            $table->index('season_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};
