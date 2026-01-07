<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            // طريقة عرض الرابط: embedded داخل الصفحة أو external في تبويب جديد
            $table->string('display_mode', 20)
                ->default('external')
                ->after('resource_url')
                ->comment('embedded, external');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn('display_mode');
        });
    }
};
