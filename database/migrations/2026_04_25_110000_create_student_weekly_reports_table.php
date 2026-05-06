<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_weekly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_title');
            $table->text('student_details')->nullable();
            $table->text('student_notes')->nullable();
            $table->text('admin_feedback')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->string('status')->default('draft'); // draft|submitted|reviewed|closed
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_weekly_reports');
    }
};

