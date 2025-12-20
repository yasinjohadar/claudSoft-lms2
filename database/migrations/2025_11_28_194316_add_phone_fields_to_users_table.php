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
        Schema::table('users', function (Blueprint $table) {
            // تحقق من وجود العمود قبل الإضافة
            if (!Schema::hasColumn('users', 'country_code')) {
                $table->string('country_code', 5)->nullable()->after('email'); // مثل: +966
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('country_code'); // رقم الهاتف بدون الرمز
            }

            if (!Schema::hasColumn('users', 'full_phone')) {
                $table->string('full_phone', 25)->nullable()->after('phone')->index(); // الرقم الكامل بصيغة دولية
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'phone', 'full_phone']);
        });
    }
};
