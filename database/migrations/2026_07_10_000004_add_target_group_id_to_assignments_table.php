<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->foreignId('target_group_id')
                ->nullable()
                ->after('course_id')
                ->constrained('course_groups')
                ->nullOnDelete();

            $table->index(['course_id', 'target_group_id'], 'assignments_course_group_idx');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropIndex('assignments_course_group_idx');
            $table->dropConstrainedForeignId('target_group_id');
        });
    }
};
