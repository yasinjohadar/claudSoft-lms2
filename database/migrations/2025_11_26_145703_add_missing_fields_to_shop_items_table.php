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
            $table->integer('discount_percentage')->unsigned()->nullable()->after('price_gems');
            $table->timestamp('discount_expires_at')->nullable()->after('discount_percentage');
            $table->integer('purchase_limit')->unsigned()->nullable()->after('required_level');
            $table->unsignedBigInteger('required_badge_id')->nullable()->after('purchase_limit');
            $table->integer('sort_order')->unsigned()->default(0)->after('required_badge_id');
            $table->boolean('is_featured')->default(false)->after('in_stock');
            $table->integer('total_purchases')->unsigned()->default(0)->after('is_featured');
            $table->timestamp('last_purchased_at')->nullable()->after('total_purchases');

            $table->index('sort_order');
            $table->index('is_featured');
            $table->index('discount_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gamification_shop_items', function (Blueprint $table) {
            $table->dropColumn([
                'discount_percentage',
                'discount_expires_at',
                'purchase_limit',
                'required_badge_id',
                'sort_order',
                'is_featured',
                'total_purchases',
                'last_purchased_at',
            ]);
        });
    }
};
