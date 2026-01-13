<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // توسيع enum لمزود الموديلات ليشمل groq بالإضافة إلى المزودات الحالية
        DB::statement("
            ALTER TABLE ai_models 
            MODIFY COLUMN provider 
            ENUM('openai', 'anthropic', 'google', 'openrouter', 'zai', 'local', 'custom', 'manus', 'groq') 
            NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إعادة enum بدون groq (مع بقاء manus كما هو)
        DB::statement("
            ALTER TABLE ai_models 
            MODIFY COLUMN provider 
            ENUM('openai', 'anthropic', 'google', 'openrouter', 'zai', 'local', 'custom', 'manus') 
            NOT NULL
        ");
    }
};


