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
        Schema::create('course_groups', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // Basic Information
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();

            // Settings
            $table->integer('max_members')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_active')->default(true);

            // Auditing
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('course_id');
            $table->index('is_visible');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_groups');
    }
};
