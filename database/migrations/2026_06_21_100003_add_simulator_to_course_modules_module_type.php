<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE course_modules MODIFY COLUMN module_type ENUM('lesson','video','quiz','programming_challenge','assignment','resource','forum','live_session','question_module','documentation','simulator') NOT NULL");
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE course_modules MODIFY COLUMN module_type ENUM('lesson','video','quiz','programming_challenge','assignment','resource','forum','live_session','question_module','documentation') NOT NULL");
    }
};
