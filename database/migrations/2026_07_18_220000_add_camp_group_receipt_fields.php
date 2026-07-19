<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('course_groups', 'require_payment_receipt')) {
                $table->boolean('require_payment_receipt')
                    ->default(true)
                    ->after('end_date');
            }
        });

        Schema::table('group_membership_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('group_membership_requests', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('message');
                $table->string('receipt_disk', 64)->nullable()->after('receipt_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_groups', function (Blueprint $table) {
            if (Schema::hasColumn('course_groups', 'require_payment_receipt')) {
                $table->dropColumn('require_payment_receipt');
            }
        });

        Schema::table('group_membership_requests', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('group_membership_requests', 'receipt_path')) {
                $columns[] = 'receipt_path';
            }
            if (Schema::hasColumn('group_membership_requests', 'receipt_disk')) {
                $columns[] = 'receipt_disk';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
