<div class="student-my-courses-empty text-center py-5">
    <div class="student-my-courses-empty__icon mb-4">
        <i class="fe fe-credit-card"></i>
    </div>
    @if(request('status'))
        <h4 class="mb-2">لا توجد مدفوعات بهذا التصنيف</h4>
        <p class="text-muted mb-4">جرّب تصفية أخرى أو اعرض جميع المدفوعات.</p>
        <a href="{{ route('student.payments.index') }}" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fe fe-grid me-2"></i>عرض الكل
        </a>
    @else
        <h4 class="mb-2">لا توجد مدفوعات</h4>
        <p class="text-muted mb-4">لم تقم بأي عملية دفع بعد</p>
        <a href="{{ route('student.invoices.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fe fe-file-text me-2"></i>عرض فواتيري
        </a>
    @endif
</div>
