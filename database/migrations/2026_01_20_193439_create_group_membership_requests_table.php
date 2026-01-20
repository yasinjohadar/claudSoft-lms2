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
        Schema::create('group_membership_requests', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('group_id')->constrained('course_groups')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');

            // Request Details
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('terms_accepted')->default(false); // الموافقة على شروط المعسكر
            $table->date('payment_date')->nullable(); // متى يمكن تسديد رسوم المعسكر
            $table->text('message')->nullable(); // رسالة اختيارية من الطالب
            $table->text('admin_notes')->nullable(); // ملاحظات الإدارة

            // Approval/Rejection Tracking
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('group_id');
            $table->index('student_id');
            $table->index('status');
            $table->index(['group_id', 'student_id']);

            // Note: A student can have only one pending request per group
            // This is enforced at application level, not database level due to soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_membership_requests');
    }
};
