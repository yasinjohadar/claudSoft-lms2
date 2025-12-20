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
        Schema::create('module_completions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('module_id')->constrained('course_modules')->onDelete('cascade');

            // Completion Status
            $table->enum('completion_status', ['not_started', 'in_progress', 'completed'])->default('not_started');

            // Score & Progress
            $table->decimal('score', 10, 2)->nullable();
            $table->integer('time_spent')->nullable()->comment('minutes');

            // Completion Dates
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();

            $table->timestamps();

            // Unique Constraint: student can complete module once
            $table->unique(['student_id', 'module_id']);

            // Indexes
            $table->index('student_id');
            $table->index('module_id');
            $table->index('completion_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_completions');
    }
};
