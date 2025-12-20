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
        Schema::create('training_camps', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم المعسكر
            $table->string('slug')->unique(); // معرف URL
            $table->text('description')->nullable(); // وصف المعسكر
            $table->string('image')->nullable(); // صورة المعسكر
            $table->foreignId('category_id')->nullable()->constrained('course_categories')->onDelete('set null'); // التخصص/التصنيف
            $table->decimal('price', 10, 2)->default(0); // السعر
            $table->date('start_date'); // تاريخ البداية
            $table->date('end_date'); // تاريخ النهاية
            $table->integer('duration_days')->default(0); // عدد الأيام
            $table->string('instructor_name')->nullable(); // اسم المدرب
            $table->string('location')->nullable(); // الموقع (حضوري/أونلاين)
            $table->integer('max_participants')->nullable(); // الحد الأقصى للمشاركين
            $table->integer('current_participants')->default(0); // عدد المسجلين حالياً
            $table->boolean('is_active')->default(true); // نشط/غير نشط
            $table->boolean('is_featured')->default(false); // مميز
            $table->integer('order')->default(0); // ترتيب العرض
            $table->timestamps();
            $table->softDeletes(); // الحذف الناعم

            // Indexes for better performance
            $table->index('slug');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('category_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_camps');
    }
};
