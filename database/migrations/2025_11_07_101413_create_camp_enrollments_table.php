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
        Schema::create('camp_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camp_id')->constrained('training_camps')->onDelete('cascade'); // المعسكر
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade'); // الطالب
            $table->timestamp('enrollment_date')->useCurrent(); // تاريخ التسجيل
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending'); // حالة التسجيل
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid'); // حالة الدفع
            $table->text('notes')->nullable(); // ملاحظات
            $table->timestamps();

            // Indexes for better performance
            $table->index('camp_id');
            $table->index('student_id');
            $table->index('status');
            $table->index('payment_status');

            // Unique constraint to prevent duplicate enrollments
            $table->unique(['camp_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camp_enrollments');
    }
};
