<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>إيصال {{ $payment->payment_number }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; padding: 16px; color: #222; }
        h1 { font-size: 1.25rem; }
        .meta { margin: 8px 0; font-size: 14px; }
    </style>
</head>
<body>
    <h1>إيصال دفع {{ $payment->payment_number }}</h1>
    <div class="meta">
        <div>المبلغ: {{ number_format((float) $payment->amount, 2) }}</div>
        <div>التاريخ: {{ $payment->payment_date?->format('Y-m-d H:i') }}</div>
        @if($payment->paymentMethod)
            <div>طريقة الدفع: {{ $payment->paymentMethod->name }}</div>
        @endif
        @if($payment->invoice)
            <div>الفاتورة: {{ $payment->invoice->invoice_number }}</div>
        @endif
        @if($payment->transaction_id)
            <div>رقم العملية: {{ $payment->transaction_id }}</div>
        @endif
    </div>
</body>
</html>
