<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\Finance\PaymentWhatsAppNotifyService;
use Illuminate\Support\Facades\DB;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        if ($payment->status !== 'completed') {
            return;
        }

        DB::afterCommit(function () use ($payment) {
            app(PaymentWhatsAppNotifyService::class)->notify($payment->fresh([
                'student',
                'invoice.items.campEnrollment.camp',
                'paymentMethod',
            ]));
        });
    }

    public function updated(Payment $payment): void
    {
        if ($payment->status !== 'completed' || ! $payment->wasChanged('status')) {
            return;
        }

        if ($payment->getOriginal('status') === 'completed') {
            return;
        }

        DB::afterCommit(function () use ($payment) {
            app(PaymentWhatsAppNotifyService::class)->notify($payment->fresh([
                'student',
                'invoice.items.campEnrollment.camp',
                'paymentMethod',
            ]));
        });
    }
}
