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
        Schema::table('gamification_shop_items', function (Blueprint $table) {
            $table->boolean('in_stock')->default(true)->after('is_active');
            $table->renameColumn('stock', 'stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gamification_shop_items', function (Blueprint $table) {
            $table->dropColumn('in_stock');
            $table->renameColumn('stock_quantity', 'stock');
        });
    }
};
