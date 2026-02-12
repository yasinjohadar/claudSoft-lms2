<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إضافة عداد الدروس المكتملة لدعم منح الشارات (مثل متعلم نشط، عاشق التعلم، مدمن المعرفة).
     */
    public function up(): void
    {
        Schema::table('user_stats', function (Blueprint $table) {
            $table->unsignedInteger('lessons_completed')->default(0)->after('assignments_submitted')
                ->comment('عدد الدروس/الوحدات المكتملة');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_stats', function (Blueprint $table) {
            $table->dropColumn('lessons_completed');
        });
    }
};
