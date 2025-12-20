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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade')->comment('الفاتورة');
            $table->string('itemable_type')->nullable()->comment('نوع البند (TrainingCamp)');
            $table->unsignedBigInteger('itemable_id')->nullable()->comment('معرف البند');
            $table->string('description')->comment('وصف البند');
            $table->integer('quantity')->default(1)->comment('الكمية');
            $table->decimal('unit_price', 10, 2)->comment('سعر الوحدة');
            $table->decimal('total_price', 10, 2)->comment('الإجمالي');
            $table->foreignId('camp_enrollment_id')->nullable()->constrained('camp_enrollments')->onDelete('set null')->comment('التسجيل في المعسكر');
            $table->timestamps();

            // Indexes
            $table->index('invoice_id');
            $table->index(['itemable_type', 'itemable_id']);
            $table->index('camp_enrollment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
