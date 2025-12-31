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
        Schema::table('quiz_responses', function (Blueprint $table) {
            $table->boolean('ai_graded')->default(false)->after('auto_graded')->comment('Was this graded by AI?');
            $table->foreignId('ai_request_id')->nullable()->after('ai_graded')->constrained('ai_requests')->onDelete('set null')->comment('Reference to AI request that graded this');
            $table->json('ai_feedback')->nullable()->after('ai_request_id')->comment('AI-generated feedback');
            $table->json('ai_grading_details')->nullable()->after('ai_feedback')->comment('Detailed AI grading information');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_responses', function (Blueprint $table) {
            $table->dropForeign(['ai_request_id']);
            $table->dropColumn(['ai_graded', 'ai_request_id', 'ai_feedback', 'ai_grading_details']);
        });
    }
};
