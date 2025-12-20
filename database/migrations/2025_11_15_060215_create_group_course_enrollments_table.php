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
        Schema::create('group_course_enrollments', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('course_groups')->onDelete('cascade');
            $table->foreignId('enrolled_by')->constrained('users')->onDelete('cascade');

            // Enrollment Info
            $table->timestamp('enrollment_date')->useCurrent();
            $table->enum('enrollment_status', ['active', 'suspended', 'cancelled'])->default('active');

            // Settings
            $table->boolean('auto_enroll_new_members')->default(true)->comment('Auto-enroll new group members');

            $table->timestamps();

            // Unique Constraint
            $table->unique(['course_id', 'group_id']);

            // Indexes
            $table->index('course_id');
            $table->index('group_id');
            $table->index('enrollment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_course_enrollments');
    }
};
