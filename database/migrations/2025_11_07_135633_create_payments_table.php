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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique()->comment('رقم الدفعة');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade')->comment('الفاتورة');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade')->comment('الطالب');
            $table->decimal('amount', 10, 2)->comment('المبلغ المدفوع');
            $table->foreignId('payment_method_id')->constrained('payment_methods')->onDelete('restrict')->comment('طريقة الدفع');
            $table->dateTime('payment_date')->comment('تاريخ ووقت الدفع');
            $table->string('transaction_id')->nullable()->comment('رقم العملية/الإيصال');
            $table->string('receipt_number')->nullable()->comment('رقم وصل الاستلام');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('completed')->comment('حالة الدفع');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null')->comment('المستلم');
            $table->string('reference')->nullable()->comment('مرجع إضافي');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('payment_number');
            $table->index('invoice_id');
            $table->index('student_id');
            $table->index('payment_method_id');
            $table->index('payment_date');
            $table->index('status');
            $table->index('received_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
