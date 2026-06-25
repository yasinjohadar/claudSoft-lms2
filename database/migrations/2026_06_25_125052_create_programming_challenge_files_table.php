<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programming_challenge_files')) {
            return;
        }

        Schema::create('programming_challenge_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_challenge_id')->constrained('programming_challenges', 'id', 'pc_files_challenge_fk')->cascadeOnDelete();
            $table->foreignId('programming_language_id')->nullable()->constrained('programming_languages', 'id', 'pc_files_language_fk')->nullOnDelete();
            $table->enum('file_role', ['html', 'css', 'js', 'solution', 'starter'])->default('starter');
            $table->string('filename');
            $table->longText('content')->nullable();
            $table->boolean('is_readonly')->default(false);
            $table->timestamps();
            $table->index(['programming_challenge_id', 'file_role'], 'pc_files_challenge_role_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenge_files');
    }
};