<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            if (! Schema::hasColumn('user_devices', 'trusted_at')) {
                $table->timestamp('trusted_at')->nullable()->after('is_trusted');
            }
        });

        DB::table('user_devices')
            ->where('is_trusted', true)
            ->whereNull('trusted_at')
            ->update([
                'trusted_at' => DB::raw('COALESCE(updated_at, CURRENT_TIMESTAMP)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('user_devices', function (Blueprint $table) {
            if (Schema::hasColumn('user_devices', 'trusted_at')) {
                $table->dropColumn('trusted_at');
            }
        });
    }
};
