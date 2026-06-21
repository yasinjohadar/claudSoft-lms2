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
        if (Schema::hasTable('course_group_membership_histories')) {
            return;
        }

        Schema::create('course_group_membership_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('course_groups')->cascadeOnDelete();

            $table->enum('role', ['member', 'leader'])->default('member');

            $table->timestamp('joined_at');
            $table->timestamp('left_at')->nullable();

            $table->text('join_reason')->nullable();
            $table->text('leave_reason')->nullable();

            $table->foreignId('joined_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('removed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('source', 64)->default('system');
            $table->unsignedBigInteger('source_reference_id')->nullable();

            $table->timestamps();

            $table->index(['student_id', 'joined_at']);
            $table->index(['group_id', 'student_id', 'left_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_group_membership_histories');
    }
};
