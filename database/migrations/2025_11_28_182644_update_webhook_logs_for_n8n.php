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
        Schema::table('webhook_logs', function (Blueprint $table) {
            // التأكد من أن source يدعم مصادر متعددة
            $table->string('source', 50)->default('wpforms')->change();

            // إضافة فهرس إذا لم يكن موجوداً
            if (!Schema::hasIndex('webhook_logs', ['source', 'status'])) {
                $table->index(['source', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('webhook_logs', function (Blueprint $table) {
            // إزالة الفهرس المضاف
            $table->dropIndex(['source', 'status']);
        });
    }
};
