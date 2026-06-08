<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('reference')->comment('مسار إيصال الدفع');
            $table->string('receipt_disk')->nullable()->after('receipt_path')->comment('قرص التخزين');
            $table->foreignId('reviewed_by')->nullable()->after('received_by')->constrained('users')->nullOnDelete()->comment('مراجع الطلب');
            $table->dateTime('reviewed_at')->nullable()->after('reviewed_by')->comment('تاريخ المراجعة');
            $table->text('rejection_reason')->nullable()->after('reviewed_at')->comment('سبب الرفض');

            $table->index('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'receipt_path',
                'receipt_disk',
                'reviewed_by',
                'reviewed_at',
                'rejection_reason',
            ]);
        });
    }
};
