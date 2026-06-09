<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\User;

class StudentDueInvoicesAlertService
{
    /**
     * @return array{
     *     count: int,
     *     total_remaining: float,
     *     dismiss_key: string,
     *     invoices_url: string
     * }|null
     */
    public function forUser(User $user): ?array
    {
        $summary = Invoice::query()
            ->where('student_id', $user->id)
            ->unpaid()
            ->where('remaining_amount', '>', 0)
            ->selectRaw('COUNT(*) as invoice_count, COALESCE(SUM(remaining_amount), 0) as total_remaining')
            ->first();

        $count = (int) ($summary->invoice_count ?? 0);

        if ($count === 0) {
            return null;
        }

        $totalRemaining = round((float) ($summary->total_remaining ?? 0), 2);

        return [
            'count' => $count,
            'total_remaining' => $totalRemaining,
            'dismiss_key' => sprintf(
                'due-invoices-alert-%s-%d-%s',
                $user->id,
                $count,
                str_replace('.', '_', (string) $totalRemaining)
            ),
            'invoices_url' => route('student.invoices.index'),
        ];
    }
}
