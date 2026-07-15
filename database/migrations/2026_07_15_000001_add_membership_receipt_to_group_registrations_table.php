<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_registrations', function (Blueprint $table) {
            $table->string('membership_receipt_path')->nullable()->after('interested_in_bootcamp');
            $table->string('membership_receipt_disk')->nullable()->after('membership_receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('group_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'membership_receipt_path',
                'membership_receipt_disk',
            ]);
        });
    }
};
