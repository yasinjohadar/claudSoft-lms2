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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->foreignId('course_category_id')->constrained('course_categories')->onDelete('restrict');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('code', 50)->unique()->nullable();
            $table->longText('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('image')->nullable();

            // Instructor
            $table->foreignId('instructor_id')->nullable()->constrained('users')->onDelete('set null');

            // Course Details
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'expert'])->default('beginner');
            $table->enum('language', ['ar', 'en', 'both'])->default('ar');
            $table->integer('duration_in_hours')->nullable();

            // Pricing
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_free')->default(false);

            // Visibility & Publishing
            $table->boolean('is_published')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->boolean('featured')->default(false);

            // Enrollment Settings
            $table->enum('enrollment_type', ['open', 'by_approval', 'invite_only'])->default('open');
            $table->boolean('auto_enroll')->default(false);
            $table->integer('max_students')->nullable();

            // Date Settings
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();
            $table->dateTime('enrollment_start_date')->nullable();
            $table->dateTime('enrollment_end_date')->nullable();

            // Course Settings
            $table->json('completion_criteria')->nullable();
            $table->integer('sort_order')->default(0);

            // SEO
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            // Auditing
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_published');
            $table->index('is_visible');
            $table->index('featured');
            $table->index('course_category_id');
            $table->index('instructor_id');
            $table->index(['available_from', 'available_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
