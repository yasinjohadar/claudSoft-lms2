<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programming_challenge_language')) {
            return;
        }

        Schema::create('programming_challenge_language', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programming_challenge_id')->constrained('programming_challenges', 'id', 'pc_lang_challenge_fk')->cascadeOnDelete();
            $table->foreignId('programming_language_id')->constrained('programming_languages', 'id', 'pc_lang_language_fk')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->string('editor_tab_label')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['programming_challenge_id', 'programming_language_id'], 'challenge_language_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programming_challenge_language');
    }
};