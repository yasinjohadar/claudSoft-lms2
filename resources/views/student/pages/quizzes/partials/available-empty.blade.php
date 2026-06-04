<div class="col-12">
    <div class="card custom-card student-quizzes-panel">
        <div class="card-body text-center py-5">
            <div class="student-quizzes-available-empty__icon">
                <i class="fe fe-clipboard"></i>
            </div>
            <h5 class="mb-2">لا توجد اختبارات متاحة حالياً</h5>
            <p class="text-muted mb-4">جرّب تغيير الفلاتر أو عد لاحقاً عند إضافة اختبارات جديدة</p>
            @if(request()->hasAny(['course_id', 'quiz_type']))
                <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary rounded-pill px-4">
                    <i class="fe fe-refresh-cw me-1"></i>إعادة تعيين الفلاتر
                </a>
            @endif
        </div>
    </div>
</div>
