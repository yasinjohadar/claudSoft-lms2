<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0">
        <thead>
            <tr>
                <th style="width: 48px;">#</th>
                <th>عنوان الواجب</th>
                <th>الكورس</th>
                <th>الدرس</th>
                <th>الدرجة القصوى</th>
                <th>موعد التسليم</th>
                <th>التسليمات</th>
                <th>الحالة</th>
                <th style="width: 120px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr class="assignments-table-row" id="assignment-row-{{ $assignment->id }}">
                    <td>{{ $loop->iteration + ($assignments->currentPage() - 1) * $assignments->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="assignments-lesson-icon"><i class="fe fe-clipboard"></i></span>
                            <div class="min-w-0">
                                <a href="{{ route('assignments.show', $assignment->id) }}" class="fw-semibold text-truncate d-block" style="max-width: 260px;" title="{{ $assignment->title }}">
                                    {{ $assignment->title }}
                                </a>
                                <small class="text-muted">
                                    <i class="fe fe-user me-1"></i>{{ $assignment->creator->name ?? 'غير محدد' }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($assignment->course)
                            <span class="assignments-course-chip" title="{{ $assignment->course->title }}">{{ $assignment->course->title }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($assignment->lesson)
                            <span class="assignments-lesson-chip" title="{{ $assignment->lesson->title }}">{{ $assignment->lesson->title }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td><span class="assignments-grade-chip">{{ $assignment->max_grade }}</span></td>
                    <td>
                        @if($assignment->due_date)
                            <div class="small">{{ $assignment->due_date->format('Y-m-d H:i') }}</div>
                            @if($assignment->isPastDue())
                                <span class="assignments-status-chip assignments-status-chip--expired"><i class="fe fe-clock me-1"></i>منتهي</span>
                            @else
                                <span class="assignments-status-chip assignments-status-chip--active"><i class="fe fe-clock me-1"></i>نشط</span>
                            @endif
                        @else
                            <span class="text-muted">غير محدد</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('assignments.show', $assignment->id) }}" class="assignments-submissions-chip">
                            <i class="fe fe-inbox"></i>{{ $assignment->submissions_count }} تسليم
                        </a>
                    </td>
                    <td>
                        @if($assignment->is_published)
                            <span class="assignments-status-chip assignments-status-chip--published">منشور</span>
                        @else
                            <span class="assignments-status-chip assignments-status-chip--draft">مسودة</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('assignments.show', $assignment->id) }}" class="btn btn-info-light btn-sm assignments-actions__btn" title="عرض">
                                <i class="fe fe-eye"></i>
                            </a>
                            <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn btn-primary-light btn-sm assignments-actions__btn" title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>
                            <form action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" class="d-inline assignment-delete-form" id="delete-form-{{ $assignment->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-danger-light btn-sm assignments-actions__btn btn-delete-assignment" title="حذف"
                                        data-assignment-id="{{ $assignment->id }}"
                                        data-assignment-title="{{ $assignment->title }}">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="assignments-empty-state">
                            <span class="assignments-empty-state__icon"><i class="fe fe-clipboard"></i></span>
                            <p class="mb-2 text-muted">لا توجد واجبات</p>
                            <a href="{{ route('assignments.create') }}" class="btn btn-primary btn-sm">
                                <i class="fe fe-plus me-1"></i>إضافة واجب جديد
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($assignments->hasPages())
    <div class="mt-3">{{ $assignments->withQueryString()->links() }}</div>
@endif
