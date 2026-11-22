{{-- تسلسل الدفعات لفاتورة: كل دفعة برقمها ومبلغها وحالتها، والرصيد بعد كل دفعة مقبولة --}}
@php
    $timeline = $invoice->paymentTimeline();
    $statusMap = [
        'completed' => ['class' => 'success', 'icon' => 'fe-check-circle', 'label' => 'مقبولة'],
        'pending' => ['class' => 'warning', 'icon' => 'fe-clock', 'label' => 'قيد المراجعة'],
        'failed' => ['class' => 'danger', 'icon' => 'fe-x-circle', 'label' => 'مرفوضة'],
        'cancelled' => ['class' => 'secondary', 'icon' => 'fe-slash', 'label' => 'ملغاة'],
        'refunded' => ['class' => 'secondary', 'icon' => 'fe-rotate-ccw', 'label' => 'مستردة'],
    ];
@endphp

@if($timeline->isEmpty())
    <p class="text-muted small mb-0"><i class="fe fe-inbox me-1"></i>لا توجد دفعات على هذه الفاتورة بعد.</p>
@else
    <ol class="student-payment-timeline {{ $class ?? '' }}">
        @foreach($timeline as $entry)
            @php
                $payment = $entry['payment'];
                $status = $statusMap[$payment->status] ?? ['class' => 'secondary', 'icon' => 'fe-help-circle', 'label' => $payment->status];
            @endphp
            <li class="student-payment-timeline__item is-{{ $status['class'] }}">
                <span class="student-payment-timeline__seq">{{ $entry['seq'] }}</span>
                <div class="student-payment-timeline__body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                        <div>
                            <span class="student-payment-timeline__title">الدفعة رقم {{ $entry['seq'] }}</span>
                            <span class="student-payment-timeline__number">{{ $payment->payment_number }}</span>
                        </div>
                        <span class="badge bg-{{ $status['class'] }}-transparent">
                            <i class="fe {{ $status['icon'] }} me-1"></i>{{ $status['label'] }}
                        </span>
                    </div>

                    <div class="student-payment-timeline__amount text-{{ $status['class'] === 'secondary' ? 'muted' : $status['class'] }}">
                        ${{ number_format($payment->amount, 2) }}
                        <small>
                            @switch($payment->status)
                                @case('completed') تم احتسابها من الفاتورة @break
                                @case('pending') المبلغ الذي أدخلته — لا يُحتسب حتى تقبله الإدارة @break
                                @case('failed') لم تُحتسب @break
                                @default لم تُحتسب
                            @endswitch
                        </small>
                    </div>

                    <ul class="student-payment-timeline__meta">
                        <li><i class="fe fe-calendar"></i>تاريخ الدفع: {{ $payment->payment_date?->format('Y-m-d') ?? '—' }}</li>
                        @if($payment->paymentMethod)
                            <li><i class="fe fe-credit-card"></i>{{ $payment->paymentMethod->name }}</li>
                        @endif
                        @if($payment->created_at)
                            <li><i class="fe fe-upload"></i>أُرسلت: {{ $payment->created_at->format('Y-m-d H:i') }}</li>
                        @endif
                        @if($payment->reviewed_at && $payment->status !== 'pending')
                            <li><i class="fe fe-check-square"></i>{{ $payment->status === 'completed' ? 'قُبلت' : 'رُوجعت' }}: {{ $payment->reviewed_at->format('Y-m-d H:i') }}</li>
                        @endif
                    </ul>

                    @if(! is_null($entry['paid_after']))
                        <div class="student-payment-timeline__balance">
                            <span>إجمالي المدفوع بعدها: <strong class="text-success">${{ number_format($entry['paid_after'], 2) }}</strong></span>
                            <span>المتبقي بعدها: <strong class="text-danger">${{ number_format($entry['remaining_after'], 2) }}</strong></span>
                        </div>
                    @endif

                    @if($payment->status === 'failed' && $payment->rejection_reason)
                        <p class="text-danger small mb-1 mt-1"><i class="fe fe-alert-circle me-1"></i>سبب الرفض: {{ $payment->rejection_reason }}</p>
                    @endif
                    @if($payment->notes)
                        <p class="text-muted small mb-1 mt-1"><i class="fe fe-message-square me-1"></i>{{ $payment->notes }}</p>
                    @endif

                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @if($payment->has_receipt)
                            <a href="{{ route('student.payments.receipt', $payment->id) }}" class="btn btn-sm btn-outline-primary rounded-pill" target="_blank">
                                <i class="fe fe-paperclip me-1"></i>الإيصال المرفق
                            </a>
                        @endif
                        @if($payment->status === 'completed')
                            <a href="{{ route('student.payments.show', $payment->id) }}" class="btn btn-sm btn-outline-success rounded-pill">
                                <i class="fe fe-file-text me-1"></i>سند القبض
                            </a>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ol>
@endif
