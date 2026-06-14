<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table mb-0">
        <thead>
            <tr>
                <th style="width: 48px;">#</th>
                <th>عنوان الدرس</th>
                <th>الكورس</th>
                <th>الموديول</th>
                <th>وقت القراءة</th>
                <th>الحالة</th>
                <th style="width: 100px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lessons as $lesson)
                <tr class="lessons-table-row">
                    <td>{{ $loop->iteration + ($lessons->currentPage() - 1) * $lessons->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="lessons-lesson-icon">
                                <i class="fe fe-book"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate" style="max-width: 280px;" title="{{ $lesson->title }}">
                                    {{ $lesson->title }}
                                </div>
                                @if($lesson->description)
                                    <small class="text-muted d-block text-truncate" style="max-width: 280px;" title="{{ $lesson->description }}">
                                        {{ Str::limit($lesson->description, 60) }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($lesson->module && $lesson->module->section && $lesson->module->section->course)
                            <a href="{{ route('courses.show', $lesson->module->section->course_id) }}" class="text-primary fw-semibold text-truncate d-inline-block" style="max-width: 180px;" title="{{ $lesson->module->section->course->title }}">
                                {{ $lesson->module->section->course->title }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 160px;" title="{{ optional($lesson->module)->title }}">
                            {{ optional($lesson->module)->title ?? '—' }}
                        </span>
                    </td>
                    <td>
                        @if($lesson->reading_time)
                            <span class="lessons-time-chip">
                                <i class="fe fe-clock"></i>
                                {{ $lesson->reading_time }} دقيقة
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($lesson->is_published)
                            <span class="lessons-status-chip lessons-status-chip--published">منشور</span>
                        @else
                            <span class="lessons-status-chip lessons-status-chip--draft">مسودة</span>
                        @endif
                    </td>
                    <td>
                        <div class="lessons-actions d-flex gap-1">
                            <a href="{{ route('lessons.edit', $lesson->id) }}"
                               class="btn btn-primary-light btn-sm lessons-actions__btn"
                               title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="lessons-empty-state">
                            <span class="lessons-empty-state__icon"><i class="fe fe-book-open"></i></span>
                            <p class="mb-0 text-muted">لا توجد دروس مطابقة للبحث</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($lessons->hasPages())
    <div class="mt-3">
        {{ $lessons->withQueryString()->links() }}
    </div>
@endif
