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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->comment('رقم الفاتورة');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade')->comment('الطالب');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('المبلغ الإجمالي');
            $table->decimal('paid_amount', 10, 2)->default(0)->comment('المبلغ المدفوع');
            $table->decimal('remaining_amount', 10, 2)->default(0)->comment('المبلغ المتبقي');
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('الضريبة');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('الخصم');
            $table->enum('status', ['draft', 'issued', 'partial', 'paid', 'cancelled', 'refunded'])->default('draft')->comment('حالة الفاتورة');
            $table->date('issue_date')->comment('تاريخ الإصدار');
            $table->date('due_date')->nullable()->comment('تاريخ الاستحقاق');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('أنشئت بواسطة');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('invoice_number');
            $table->index('student_id');
            $table->index('status');
            $table->index('issue_date');
            $table->index('due_date');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
