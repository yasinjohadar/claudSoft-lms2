<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Batch topics can now carry a full multi-sentence brief (not just a short
     * title), so the 500-char varchar column is replaced with TEXT. Raw SQL is
     * used instead of Blueprint::change() to avoid requiring doctrine/dbal.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite has no fixed-length varchar limit to begin with; nothing to change.
            return;
        }

        DB::statement('ALTER TABLE documentation_ai_batch_items MODIFY topic TEXT NOT NULL');
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE documentation_ai_batch_items MODIFY topic VARCHAR(500) NOT NULL');
    }
};
