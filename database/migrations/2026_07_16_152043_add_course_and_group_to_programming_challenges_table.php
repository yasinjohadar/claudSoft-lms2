<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programming_challenges', function (Blueprint $table) {
            $table->foreignId('course_id')
                ->nullable()
                ->after('is_standalone')
                ->constrained('courses')
                ->nullOnDelete();

            $table->foreignId('target_group_id')
                ->nullable()
                ->after('course_id')
                ->constrained('course_groups')
                ->nullOnDelete();

            $table->index(['course_id', 'target_group_id']);
        });
    }

    public function down(): void
    {
        Schema::table('programming_challenges', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->dropForeign(['target_group_id']);
            $table->dropIndex(['course_id', 'target_group_id']);
            $table->dropColumn(['course_id', 'target_group_id']);
        });
    }
};
