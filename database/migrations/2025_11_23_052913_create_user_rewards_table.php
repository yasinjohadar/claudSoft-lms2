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
        Schema::create('user_rewards', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reward_id')->constrained('rewards_catalog', 'id')->onDelete('cascade');

            // Purchase Info
            $table->timestamp('purchased_at')->useCurrent()->comment('تاريخ الشراء');
            $table->integer('points_spent')->unsigned()->default(0)->comment('النقاط المصروفة');

            // Status
            $table->enum('status', ['pending', 'processing', 'delivered', 'claimed', 'expired', 'cancelled'])
                  ->default('pending')
                  ->comment('الحالة');

            // Delivery
            $table->text('delivery_code')->nullable()->comment('كود التسليم');
            $table->text('delivery_details')->nullable()->comment('تفاصيل التسليم');
            $table->timestamp('delivered_at')->nullable()->comment('تاريخ التسليم');
            $table->timestamp('claimed_at')->nullable()->comment('تاريخ الاستلام');

            // Validity
            $table->timestamp('expires_at')->nullable()->comment('تاريخ انتهاء الصلاحية');
            $table->boolean('is_expired')->default(false)->comment('منتهي؟');

            // Transaction Reference
            $table->foreignId('transaction_id')->nullable()->constrained('points_transactions')->onDelete('set null');

            // Admin Actions
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->comment('تاريخ الموافقة');
            $table->text('admin_notes')->nullable()->comment('ملاحظات الإدارة');

            // Metadata
            $table->json('metadata')->nullable()->comment('بيانات إضافية');

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('reward_id');
            $table->index('status');
            $table->index('purchased_at');
            $table->index('expires_at');
            $table->index('is_expired');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rewards');
    }
};
