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
        Schema::create('gamification_user_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shop_item_id')->constrained('gamification_shop_items')->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained('gamification_user_purchases')->onDelete('set null');
            $table->integer('quantity')->unsigned()->default(1);
            $table->string('status')->default('owned'); // owned, active, consumed, expired
            $table->boolean('is_active')->default(false);
            $table->timestamp('acquired_at');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['shop_item_id']);
            $table->index(['is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gamification_user_inventory');
    }
};
