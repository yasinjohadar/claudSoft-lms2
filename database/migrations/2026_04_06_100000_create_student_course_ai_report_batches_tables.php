<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_course_ai_report_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete()->name('scarb_course_fk');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->name('scarb_creator_fk');
            $table->string('attempt_strategy', 16);
            $table->date('since')->nullable();
            $table->foreignId('laravel_ai_model_id')->nullable()->constrained('laravel_ai_models')->nullOnDelete()->name('scarb_laravel_ai_fk');
            $table->string('scope', 32);
            $table->foreignId('course_group_id')->nullable()->constrained('course_groups')->nullOnDelete()->name('scarb_group_fk');
            $table->string('status', 32)->default('running');
            $table->unsignedInteger('items_total')->default(0);
            $table->unsignedInteger('items_succeeded')->default(0);
            $table->unsignedInteger('items_failed')->default(0);
            $table->unsignedInteger('items_skipped')->default(0);
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'created_at']);
        });

        Schema::create('student_course_ai_report_batch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('student_course_ai_report_batches')->cascadeOnDelete()->name('scarbi_batch_fk');
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete()->name('scarbi_student_fk');
            $table->foreignId('course_group_id')->constrained('course_groups')->cascadeOnDelete()->name('scarbi_cgroup_fk');
            $table->string('status', 32)->default('queued');
            $table->foreignId('student_course_ai_report_id')->nullable()->constrained('student_course_ai_reports')->nullOnDelete()->name('scarbi_report_fk');
            $table->text('error_message')->nullable();
            $table->json('narrative_segments')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_course_ai_report_batch_items');
        Schema::dropIfExists('student_course_ai_report_batches');
    }
};
