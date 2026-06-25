<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programming_challenge_submission_files')) {
            return;
        }

        Schema::create('programming_challenge_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_challenge_submission_id')->constrained('programming_challenge_submissions', 'id', 'pc_sub_files_sub_fk')->cascadeOnDelete();
            $table->foreignId('programming_language_id')->nullable()->constrained('programming_languages', 'id', 'pc_sub_files_lang_fk')->nullOnDelete();
            $table->string('file_role', 30);
            $table->string('filename')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
            $table->index('programming_challenge_submission_id', 'pc_sub_file_submission_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenge_submission_files');
    }
};