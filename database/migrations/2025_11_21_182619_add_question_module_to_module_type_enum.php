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
        DB::statement("ALTER TABLE course_modules MODIFY COLUMN module_type ENUM('lesson','video','quiz','programming_challenge','assignment','resource','forum','live_session','question_module') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE course_modules MODIFY COLUMN module_type ENUM('lesson','video','quiz','programming_challenge','assignment','resource','forum','live_session') NOT NULL");
    }
};
