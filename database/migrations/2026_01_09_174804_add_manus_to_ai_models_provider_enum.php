<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // في MySQL، تعديل enum يتطلب تغيير نوع العمود بالكامل
        DB::statement("ALTER TABLE ai_models MODIFY COLUMN provider ENUM('openai', 'anthropic', 'google', 'openrouter', 'zai', 'local', 'custom', 'manus') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إرجاع enum إلى حالته السابقة (بدون manus)
        DB::statement("ALTER TABLE ai_models MODIFY COLUMN provider ENUM('openai', 'anthropic', 'google', 'openrouter', 'zai', 'local', 'custom') NOT NULL");
    }
};
