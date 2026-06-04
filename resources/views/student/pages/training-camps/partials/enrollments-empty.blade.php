<div class="student-my-courses-empty text-center py-5">
    <div class="student-my-courses-empty__icon mb-4">
        <i class="fe fe-flag"></i>
    </div>
    @if(request('status'))
        <h4 class="mb-2">لا توجد تسجيلات بهذا التصنيف</h4>
        <p class="text-muted mb-4">جرّب تصفية أخرى أو اعرض جميع التسجيلات.</p>
        <a href="{{ route('student.training-camps.my-enrollments') }}" class="btn btn-outline-primary rounded-pill px-4">
            <i class="fe fe-grid me-2"></i>عرض الكل
        </a>
    @else
        <h4 class="mb-2">لا توجد تسجيلات</h4>
        <p class="text-muted mb-4">لم تقم بالتسجيل في أي معسكر تدريبي بعد</p>
        <a href="{{ route('student.training-camps.index') }}" class="btn btn-primary rounded-pill px-4">
            <i class="fe fe-search me-2"></i>تصفح المعسكرات المتاحة
        </a>
    @endif
</div>
