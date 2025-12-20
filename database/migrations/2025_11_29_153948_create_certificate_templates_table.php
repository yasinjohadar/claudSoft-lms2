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
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['html', 'image'])->default('html');
            $table->string('template_file')->nullable(); // For image templates
            $table->text('html_content')->nullable(); // For HTML templates
            $table->json('field_positions')->nullable(); // Positions for text overlay on images
            $table->json('dynamic_fields')->nullable(); // Available fields: {student_name}, {course_name}, etc.
            $table->json('requirements')->nullable(); // Completion %, attendance %, etc.
            $table->boolean('auto_issue')->default(false);
            $table->boolean('requires_attendance')->default(false);
            $table->integer('min_attendance_percentage')->nullable();
            $table->boolean('requires_completion')->default(true);
            $table->integer('min_completion_percentage')->default(100);
            $table->boolean('requires_final_exam')->default(false);
            $table->integer('min_final_exam_score')->nullable();
            $table->boolean('has_expiry')->default(false);
            $table->integer('expiry_months')->nullable();
            $table->enum('orientation', ['portrait', 'landscape'])->default('landscape');
            $table->string('page_size')->default('A4');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
