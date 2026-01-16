<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\CampEnrollment;

class UpdateCampEnrollmentPaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'camp-enrollments:update-payment-status 
                            {--dry-run : Run without making changes to see what would be updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update payment status of camp enrollments based on their related paid invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('بدء تحديث حالة الدفع في المعسكرات...');
        $this->newLine();

        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('وضع التجربة - لن يتم إجراء أي تغييرات');
            $this->newLine();
        }

        // Get all paid invoices
        $paidInvoices = Invoice::where('status', 'paid')
            ->where('remaining_amount', '<=', 0)
            ->with('items')
            ->get();

        $this->info("تم العثور على {$paidInvoices->count()} فاتورة مدفوعة");
        $this->newLine();

        $updatedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        $progressBar = $this->output->createProgressBar($paidInvoices->count());
        $progressBar->start();

        foreach ($paidInvoices as $invoice) {
            try {
                $invoiceItems = $invoice->items()->whereNotNull('camp_enrollment_id')->get();
                
                if ($invoiceItems->isEmpty()) {
                    $skippedCount++;
                    $progressBar->advance();
                    continue;
                }

                foreach ($invoiceItems as $item) {
                    if ($item->camp_enrollment_id) {
                        $enrollment = CampEnrollment::find($item->camp_enrollment_id);
                        
                        if ($enrollment) {
                            // Check if update is needed
                            if ($enrollment->payment_status !== 'paid') {
                                if (!$dryRun) {
                                    $enrollment->payment_status = 'paid';
                                    $enrollment->save();
                                }
                                $updatedCount++;
                            } else {
                                $skippedCount++;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("خطأ في معالجة الفاتورة {$invoice->invoice_number}: " . $e->getMessage());
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info('ملخص التحديث:');
        $this->table(
            ['العنصر', 'العدد'],
            [
                ['المعسكرات المحدثة', $updatedCount],
                ['المعسكرات التي تم تخطيها (مدفوعة بالفعل)', $skippedCount],
                ['الأخطاء', $errorCount],
                ['إجمالي الفواتير المعالجة', $paidInvoices->count()],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('تم تشغيل الأمر في وضع التجربة. لإجراء التحديثات الفعلية، قم بتشغيل الأمر بدون --dry-run');
        } else {
            $this->newLine();
            $this->info('تم تحديث حالة الدفع بنجاح!');
        }

        return Command::SUCCESS;
    }
}
