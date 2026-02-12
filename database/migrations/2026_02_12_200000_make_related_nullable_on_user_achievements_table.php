<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * جعل related_type و related_id قابلة للإلغاء لتجنب خطأ عند إنشاء سجل إنجاز دون ربط بكيان معين.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE user_achievements MODIFY related_type VARCHAR(255) NULL');
            DB::statement('ALTER TABLE user_achievements MODIFY related_id BIGINT UNSIGNED NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE user_achievements ALTER COLUMN related_type DROP NOT NULL');
            DB::statement('ALTER TABLE user_achievements ALTER COLUMN related_id DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE user_achievements MODIFY related_type VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE user_achievements MODIFY related_id BIGINT UNSIGNED NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE user_achievements ALTER COLUMN related_type SET NOT NULL');
            DB::statement('ALTER TABLE user_achievements ALTER COLUMN related_id SET NOT NULL');
        }
    }
};
