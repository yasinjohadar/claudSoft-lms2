<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0">
        <thead>
            <tr>
                <th style="width: 48px;">#</th>
                <th>عنوان الاختبار</th>
                <th>الكورس</th>
                <th>النوع</th>
                <th>الأسئلة</th>
                <th>الدرجة</th>
                <th>المحاولات</th>
                <th>الحالة</th>
                <th style="width: 150px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quizzes as $quiz)
                <tr class="quizzes-table-row">
                    <td>{{ $loop->iteration + ($quizzes->currentPage() - 1) * $quizzes->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="quizzes-quiz-icon"><i class="fe fe-file-text"></i></span>
                            <div class="min-w-0">
                                <a href="{{ route('quizzes.show', $quiz->id) }}" class="fw-semibold text-truncate d-block" style="max-width: 260px;" title="{{ $quiz->title }}">
                                    {{ $quiz->title }}
                                </a>
                                <small class="text-muted">
                                    <i class="fe fe-user me-1"></i>{{ $quiz->creator->name ?? 'غير محدد' }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($quiz->course)
                            <span class="assignments-course-chip" title="{{ $quiz->course->title }}">{{ $quiz->course->title }}</span>
                            @if($quiz->lesson)
                                <br><small class="assignments-lesson-chip mt-1" title="{{ $quiz->lesson->title }}">{{ $quiz->lesson->title }}</small>
                            @endif
                        @else
                            <span class="text-muted">بدون كورس</span>
                        @endif
                    </td>
                    <td>
                        @if($quiz->quiz_type == 'practice')
                            <span class="quizzes-type-chip quizzes-type-chip--practice">تدريبي</span>
                        @elseif($quiz->quiz_type == 'graded')
                            <span class="quizzes-type-chip quizzes-type-chip--graded">مُقيّم</span>
                        @elseif($quiz->quiz_type == 'final_exam')
                            <span class="quizzes-type-chip quizzes-type-chip--final">نهائي</span>
                        @else
                            <span class="quizzes-type-chip quizzes-type-chip--survey">استبيان</span>
                        @endif
                    </td>
                    <td>
                        <span class="quizzes-questions-chip">
                            <i class="fe fe-help-circle"></i>{{ $quiz->getQuestionCount() }}
                        </span>
                    </td>
                    <td><span class="assignments-grade-chip">{{ number_format($quiz->max_score, 1) }}</span></td>
                    <td>
                        <a href="{{ route('quizzes.show', $quiz->id) }}" class="quizzes-attempts-chip">
                            {{ $quiz->attempts_count }} محاولة
                        </a>
                    </td>
                    <td>
                        @if($quiz->is_published)
                            <span class="assignments-status-chip assignments-status-chip--published">منشور</span>
                        @else
                            <span class="assignments-status-chip assignments-status-chip--draft">مسودة</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('quizzes.show', $quiz->id) }}" class="btn btn-info-light btn-sm assignments-actions__btn" title="عرض">
                                <i class="fe fe-eye"></i>
                            </a>
                            <a href="{{ route('quizzes.edit', $quiz->id) }}" class="btn btn-primary-light btn-sm assignments-actions__btn" title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>
                            <form action="{{ route('quizzes.toggle-publish', $quiz->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-{{ $quiz->is_published ? 'warning' : 'success' }}-light btn-sm assignments-actions__btn"
                                        title="{{ $quiz->is_published ? 'إلغاء النشر' : 'نشر' }}">
                                    <i class="fe fe-{{ $quiz->is_published ? 'eye-off' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('quizzes.destroy', $quiz->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('هل أنت متأكد من حذف هذا الاختبار؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-light btn-sm assignments-actions__btn" title="حذف">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <span class="assignments-empty-state__icon d-inline-flex"><i class="fe fe-file-text"></i></span>
                        <p class="mb-2 text-muted">لا توجد اختبارات</p>
                        <a href="{{ route('quizzes.create') }}" class="btn btn-primary btn-sm">
                            <i class="fe fe-plus me-1"></i>إضافة اختبار جديد
                        </a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($quizzes->hasPages())
    <div class="mt-3">{{ $quizzes->withQueryString()->links() }}</div>
@endif
