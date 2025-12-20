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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique(); // CERT-2025-00001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('certificate_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('student_name');
            $table->string('course_name');
            $table->string('course_name_en')->nullable();
            $table->date('issue_date');
            $table->date('completion_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('completion_percentage', 5, 2)->nullable();
            $table->decimal('attendance_percentage', 5, 2)->nullable();
            $table->decimal('final_exam_score', 5, 2)->nullable();
            $table->integer('course_hours')->nullable();
            $table->string('verification_code')->unique(); // For QR code
            $table->string('pdf_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->text('revocation_reason')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('certificate_number');
            $table->index('verification_code');
            $table->index(['user_id', 'course_id']);
            $table->index('issue_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
