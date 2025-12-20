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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();
            $table->longText('instructions')->nullable();

            // Course/Lesson Relationship
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->onDelete('cascade');

            // Assignment Settings
            $table->integer('max_grade')->default(100);
            $table->enum('submission_type', ['link', 'file', 'both'])->default('both');
            $table->integer('max_links')->default(5)->comment('Maximum number of links allowed');
            $table->integer('max_files')->default(5)->comment('Maximum number of files allowed');
            $table->integer('max_file_size')->default(10240)->comment('Max file size in KB');

            // Deadlines
            $table->dateTime('available_from')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->dateTime('late_submission_until')->nullable();
            $table->boolean('allow_late_submission')->default(false);
            $table->integer('late_penalty_percentage')->default(0)->comment('Percentage deducted for late submissions');

            // Visibility & Publishing
            $table->boolean('is_published')->default(false);
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);

            // Attachments for instructions
            $table->json('attachments')->nullable()->comment('Instructor attachments/resources');

            // Auditing
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('course_id');
            $table->index('lesson_id');
            $table->index('is_published');
            $table->index('is_visible');
            $table->index(['due_date', 'is_published']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
