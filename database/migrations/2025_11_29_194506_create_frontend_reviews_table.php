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
        Schema::create('frontend_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('frontend_course_id')->nullable()->constrained('frontend_courses')->onDelete('cascade');
            $table->string('student_name');
            $table->string('student_email')->nullable();
            $table->string('student_image')->nullable();
            $table->string('student_position')->nullable(); // e.g., "مطور ويب", "طالب"
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            $table->text('review_text');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_reviews');
    }
};
