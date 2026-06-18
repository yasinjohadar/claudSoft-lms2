@switch($status)
    @case('completed')
        <span class="badge bg-success">مكتمل</span>
        @break
    @case('processing')
        <span class="badge bg-info">جاري الإرسال</span>
        @break
    @case('pending')
        <span class="badge bg-warning">قيد الانتظار</span>
        @break
    @case('failed')
        <span class="badge bg-danger">فشل</span>
        @break
    @default
        <span class="badge bg-secondary">{{ $status }}</span>
@endswitch
