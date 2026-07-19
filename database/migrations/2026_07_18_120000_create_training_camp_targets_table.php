<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_camp_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_camp_id')
                ->constrained('training_camps')
                ->cascadeOnDelete();
            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();
            $table->foreignId('group_id')
                ->constrained('course_groups')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(
                ['training_camp_id', 'course_id', 'group_id'],
                'camp_targets_unique'
            );
            $table->index(['group_id'], 'camp_targets_group_idx');
            $table->index(['course_id', 'group_id'], 'camp_targets_course_group_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_camp_targets');
    }
};
