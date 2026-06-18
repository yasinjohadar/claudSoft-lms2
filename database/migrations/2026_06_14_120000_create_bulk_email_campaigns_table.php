<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bulk_email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_setting_id')->nullable()->constrained('email_settings')->nullOnDelete();
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->enum('content_mode', ['template', 'custom']);
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->enum('audience_type', ['individual', 'selected', 'group', 'course', 'course_group']);
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('course_groups')->nullOnDelete();
            $table->json('student_ids')->nullable();
            $table->unsignedInteger('total_recipients')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('created_by');
            $table->index(['audience_type', 'course_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bulk_email_campaigns');
    }
};
