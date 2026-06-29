<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_question_generations', function (Blueprint $table) {
            $table->string('lesson_name')->nullable()->after('lesson_id');
            $table->foreignId('programming_language_id')
                ->nullable()
                ->after('lesson_name')
                ->constrained('programming_languages')
                ->nullOnDelete();
            $table->json('question_type_ids')->nullable()->after('question_type');
            $table->decimal('default_grade', 8, 2)->default(1)->after('difficulty_level');
            $table->json('saved_indices')->nullable()->after('generated_questions');
            $table->json('saved_question_ids')->nullable()->after('saved_indices');
        });
    }

    public function down(): void
    {
        Schema::table('ai_question_generations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('programming_language_id');
            $table->dropColumn([
                'lesson_name',
                'question_type_ids',
                'default_grade',
                'saved_indices',
                'saved_question_ids',
            ]);
        });
    }
};
