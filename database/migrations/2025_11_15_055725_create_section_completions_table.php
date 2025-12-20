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
        Schema::create('section_completions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('section_id')->constrained('course_sections')->onDelete('cascade');

            // Progress Tracking
            $table->decimal('completion_percentage', 5, 2)->default(0);
            $table->integer('modules_completed')->default(0);
            $table->integer('total_modules')->default(0);

            // Dates
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();

            $table->timestamps();

            // Unique Constraint
            $table->unique(['student_id', 'section_id']);

            // Indexes
            $table->index('student_id');
            $table->index('section_id');
            $table->index('completion_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_completions');
    }
};
