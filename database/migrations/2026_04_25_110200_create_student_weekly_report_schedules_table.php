<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_weekly_report_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('weekday')->default(0); // 0=Sunday
            $table->time('due_time')->default('23:00:00');
            $table->string('target_scope')->default('all_students'); // all_students|specific_students
            $table->json('target_student_ids')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'next_run_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_weekly_report_schedules');
    }
};

