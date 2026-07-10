<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_weekly_reports', function (Blueprint $table) {
            $table->text('report_description')->nullable()->after('report_title');
        });
    }

    public function down(): void
    {
        Schema::table('student_weekly_reports', function (Blueprint $table) {
            $table->dropColumn('report_description');
        });
    }
};
