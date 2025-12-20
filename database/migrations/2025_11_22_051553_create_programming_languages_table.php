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
        Schema::create('programming_languages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Laravel, React, Python, etc.
            $table->string('slug')->unique();
            $table->string('display_name'); // الاسم بالعربي
            $table->text('description')->nullable();
            $table->string('category'); // frontend, backend, mobile, database, ai, devops, design
            $table->string('icon')->nullable(); // Font Awesome icon class
            $table->string('color')->nullable(); // Hex color for UI
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programming_languages');
    }
};
