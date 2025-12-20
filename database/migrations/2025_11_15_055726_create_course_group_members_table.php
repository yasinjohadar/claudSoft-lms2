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
        Schema::create('course_group_members', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('group_id')->constrained('course_groups')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');

            // Role
            $table->enum('role', ['member', 'leader'])->default('member');

            // Dates
            $table->timestamp('joined_at')->useCurrent();

            $table->timestamps();

            // Unique Constraint
            $table->unique(['group_id', 'student_id']);

            // Indexes
            $table->index('group_id');
            $table->index('student_id');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_group_members');
    }
};
