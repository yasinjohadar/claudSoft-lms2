<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الفاتورة {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .invoice-header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .invoice-number {
            font-size: 18px;
            font-weight: bold;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-block {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
        }
        .info-block h3 {
            font-size: 14px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-block p {
            margin-bottom: 5px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th,
        table td {
            padding: 10px;
            text-align: right;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        table tfoot td {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .table-success {
            background-color: #d4edda !important;
        }
        .table-danger {
            background-color: #f8d7da !important;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .notes h4 {
            margin-bottom: 10px;
        }
        .text-end {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <h1>فاتورة</h1>
        <div class="invoice-number">رقم الفاتورة: {{ $invoice->invoice_number }}</div>
    </div>

    <div class="info-section">
        <div class="info-block">
            <h3>معلومات الطالب:</h3>
            <p><strong>الاسم:</strong> {{ $invoice->student->name }}</p>
            <p><strong>البريد الإلكتروني:</strong> {{ $invoice->student->email }}</p>
        </div>
        <div class="info-block">
            <h3>معلومات الفاتورة:</h3>
            <p><strong>تاريخ الإصدار:</strong> {{ $invoice->issue_date->format('Y-m-d') }}</p>
            <p><strong>تاريخ الاستحقاق:</strong> {{ $invoice->due_date ? $invoice->due_date->format('Y-m-d') : '-' }}</p>
            @php
                $statusLabels = [
                    'draft' => 'مسودة',
                    'issued' => 'صادرة',
                    'partial' => 'مدفوعة جزئياً',
                    'paid' => 'مدفوعة',
                    'cancelled' => 'ملغاة',
                    'refunded' => 'مستردة'
                ];
            @endphp
            <p><strong>الحالة:</strong> {{ $statusLabels[$invoice->status] ?? $invoice->status }}</p>
        </div>
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
                    <td>
                        {{ $item->description }}
                        @if($item->campEnrollment && $item->campEnrollment->camp)
                            <br><small>المعسكر: {{ $item->campEnrollment->camp->name }}</small>
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price, 2) }}</td>
                    <td>${{ number_format($item->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end"><strong>المجموع:</strong></td>
                <td><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
            </tr>
            <tr class="table-success">
                <td colspan="3" class="text-end"><strong>المدفوع:</strong></td>
                <td><strong>${{ number_format($invoice->paid_amount, 2) }}</strong></td>
            </tr>
            <tr class="table-danger">
                <td colspan="3" class="text-end"><strong>المتبقي:</strong></td>
                <td><strong>${{ number_format($invoice->remaining_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    @if($invoice->notes)
        <div class="notes">
            <h4>ملاحظات:</h4>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif
</body>
</html>

