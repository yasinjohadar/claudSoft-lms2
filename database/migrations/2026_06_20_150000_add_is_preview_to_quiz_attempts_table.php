<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->boolean('is_preview')->default(false)->after('is_completed');
            $table->index(['quiz_id', 'is_preview', 'student_id'], 'qa_quiz_preview_student_idx');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('qa_quiz_preview_student_idx');
            $table->dropColumn('is_preview');
        });
    }
};
