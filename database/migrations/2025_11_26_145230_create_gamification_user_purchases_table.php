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
        Schema::create('gamification_user_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_item_id')->constrained('gamification_shop_items')->onDelete('cascade');
            $table->string('payment_method')->default('points'); // points, gems
            $table->integer('original_price')->unsigned();
            $table->integer('discount_percentage')->unsigned()->default(0);
            $table->integer('final_price')->unsigned();
            $table->timestamp('purchased_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purchased_at']);
            $table->index(['shop_item_id']);
            $table->index(['payment_method']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_user_purchases');
    }
};
