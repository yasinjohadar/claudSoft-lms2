@php
    $earningCatalog = app(\App\Services\Gamification\PointEarningCatalog::class);
@endphp

@forelse ($transactions as $transaction)
    @php
        $sourceLabel = $earningCatalog->getSourceLabel($transaction->source);
    @endphp
    <tr>
        <td>{{ $transactions->firstItem() + $loop->index }}</td>
        <td>
            @if ($transaction->user)
                <div class="fw-semibold">{{ $transaction->user->name }}</div>
                <small class="text-muted">{{ $transaction->user->email }}</small>
            @else
                <span class="text-muted">غير معروف</span>
            @endif
        </td>
        <td>
            @if ($transaction->points > 0)
                <span class="badge bg-success-transparent text-success">+{{ number_format($transaction->points) }}</span>
            @else
                <span class="badge bg-danger-transparent text-danger">{{ number_format($transaction->points) }}</span>
            @endif
        </td>
        <td>
            @switch($transaction->type)
                @case('earn')
                    <span class="badge bg-success">مكتسب</span>
                    @break
                @case('spend')
                    <span class="badge bg-warning">مصروف</span>
                    @break
                @case('bonus')
                    <span class="badge bg-info">مكافأة</span>
                    @break
                @case('penalty')
                    <span class="badge bg-danger">خصم</span>
                    @break
                @case('refund')
                    <span class="badge bg-primary">استرداد</span>
                    @break
                @case('adjustment')
                    <span class="badge bg-secondary">تعديل</span>
                    @break
                @default
                    <span class="badge bg-light text-dark">{{ $transaction->type }}</span>
            @endswitch
        </td>
        <td>
            <span class="badge bg-primary-transparent text-primary">{{ $sourceLabel }}</span>
            <div class="fs-11 text-muted mt-1">{{ $transaction->source }}</div>
        </td>
        <td>
            <span title="{{ $transaction->description }}">
                {{ Str::limit($transaction->description ?: '—', 40) }}
            </span>
            @if ($transaction->admin)
                <div class="fs-11 text-muted mt-1">بواسطة: {{ $transaction->admin->name }}</div>
            @endif
        </td>
        <td>
            <div>{{ $transaction->created_at->format('Y-m-d') }}</div>
            <small class="text-muted">{{ $transaction->created_at->format('H:i') }}</small>
        </td>
        <td class="text-center">
            @if ($transaction->user)
                <a href="{{ route('admin.gamification.points.user-transactions', $transaction->user) }}"
                    class="btn btn-sm btn-icon btn-outline-primary" title="سجل الطالب">
                    <i class="fe fe-user"></i>
                </a>
            @endif
            <button type="button" class="btn btn-sm btn-icon btn-outline-danger"
                data-bs-toggle="modal" data-bs-target="#deleteTransaction{{ $transaction->id }}"
                title="إلغاء المعاملة">
                <i class="fe fe-trash-2"></i>
            </button>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center text-muted py-5">
            <i class="fe fe-inbox fs-24 d-block mb-2"></i>
            لا توجد معاملات مطابقة للبحث
        </td>
    </tr>
@endforelse
