@php
    $sort = request('sort', 'created_at');
    $order = request('order', 'desc');
@endphp

<div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
    <h6 class="group-show-members-card__title mb-0">
        قائمة المجموعات
        <span class="group-show-members-card__count" id="groups-total-count">{{ $groups->total() }}</span>
    </h6>
    <div class="d-flex flex-wrap align-items-center gap-2">
        <label class="form-label mb-0 small text-muted" for="filter-sort">ترتيب</label>
        <select id="filter-sort" class="form-select form-select-sm" style="width: auto; min-width: 160px;">
            <option value="created_at:desc" {{ $sort === 'created_at' && $order === 'desc' ? 'selected' : '' }}>الأحدث أولاً</option>
            <option value="created_at:asc" {{ $sort === 'created_at' && $order === 'asc' ? 'selected' : '' }}>الأقدم أولاً</option>
            <option value="name:asc" {{ $sort === 'name' && $order === 'asc' ? 'selected' : '' }}>الاسم (أ-ي)</option>
            <option value="name:desc" {{ $sort === 'name' && $order === 'desc' ? 'selected' : '' }}>الاسم (ي-أ)</option>
            <option value="members_count:desc" {{ $sort === 'members_count' && $order === 'desc' ? 'selected' : '' }}>الأعضاء (الأكثر)</option>
            <option value="members_count:asc" {{ $sort === 'members_count' && $order === 'asc' ? 'selected' : '' }}>الأعضاء (الأقل)</option>
        </select>
    </div>
</div>
<div class="card-body">
    @if($groups->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover text-nowrap dashboard-table mb-0">
                <thead>
                    <tr>
                        <th>اسم المجموعة</th>
                        <th>النوع</th>
                        <th>الكورس</th>
                        <th>عدد الأعضاء</th>
                        <th>المجموعات المطلوبة للظهور</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $group)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar avatar-sm bg-primary-transparent flex-shrink-0">
                                        <i class="fe fe-users"></i>
                                    </span>
                                    <div class="min-w-0">
                                        @php $firstCourseForLink = $group->courses->first(); @endphp
                                        @if($firstCourseForLink)
                                            <a href="{{ route('courses.groups.show', [$firstCourseForLink->id, $group->id]) }}"
                                               class="fw-semibold text-primary text-decoration-none">
                                                {{ $group->name }}
                                            </a>
                                        @else
                                            <a href="{{ route('groups.show', $group->id) }}"
                                               class="fw-semibold text-primary text-decoration-none">
                                                {{ $group->name }}
                                            </a>
                                        @endif
                                        @if($group->description)
                                            <small class="d-block text-muted text-truncate" style="max-width: 220px;">
                                                {{ Str::limit($group->description, 50) }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($group->is_camp)
                                    <span class="badge bg-warning-transparent text-warning">
                                        <i class="fe fe-flag me-1"></i>معسكر
                                    </span>
                                    @if($group->price !== null)
                                        <small class="d-block text-muted mt-1">${{ number_format((float) $group->price, 2) }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-secondary-transparent text-secondary">مجموعة</span>
                                @endif
                            </td>
                            <td>
                                @if($group->courses && $group->courses->count() > 0)
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 260px;">
                                        @foreach($group->courses->take(3) as $course)
                                            <a href="{{ route('courses.show', $course->id) }}"
                                               class="badge bg-primary-transparent text-primary text-decoration-none"
                                               title="{{ $course->title }}">
                                                <i class="fe fe-book-open me-1"></i>{{ Str::limit($course->title, 22) }}
                                            </a>
                                        @endforeach
                                        @if($group->courses->count() > 3)
                                            <span class="badge bg-secondary-transparent text-secondary"
                                                  title="{{ $group->courses->skip(3)->pluck('title')->implode('، ') }}">
                                                +{{ $group->courses->count() - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">لا توجد كورسات</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info-transparent">
                                    <i class="fe fe-users me-1"></i>{{ $group->members_count ?? 0 }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $requiredGroups = $group->visibilityRequirements
                                        ->map(fn ($req) => $req->requiredGroup)
                                        ->filter();
                                @endphp
                                @if($requiredGroups->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1" style="max-width: 260px;">
                                        @foreach($requiredGroups->take(3) as $requiredGroup)
                                            <span class="badge bg-info-transparent text-info" title="{{ $requiredGroup->name }}">
                                                <i class="fe fe-eye me-1"></i>{{ Str::limit($requiredGroup->name, 22) }}
                                            </span>
                                        @endforeach
                                        @if($requiredGroups->count() > 3)
                                            <span class="badge bg-secondary-transparent text-secondary"
                                                  title="{{ $requiredGroups->skip(3)->pluck('name')->implode('، ') }}">
                                                +{{ $requiredGroups->count() - 3 }}
                                            </span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mt-1">أعضاء هذه المجموعات يمكنهم الرؤية</small>
                                @else
                                    <span class="badge bg-danger-transparent text-danger" title="بدون مجموعات مطلوبة لن تظهر للطلاب">
                                        <i class="fe fe-eye-off me-1"></i>غير محددة
                                    </span>
                                @endif
                            </td>
                            <td>{{ $group->created_at->format('Y-m-d') }}</td>
                            <td>
                                @if($group->is_active)
                                    <span class="badge bg-success-transparent text-success">نشطة</span>
                                @else
                                    <span class="badge bg-secondary-transparent text-secondary">غير نشطة</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @php $firstCourse = $group->courses->first(); @endphp
                                    @if($firstCourse)
                                        <a href="{{ route('courses.groups.show', [$firstCourse->id, $group->id]) }}"
                                           class="btn btn-sm btn-info-light" title="عرض">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <a href="{{ route('courses.groups.edit', [$firstCourse->id, $group->id]) }}"
                                           class="btn btn-sm btn-primary-light" title="تعديل">
                                            <i class="fe fe-edit-2"></i>
                                        </a>
                                    @else
                                        <a href="{{ route('groups.show', $group->id) }}"
                                           class="btn btn-sm btn-info-light" title="عرض">
                                            <i class="fe fe-eye"></i>
                                        </a>
                                        <a href="{{ route('groups.edit', $group->id) }}"
                                           class="btn btn-sm btn-primary-light" title="تعديل">
                                            <i class="fe fe-edit-2"></i>
                                        </a>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger-light js-delete-group" title="حذف"
                                            data-group-id="{{ $group->id }}"
                                            data-group-name="{{ $group->name }}"
                                            data-members-count="{{ $group->members_count ?? 0 }}">
                                        <i class="fe fe-trash-2"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center" id="groups-pagination">
            {{ $groups->withQueryString()->links() }}
        </div>
    @else
        <div class="group-show-empty">
            <div class="group-show-empty__icon">
                <i class="fe fe-layers"></i>
            </div>
            <h4 class="group-show-empty__title">لا توجد مجموعات</h4>
            <p class="text-muted mb-3">لا توجد نتائج مطابقة للفلاتر الحالية.</p>
            <a href="{{ route('groups.select-course') }}" class="btn btn-primary">
                <i class="fe fe-plus me-1"></i>إضافة مجموعة
            </a>
        </div>
    @endif
</div>
