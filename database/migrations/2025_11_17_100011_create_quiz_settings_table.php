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
        Schema::create('quiz_settings', function (Blueprint $table) {
            $table->id();

            // Relationship
            $table->foreignId('quiz_id')->unique()->constrained('quizzes')->onDelete('cascade');

            // Security
            $table->boolean('require_password')->default(false);
            $table->string('quiz_password')->nullable();
            $table->enum('browser_security', ['none', 'popup', 'fullscreen', 'safe_exam_browser'])->default('none');

            // Navigation
            $table->boolean('allow_navigation')->default(true)->comment('Allow going back to previous questions');
            $table->enum('navigation_method', ['sequential', 'free'])->default('free');
            $table->boolean('show_question_numbers')->default(true);
            $table->integer('questions_per_page')->default(1);

            // Display Options
            $table->boolean('show_timer')->default(true);
            $table->boolean('auto_submit')->default(true)->comment('Auto-submit when time runs out');
            $table->boolean('allow_pause')->default(false);
            $table->boolean('show_progress_bar')->default(true);

            // Tools
            $table->boolean('enable_calculator')->default(false);
            $table->integer('decimal_places')->default(2)->comment('For numerical questions');

            $table->timestamps();

            // Index
            $table->index('quiz_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_settings');
    }
};
