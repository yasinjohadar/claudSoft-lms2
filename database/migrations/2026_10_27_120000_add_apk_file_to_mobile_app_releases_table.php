<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mobile_app_releases', function (Blueprint $table) {
            // ملف APK مرفوع من لوحة الأدمن (قرص local الخاص، يُخدَّم عبر /app/download).
            $table->string('apk_path', 500)->nullable()->after('download_url');
            $table->unsignedBigInteger('apk_size')->nullable()->after('apk_path');
            $table->unsignedInteger('download_count')->default(0)->after('apk_size');
        });
    }

    public function down(): void
    {
        Schema::table('mobile_app_releases', function (Blueprint $table) {
            $table->dropColumn(['apk_path', 'apk_size', 'download_count']);
        });
    }
};
