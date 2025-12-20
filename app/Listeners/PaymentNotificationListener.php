<?php

namespace App\Listeners;

use App\Events\InvoiceCreated;
use App\Events\PaymentReceived;
use App\Services\Gamification\NotificationService;
use Illuminate\Support\Facades\Log;

class PaymentNotificationListener
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle InvoiceCreated event
     */
    public function handleInvoiceCreated(InvoiceCreated $event): void
    {
        try {
            $user = $event->user;
            $invoice = $event->invoice;

            // إرسال إشعار فاتورة جديدة
            $currency = $invoice->currency ?? 'ريال';
            $this->notificationService->send(
                user: $user,
                type: 'invoice_created',
                title: 'فاتورة جديدة 🧾',
                message: "تم إنشاء فاتورة جديدة بقيمة {$invoice->total_amount} {$currency}. يرجى الدفع قبل تاريخ الاستحقاق.",
                icon: '💳',
                actionUrl: route('student.invoices.show', ['id' => $invoice->id]),
                relatedType: get_class($invoice),
                relatedId: $invoice->id,
                metadata: [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'total_amount' => $invoice->total_amount,
                    'currency' => $invoice->currency ?? 'SAR',
                    'due_date' => $invoice->due_date?->toDateString(),
                    'status' => $invoice->status,
                ]
            );

            Log::info('Invoice creation notification sent', [
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send invoice creation notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
                'invoice_id' => $event->invoice->id ?? null,
            ]);
        }
    }

    /**
     * Handle PaymentReceived event
     */
    public function handlePaymentReceived(PaymentReceived $event): void
    {
        try {
            $user = $event->user;
            $payment = $event->payment;

            // إرسال إشعار استلام الدفع
            $currency = $payment->currency ?? 'ريال';
            $this->notificationService->send(
                user: $user,
                type: 'payment_received',
                title: 'تم استلام الدفع بنجاح! ✅',
                message: "تم تأكيد دفعتك بقيمة {$payment->amount} {$currency} بنجاح. شكراً لك!",
                icon: '💰',
                actionUrl: route('student.payments.show', ['id' => $payment->id]),
                relatedType: get_class($payment),
                relatedId: $payment->id,
                metadata: [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency ?? 'SAR',
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id,
                    'paid_at' => $payment->paid_at?->toDateTimeString(),
                ]
            );

            Log::info('Payment received notification sent', [
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send payment received notification', [
                'error' => $e->getMessage(),
                'user_id' => $event->user->id ?? null,
                'payment_id' => $event->payment->id ?? null,
            ]);
        }
    }

    /**
     * Register the listeners for the subscriber
     */
    public function subscribe($events): array
    {
        return [
            InvoiceCreated::class => 'handleInvoiceCreated',
            PaymentReceived::class => 'handlePaymentReceived',
        ];
    }
}
