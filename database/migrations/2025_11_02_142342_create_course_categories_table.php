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
        Schema::create('course_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // أيقونة التصنيف
            $table->string('color', 7)->default('#0d6efd'); // لون التصنيف (hex color)
            $table->string('image')->nullable(); // صورة التصنيف
            $table->integer('order')->default(0); // ترتيب العرض
            $table->boolean('is_active')->default(true); // فعال/غير فعال
            $table->foreignId('parent_id')->nullable()->constrained('course_categories')->onDelete('cascade'); // للتصنيفات الفرعية
            $table->timestamps();
            $table->softDeletes(); // للحذف الناعم

            // Indexes for better performance
            $table->index('slug');
            $table->index('is_active');
            $table->index('parent_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
