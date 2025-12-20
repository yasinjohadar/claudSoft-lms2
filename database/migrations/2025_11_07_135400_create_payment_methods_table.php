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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم طريقة الدفع بالعربي');
            $table->string('name_en')->nullable()->comment('اسم طريقة الدفع بالإنجليزي');
            $table->text('description')->nullable()->comment('وصف طريقة الدفع');
            $table->boolean('is_active')->default(true)->comment('نشط/معطل');
            $table->boolean('requires_transaction_id')->default(false)->comment('يتطلب رقم عملية');
            $table->integer('order')->default(0)->comment('الترتيب');
            $table->timestamps();

            // Indexes
            $table->index('is_active');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
