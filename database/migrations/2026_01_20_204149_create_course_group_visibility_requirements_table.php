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
        Schema::create('course_group_visibility_requirements', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('group_id')->constrained('course_groups')->onDelete('cascade');
            $table->foreignId('required_group_id')->constrained('course_groups')->onDelete('cascade');

            $table->timestamps();

            // Unique constraint: prevent duplicate requirements
            // Use a shorter index name to avoid MySQL 64 character limit
            $table->unique(['group_id', 'required_group_id'], 'cgvr_group_required_unique');

            // Indexes
            $table->index('group_id');
            $table->index('required_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_group_visibility_requirements');
    }
};
