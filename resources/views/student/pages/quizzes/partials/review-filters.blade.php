<div class="card custom-card student-quizzes-filters-panel mb-4">
    <div class="card-header border-0 pb-0">
        <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm bg-primary-transparent">
                <i class="fe fe-filter text-primary"></i>
            </span>
            <div>
                <h6 class="card-title mb-0">تصفية المحاولات</h6>
                <p class="text-muted fs-12 mb-0">اختر الحالة أو النتيجة أو الاختبار</p>
            </div>
        </div>
    </div>
    <div class="card-body pt-3">
        <form method="GET" action="{{ route('student.quizzes.review.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fs-12 fw-semibold">حالة المحاولة</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">جميع الحالات</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                    <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>تم التسليم</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fs-12 fw-semibold">النتيجة</label>
                <select name="result" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">جميع النتائج</option>
                    <option value="passed" {{ request('result') == 'passed' ? 'selected' : '' }}>ناجح</option>
                    <option value="failed" {{ request('result') == 'failed' ? 'selected' : '' }}>راسب</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fs-12 fw-semibold">الاختبار</label>
                <select name="quiz_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">جميع الاختبارات</option>
                    @foreach($quizzes ?? [] as $quiz)
                        <option value="{{ $quiz->id }}" {{ request('quiz_id') == $quiz->id ? 'selected' : '' }}>
                            {{ $quiz->title }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>
