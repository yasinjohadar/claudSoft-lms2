<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; padding: 16px; color: #222; }
        h1 { font-size: 1.25rem; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: right; font-size: 14px; }
        th { background: #f5f5f5; }
        .meta { margin: 8px 0; font-size: 14px; }
    </style>
</head>
<body>
    <h1>فاتورة {{ $invoice->invoice_number }}</h1>
    <div class="meta">
        <div>تاريخ الإصدار: {{ $invoice->issue_date?->format('Y-m-d') }}</div>
        @if($invoice->due_date)
            <div>تاريخ الاستحقاق: {{ $invoice->due_date->format('Y-m-d') }}</div>
        @endif
        <div>الإجمالي: {{ number_format((float) $invoice->total_amount, 2) }}</div>
        <div>المدفوع: {{ number_format((float) $invoice->paid_amount, 2) }}</div>
        <div>المتبقي: {{ number_format((float) $invoice->remaining_amount, 2) }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>الوصف</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>المجموع</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ number_format((float) $item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
