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
        Schema::create('frontend_course_sections', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('frontend_courses')->onDelete('cascade');

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();

            // Ordering
            $table->integer('order')->default(0);

            // Visibility
            $table->boolean('is_active')->default(true);

            // Statistics
            $table->integer('lessons_count')->default(0);
            $table->decimal('duration', 8, 2)->default(0)->comment('Total duration in hours');

            $table->timestamps();

            // Indexes
            $table->index('course_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_course_sections');
    }
};
