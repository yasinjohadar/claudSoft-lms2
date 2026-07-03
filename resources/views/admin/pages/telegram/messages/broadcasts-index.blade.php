@extends('admin.pages.telegram.layout')

@php
    $tgPageTitle = 'تقارير بث Telegram';
    $tgTitle = 'تقارير البث الجماعي';
    $tgSubtitle = 'متابعة حالة كل حملة ونتائج الإرسال.';
    $breadcrumb = 'تقارير البث';
@endphp

@section('tg-content')
<div class="tg-form-section">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>الحالة</th>
                    <th>المستلمون</th>
                    <th>مرسل</th>
                    <th>فاشل</th>
                    <th>التاريخ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($broadcasts as $b)
                <tr>
                    <td>{{ $b->id }}</td>
                    <td>
                        @php
                            $badge = match($b->status) {
                                'completed' => 'success',
                                'failed' => 'danger',
                                'processing' => 'warning',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}-transparent text-{{ $badge }}">{{ $b->status }}</span>
                    </td>
                    <td>{{ $b->total_recipients }}</td>
                    <td class="text-success">{{ $b->sent_count }}</td>
                    <td class="text-danger">{{ $b->failed_count }}</td>
                    <td class="small text-muted">{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.telegram.broadcasts.show', $b) }}" class="btn btn-sm btn-outline-info">تفاصيل</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">لا توجد حملات بث بعد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $broadcasts->links() }}</div>
</div>
@endsection
