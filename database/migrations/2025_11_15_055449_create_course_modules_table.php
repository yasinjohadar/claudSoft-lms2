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
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('course_sections')->onDelete('set null');

            // Polymorphic Relationship
            $table->enum('module_type', [
                'lesson',
                'video',
                'quiz',
                'programming_challenge',
                'assignment',
                'resource',
                'forum',
                'live_session'
            ]);
            $table->unsignedBigInteger('modulable_id');
            $table->string('modulable_type');

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();

            // Ordering
            $table->integer('sort_order')->default(0);

            // Visibility & Access
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_required')->default(false);
            $table->json('unlock_conditions')->nullable();

            // Date Settings
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();

            // Grading
            $table->boolean('is_graded')->default(false);
            $table->decimal('max_score', 10, 2)->nullable();

            // Completion
            $table->enum('completion_type', ['auto', 'manual', 'score_based'])->default('auto');
            $table->integer('estimated_duration')->nullable()->comment('minutes');

            // Quiz/Assignment Settings
            $table->integer('attempts_allowed')->nullable();
            $table->integer('time_limit')->nullable()->comment('minutes');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('course_id');
            $table->index('section_id');
            $table->index('module_type');
            $table->index(['modulable_id', 'modulable_type']);
            $table->index('sort_order');
            $table->index('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};
