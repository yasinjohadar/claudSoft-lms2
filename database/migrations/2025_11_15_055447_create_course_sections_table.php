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
        Schema::create('course_sections', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');

            // Basic Information
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('summary')->nullable();

            // Ordering
            $table->integer('sort_order')->default(0);

            // Visibility & Lock
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_locked')->default(false);
            $table->boolean('show_unavailable')->default(true);

            // Unlock Conditions (JSON)
            $table->json('unlock_conditions')->nullable();

            // Date Settings
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('course_id');
            $table->index('sort_order');
            $table->index('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_sections');
    }
};
