<div class="card custom-card group-show-members-card dashboard-fade-in quizzes-page-animate mb-4">
    <div class="card-header border-0 pb-0">
        <h4 class="card-title mb-1 d-flex align-items-center gap-2">
            <span class="assignments-section-icon"><i class="fe fe-users"></i></span>
            إدارة المحاولات
        </h4>
    </div>
    <div class="card-body pt-3">
        <p class="text-muted small mb-3">
            استخدم هذه الإجراءات لإعطاء الطلاب فرصة جديدة أو تنظيف المحاولات العالقة.
            إجمالي المحاولات الحالية: <strong>{{ $stats['total_attempts'] ?? 0 }}</strong>
            (قيد التقدم: {{ $stats['in_progress'] ?? 0 }})
        </p>
        <div class="d-grid gap-2">
            <form action="{{ route('quizzes.reconcile-attempts', $quiz->id) }}" method="POST"
                  onsubmit="return confirm('تنظيف المحاولات العالقة والفارغة فقط؟ لن تُحذف المحاولات المكتملة التي تحتوي إجابات.')">
                @csrf
                <button type="submit" class="btn btn-outline-info btn-sm w-100">
                    <i class="fe fe-filter me-1"></i>تنظيف المحاولات العالقة والفارغة
                </button>
            </form>
            <form action="{{ route('quizzes.abandon-in-progress-attempts', $quiz->id) }}" method="POST"
                  onsubmit="return confirm('إلغاء جميع المحاولات قيد التقدم؟ المحاولات المكتملة والمصححة لن تتأثر.')">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                    <i class="fe fe-pause-circle me-1"></i>إلغاء المحاولات قيد التقدم
                </button>
            </form>
            <form action="{{ route('quizzes.reset-all-attempts', $quiz->id) }}" method="POST"
                  onsubmit="return confirm('تحذير: سيتم حذف جميع محاولات الطلاب لهذا الاختبار ({{ $stats['total_attempts'] ?? 0 }} محاولة) وإعادة فرص المحاولات من الصفر.\n\nهل أنت متأكد؟')">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                    <i class="fe fe-rotate-ccw me-1"></i>إعادة تعيين جميع المحاولات
                </button>
            </form>
        </div>
    </div>
</div>
