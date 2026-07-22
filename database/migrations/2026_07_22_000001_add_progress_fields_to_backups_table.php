<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress')->default(0)->after('status');
            $table->string('stage', 50)->nullable()->after('progress');
            $table->unsignedBigInteger('bytes_processed')->nullable()->after('stage');
            $table->unsignedBigInteger('bytes_total')->nullable()->after('bytes_processed');
        });
    }

    public function down(): void
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn(['progress', 'stage', 'bytes_processed', 'bytes_total']);
        });
    }
};
