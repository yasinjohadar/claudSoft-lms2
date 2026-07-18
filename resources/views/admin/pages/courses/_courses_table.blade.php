<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table admin-users-table mb-0">
        <thead>
            <tr>
                <th scope="col" style="width: 48px;">#</th>
                <th scope="col">الكورس</th>
                <th scope="col">التصنيف</th>
                <th scope="col">المستوى</th>
                <th scope="col">المدرب</th>
                <th scope="col">الدروس</th>
                <th scope="col">الطلاب</th>
                <th scope="col">السعر</th>
                <th scope="col">الحالة</th>
                <th scope="col" style="width: 200px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse($courses as $course)
                @php
                    $imageUrl = $course->image ? course_image_url($course->image) : null;
                    $initial = mb_strtoupper(mb_substr($course->title, 0, 1));
                @endphp
                <tr class="admin-users-table__row">
                    <td>{{ $loop->iteration + ($courses->currentPage() - 1) * $courses->perPage() }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="admin-users-table__avatar flex-shrink-0">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}"
                                         alt="{{ $course->title }}"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <span style="display:none;">{{ $initial }}</span>
                                @else
                                    <span>{{ $initial }}</span>
                                @endif
                                @if($course->image)
                                    @include('admin.components.storage-location-badge', [
                                        'location' => $course->image_storage ?? null,
                                    ])
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('courses.show', $course->id) }}"
                                   class="fw-semibold text-decoration-none d-block text-truncate admin-users-table__name"
                                   title="{{ $course->title }}">
                                    {{ Str::limit($course->title, 42) }}
                                </a>
                                @if($course->code)
                                    <small class="text-muted d-block">{{ $course->code }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($course->category)
                            <span class="group-show-chip group-show-chip--sm"
                                  @if($course->category->color) style="background: {{ $course->category->color }}18; color: {{ $course->category->color }}; border-color: {{ $course->category->color }}30;" @endif>
                                {{ $course->category->name }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($course->level === 'beginner')
                            <span class="group-show-chip group-show-chip--sm text-success">مبتدئ</span>
                        @elseif($course->level === 'intermediate')
                            <span class="group-show-chip group-show-chip--sm text-info">متوسط</span>
                        @elseif($course->level === 'advanced')
                            <span class="group-show-chip group-show-chip--sm text-danger">متقدم</span>
                        @elseif($course->level === 'expert')
                            <span class="group-show-chip group-show-chip--sm text-warning">خبير</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($course->instructor)
                            <span class="d-inline-flex align-items-center gap-1 small">
                                <span class="admin-users-table__avatar flex-shrink-0" style="width:28px;height:28px;font-size:0.7rem;border-radius:8px;">
                                    <span>{{ mb_strtoupper(mb_substr($course->instructor->name, 0, 1)) }}</span>
                                </span>
                                <span class="text-truncate" style="max-width:120px;">{{ $course->instructor->name }}</span>
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="group-show-chip group-show-chip--sm">
                            <i class="fe fe-book me-1"></i>{{ $course->modules_count ?? 0 }}
                        </span>
                    </td>
                    <td>
                        <span class="group-show-chip group-show-chip--sm text-success">
                            <i class="fe fe-users me-1"></i>{{ $course->enrollments_count ?? 0 }}
                        </span>
                    </td>
                    <td>
                        @if($course->price > 0)
                            <strong class="text-primary">${{ number_format($course->price, 2) }}</strong>
                        @else
                            <strong class="text-success">مجاني</strong>
                        @endif
                    </td>
                    <td>
                        @if($course->is_published)
                            <span class="admin-users-status-chip admin-users-status-chip--active">
                                <i class="fe fe-check-circle"></i>منشور
                            </span>
                        @else
                            <span class="admin-users-status-chip admin-users-status-chip--inactive">
                                <i class="fe fe-edit-3"></i>مسودة
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <a href="{{ route('courses.show', $course->id) }}"
                               class="btn btn-sm btn-info-light" title="عرض">
                                <i class="fe fe-eye"></i>
                            </a>
                            <a href="{{ route('courses.edit', $course->id) }}"
                               class="btn btn-sm btn-primary-light" title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>
                            <a href="{{ route('courses.enrollments.index', $course->id) }}"
                               class="btn btn-sm btn-success-light" title="التسجيلات">
                                <i class="fe fe-users"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-{{ $course->is_published ? 'warning' : 'success' }}-light"
                                    onclick="togglePublish({{ $course->id }}, '{{ e(Str::limit($course->title, 40)) }}', {{ $course->is_published ? 'true' : 'false' }})"
                                    title="{{ $course->is_published ? 'إلغاء النشر' : 'نشر' }}">
                                <i class="fe fe-{{ $course->is_published ? 'eye-off' : 'send' }}"></i>
                            </button>
                            <form action="{{ route('courses.destroy', $course->id) }}"
                                  method="POST"
                                  class="d-inline course-delete-form"
                                  data-course-title="{{ $course->title }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-danger-light btn-delete-course" title="حذف">
                                    <i class="fe fe-trash-2"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="p-0 border-0">
                        <div class="group-show-empty py-5">
                            <i class="fe fe-book-open group-show-empty__icon" style="width:64px;height:64px;font-size:1.5rem;"></i>
                            <h5 class="group-show-empty__title">لا توجد كورسات</h5>
                            <p class="group-show-empty__desc mb-3">لم يتم العثور على كورسات مطابقة للبحث أو الفلاتر الحالية.</p>
                            <a href="{{ route('courses.create') }}" class="btn btn-primary btn-sm rounded-pill">
                                <i class="fe fe-plus me-1"></i>إضافة كورس جديد
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($courses->hasPages())
    <div class="mt-3">
        {{ $courses->appends(request()->query())->links() }}
    </div>
@endif
