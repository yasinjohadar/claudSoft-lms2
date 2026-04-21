<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wapi_automation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->index();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('wapi_template_id')->nullable()->constrained('wapi_templates')->nullOnDelete();
            $table->string('template_name', 255)->nullable();
            $table->string('language', 32)->nullable();
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('course_groups')->cascadeOnDelete();
            $table->foreignId('module_id')->nullable()->constrained('course_modules')->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained('lessons')->cascadeOnDelete();
            $table->json('header_variables')->nullable();
            $table->json('body_variables')->nullable();
            $table->unsignedInteger('cooldown_seconds')->default(0);
            $table->string('dedupe_template', 512)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wapi_automation_rules');
    }
};
