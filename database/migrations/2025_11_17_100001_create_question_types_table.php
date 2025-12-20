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
        Schema::create('question_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Technical name: multiple_choice_single, etc.');
            $table->string('display_name')->comment('Display name in Arabic');
            $table->boolean('requires_manual_grading')->default(false)->comment('Needs manual grading?');
            $table->boolean('supports_auto_grading')->default(true)->comment('Supports auto-grading?');
            $table->string('icon')->nullable()->comment('Font Awesome icon class');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_types');
    }
};
