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
        Schema::create('bulk_enrollment_sessions', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            // File Details
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();

            // Enrollment Type
            $table->enum('enrollment_type', ['individual', 'group'])->default('individual');
            $table->foreignId('group_id')->nullable()->constrained('course_groups')->onDelete('set null');

            // Statistics
            $table->integer('total_students')->default(0);
            $table->integer('successful_enrollments')->default(0);
            $table->integer('failed_enrollments')->default(0);
            $table->integer('skipped_enrollments')->default(0);

            // Results
            $table->json('errors')->nullable();
            $table->json('success_details')->nullable();

            // Status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('course_id');
            $table->index('uploaded_by');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_enrollment_sessions');
    }
};
