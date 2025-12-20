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
        Schema::create('frontend_course_lessons', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('section_id')->constrained('frontend_course_sections')->onDelete('cascade');

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();

            // Ordering
            $table->integer('order')->default(0);

            // Lesson Type & Content
            $table->enum('type', ['video', 'text', 'file', 'quiz', 'live'])->default('video');
            $table->string('video_url')->nullable();
            $table->integer('duration')->nullable()->comment('Duration in minutes');

            // Visibility & Access
            $table->boolean('is_active')->default(true);
            $table->boolean('is_free')->default(false)->comment('Preview lesson');

            $table->timestamps();

            // Indexes
            $table->index('section_id');
            $table->index('order');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_course_lessons');
    }
};
