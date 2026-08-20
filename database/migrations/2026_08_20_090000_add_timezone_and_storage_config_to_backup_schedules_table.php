<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backup_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('backup_schedules', 'timezone')) {
                // فارغ = استخدم config('app.timezone') — فلا تتأثر الجدولات الحالية
                $table->string('timezone', 64)->nullable()->after('time');
            }

            if (! Schema::hasColumn('backup_schedules', 'storage_config_id')) {
                // الوجهة الفعلية. كان يُخزَّن اسم السائق فقط في storage_drivers،
                // فمع وجود أكثر من إعداد بنفس السائق كان يُختار الخطأ.
                $table->foreignId('storage_config_id')
                    ->nullable()
                    ->after('storage_drivers')
                    ->constrained('app_storage_configs')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('backup_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('backup_schedules', 'storage_config_id')) {
                $table->dropConstrainedForeignId('storage_config_id');
            }

            if (Schema::hasColumn('backup_schedules', 'timezone')) {
                $table->dropColumn('timezone');
            }
        });
    }
};
