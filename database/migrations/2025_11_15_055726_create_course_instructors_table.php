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
        Schema::create('course_instructors', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');

            // Role
            $table->enum('role', ['main_instructor', 'co_instructor', 'teaching_assistant'])->default('co_instructor');

            // Permissions
            $table->json('permissions')->nullable()->comment('custom permissions for this instructor');

            $table->timestamps();

            // Unique Constraint
            $table->unique(['course_id', 'instructor_id']);

            // Indexes
            $table->index('course_id');
            $table->index('instructor_id');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_instructors');
    }
};
