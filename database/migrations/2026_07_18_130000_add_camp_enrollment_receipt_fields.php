<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_camps', function (Blueprint $table) {
            if (! Schema::hasColumn('training_camps', 'require_payment_receipt')) {
                $table->boolean('require_payment_receipt')
                    ->default(true)
                    ->after('is_featured');
            }
        });

        Schema::table('camp_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('camp_enrollments', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('camp_enrollments', 'receipt_disk')) {
                $table->string('receipt_disk', 64)->nullable()->after('receipt_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('training_camps', function (Blueprint $table) {
            if (Schema::hasColumn('training_camps', 'require_payment_receipt')) {
                $table->dropColumn('require_payment_receipt');
            }
        });

        Schema::table('camp_enrollments', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('camp_enrollments', 'receipt_path')) {
                $columns[] = 'receipt_path';
            }
            if (Schema::hasColumn('camp_enrollments', 'receipt_disk')) {
                $columns[] = 'receipt_disk';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
