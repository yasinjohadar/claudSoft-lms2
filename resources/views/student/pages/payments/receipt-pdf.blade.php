<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال دفع - {{ $payment->receipt_number }}</title>

    <!-- Cairo Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            direction: rtl;
            background: #f5f5f5;
            padding: 20px;
        }

        .main-content-body-invoice {
            max-width: 900px;
            margin: 0 auto;
        }

        .card-invoice {
            background: white;
            border: 1px solid #e0e0e0;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            border-radius: 10px;
            overflow: hidden;
        }

        .invoice-header {
            background: linear-gradient(135deg, #6c5ce7 0%, #8b7ce8 100%);
            color: white;
            padding: 40px;
            text-align: center;
            border-bottom: 3px solid #5a4ad1;
        }

        .invoice-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .receipt-number {
            font-size: 20px;
            font-weight: 600;
            opacity: 0.95;
        }

        .card-body {
            padding: 40px;
        }

        .billed-from h6,
        .billed-to h6 {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .billed-from p,
        .billed-to p {
            color: #666;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 5px;
        }

        .text-gray-6 {
            color: #6c757d;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: block;
        }

        .invoice-info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            margin-bottom: 5px;
        }

        .invoice-info-row span:first-child {
            color: #666;
            font-weight: 500;
        }

        .invoice-info-row span:last-child {
            color: #333;
            font-weight: 600;
        }

        .student-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .student-info-grid .invoice-info-row {
            background: white;
            padding: 12px 15px;
            border-radius: 6px;
            border-right: 3px solid #6c5ce7;
            margin-bottom: 0;
        }

        @media print {
            .student-info-grid {
                gap: 10px;
            }
        }

        .payment-amount-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin: 30px 0;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .payment-amount-label {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 10px;
        }

        .payment-amount {
            font-size: 48px;
            font-weight: 700;
        }

        .table-invoice {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            border: 1px solid #dee2e6;
        }

        .table-invoice thead {
            background: linear-gradient(135deg, #6c5ce7 0%, #8b7ce8 100%);
            color: white;
        }

        .table-invoice thead th {
            font-weight: 600;
            padding: 15px;
            border: none;
            font-size: 14px;
            text-align: right;
        }

        .table-invoice tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #e0e0e0;
            color: #555;
        }

        .table-invoice tbody tr:hover {
            background-color: #f8f9fa;
        }

        .info-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-right: 4px solid #6c5ce7;
        }

        .info-section h3 {
            color: #6c5ce7;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 15px;
            border-bottom: 2px solid #6c5ce7;
            padding-bottom: 8px;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            display: inline-block;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .print-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #6c5ce7;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .print-button:hover {
            background: #5a4ad1;
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.15);
        }

        .receipt-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            text-align: center;
            color: #999;
            font-size: 14px;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(108, 92, 231, 0.05);
            font-weight: 900;
            z-index: 0;
            pointer-events: none;
        }

        .card-body {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="print-button no-print">
        <i class="fa-solid fa-print"></i> طباعة الإيصال
    </button>

    <div class="main-content-body-invoice">
        <div class="card-invoice">
            <div class="invoice-header">
                <h1 class="invoice-title">إيصال دفع</h1>
                <div class="receipt-number">رقم الإيصال: {{ $payment->receipt_number }}</div>
            </div>

            <div class="watermark">مدفوع</div>

            <div class="card-body">
                <!-- Student Detailed Information -->
                @if($payment->student)
                <div class="info-section">
                    <h3>📋 معلومات الطالب الكاملة</h3>
                    <div class="student-info-grid">
                        <p class="invoice-info-row">
                            <span>👤 الاسم الكامل</span>
                            <span>{{ $payment->student->name }}</span>
                        </p>
                        @if($payment->student->email)
                        <p class="invoice-info-row">
                            <span>📧 البريد الإلكتروني</span>
                            <span>{{ $payment->student->email }}</span>
                        </p>
                        @endif
                        @if($payment->student->phone)
                        <p class="invoice-info-row">
                            <span>📱 رقم الجوال</span>
                            <span>{{ $payment->student->phone }}</span>
                        </p>
                        @endif
                        @if($payment->student->national_id)
                        <p class="invoice-info-row">
                            <span>🆔 رقم الهوية الوطنية</span>
                            <span>{{ $payment->student->national_id }}</span>
                        </p>
                        @endif
                        @if($payment->student->date_of_birth)
                        <p class="invoice-info-row">
                            <span>🎂 تاريخ الميلاد</span>
                            <span>{{ $payment->student->date_of_birth }}</span>
                        </p>
                        @endif
                        @if($payment->student->gender)
                        <p class="invoice-info-row">
                            <span>⚧ الجنس</span>
                            <span>{{ $payment->student->gender == 'male' ? 'ذكر' : 'أنثى' }}</span>
                        </p>
                        @endif
                        @if($payment->student->city)
                        <p class="invoice-info-row">
                            <span>🏙️ المدينة</span>
                            <span>{{ $payment->student->city }}</span>
                        </p>
                        @endif
                        @if($payment->student->created_at)
                        <p class="invoice-info-row">
                            <span>📅 تاريخ التسجيل</span>
                            <span>{{ $payment->student->created_at->format('Y-m-d') }}</span>
                        </p>
                        @endif
                        @if($payment->student->address)
                        <p class="invoice-info-row" style="grid-column: 1 / -1;">
                            <span>📍 العنوان</span>
                            <span>{{ $payment->student->address }}</span>
                        </p>
                        @endif
                        @if($payment->student->guardian_name)
                        <p class="invoice-info-row">
                            <span>👨‍👦 اسم ولي الأمر</span>
                            <span>{{ $payment->student->guardian_name }}</span>
                        </p>
                        @endif
                        @if($payment->student->guardian_phone)
                        <p class="invoice-info-row">
                            <span>☎️ جوال ولي الأمر</span>
                            <span>{{ $payment->student->guardian_phone }}</span>
                        </p>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Payment Amount -->
                <div class="payment-amount-section">
                    <div class="payment-amount-label">المبلغ المدفوع</div>
                    <div class="payment-amount">${{ number_format($payment->amount, 2) }}</div>
                </div>

                <!-- Payment Information -->
                <div class="info-section">
                    <h3>معلومات الدفعة</h3>
                    <p class="invoice-info-row">
                        <span>رقم الدفعة</span>
                        <span>{{ $payment->payment_number }}</span>
                    </p>
                    <p class="invoice-info-row">
                        <span>تاريخ الدفع</span>
                        <span>{{ $payment->payment_date?->format('Y-m-d') }}</span>
                    </p>
                    <p class="invoice-info-row">
                        <span>طريقة الدفع</span>
                        <span>{{ $payment->paymentMethod->name ?? 'غير محدد' }}</span>
                    </p>
                    @if($payment->transaction_id)
                    <p class="invoice-info-row">
                        <span>رقم المعاملة</span>
                        <span>{{ $payment->transaction_id }}</span>
                    </p>
                    @endif
                    <p class="invoice-info-row">
                        <span>الحالة</span>
                        <span>
                            <span class="status-badge {{ $payment->status === 'completed' ? 'status-completed' : 'status-pending' }}">
                                {{ $payment->status === 'completed' ? 'مكتمل' : 'قيد الانتظار' }}
                            </span>
                        </span>
                    </p>
                    @if($payment->receivedBy)
                    <p class="invoice-info-row">
                        <span>تم الاستلام بواسطة</span>
                        <span>{{ $payment->receivedBy->name }}</span>
                    </p>
                    @endif
                </div>

                <!-- Invoice Information -->
                @if($payment->invoice)
                <div class="info-section">
                    <h3>معلومات الفاتورة المرتبطة</h3>
                    <p class="invoice-info-row">
                        <span>رقم الفاتورة</span>
                        <span>{{ $payment->invoice->invoice_number }}</span>
                    </p>
                    <p class="invoice-info-row">
                        <span>إجمالي الفاتورة</span>
                        <span>${{ number_format($payment->invoice->total_amount, 2) }}</span>
                    </p>
                    <p class="invoice-info-row">
                        <span>المبلغ المدفوع من الفاتورة</span>
                        <span class="text-success" style="color: #28a745;">${{ number_format($payment->invoice->paid_amount, 2) }}</span>
                    </p>
                    <p class="invoice-info-row">
                        <span>المبلغ المتبقي</span>
                        <span style="color: {{ $payment->invoice->remaining_amount > 0 ? '#dc3545' : '#28a745' }}; font-weight: 700;">
                            ${{ number_format($payment->invoice->remaining_amount, 2) }}
                        </span>
                    </p>
                </div>

                <!-- Invoice Items -->
                @if($payment->invoice->items->count() > 0)
                <div style="margin: 30px 0;">
                    <h3 style="color: #333; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #6c5ce7; padding-bottom: 10px;">
                        بنود الفاتورة
                    </h3>
                    <table class="table-invoice">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الوصف</th>
                                <th>الكمية</th>
                                <th>سعر الوحدة</th>
                                <th>المجموع</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payment->invoice->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                <td>${{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
                @endif

                <!-- Notes -->
                @if($payment->notes)
                <div class="info-section">
                    <h3>ملاحظات</h3>
                    <p style="color: #666; line-height: 1.8;">{{ $payment->notes }}</p>
                </div>
                @endif

                <!-- Footer -->
                <div class="receipt-footer">
                    <p>تم إصدار هذا الإيصال بتاريخ {{ now()->format('Y-m-d H:i') }}</p>
                    <p style="margin-top: 10px; font-weight: 600; color: #6c5ce7;">شكراً لك على الدفع</p>
                    <p style="margin-top: 15px; font-size: 12px;">
                        هذا إيصال رسمي ومعتمد من منصة كلاودسوفت
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
