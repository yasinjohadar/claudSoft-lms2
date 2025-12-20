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
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');

            // Enrollment Info
            $table->timestamp('enrollment_date')->useCurrent();
            $table->enum('enrollment_status', ['pending', 'active', 'completed', 'suspended', 'cancelled'])->default('active');
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->onDelete('set null')->comment('admin_id or null for self-enrollment');

            // Progress Tracking
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->json('progress')->nullable()->comment('detailed progress data');
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            // Certificate & Grade
            $table->boolean('certificate_issued')->default(false);
            $table->decimal('grade', 5, 2)->nullable();

            $table->timestamps();

            // Unique Constraint: student can enroll in course once
            $table->unique(['course_id', 'student_id']);

            // Indexes
            $table->index('course_id');
            $table->index('student_id');
            $table->index('enrollment_status');
            $table->index('enrollment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_enrollments');
    }
};
