<div class="table-responsive">
    <table class="table table-hover text-nowrap dashboard-table admin-camps-table mb-0">
        <thead>
            <tr>
                <th scope="col" style="width: 48px;">#</th>
                <th scope="col">المعسكر</th>
                <th scope="col">التصنيف</th>
                <th scope="col">المدرب</th>
                <th scope="col">التاريخ</th>
                <th scope="col">المدة</th>
                <th scope="col">السعر</th>
                <th scope="col">المشاركين</th>
                <th scope="col">الحالة</th>
                <th scope="col" style="width: 130px;">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($camps as $camp)
                @php
                    $initial = mb_strtoupper(mb_substr($camp->name, 0, 1));
                @endphp
                <tr class="admin-camps-table__row">
                    <td>{{ $loop->iteration + ($camps->currentPage() - 1) * $camps->perPage() }}</td>

                    <td>
                        <div class="d-flex align-items-center gap-2 min-w-0">
                            <div class="admin-camps-table__thumb flex-shrink-0">
                                @if($camp->image)
                                    <img src="{{ asset('storage/' . $camp->image) }}" alt="{{ $camp->name }}">
                                @else
                                    <span>{{ $initial }}</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('training-camps.show', $camp->id) }}"
                                   class="fw-semibold text-decoration-none d-block text-truncate admin-camps-table__name">
                                    {{ $camp->name }}
                                    @if($camp->is_featured)
                                        <i class="fe fe-star text-warning ms-1" title="مميز"></i>
                                    @endif
                                </a>
                                @if($camp->location)
                                    <small class="text-muted d-block text-truncate">
                                        <i class="fe fe-map-pin me-1"></i>{{ $camp->location }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td>
                        @if($camp->category)
                            <span class="group-show-chip group-show-chip--sm"
                                  @if($camp->category->color) style="background: {{ $camp->category->color }}18; color: {{ $camp->category->color }}; border-color: {{ $camp->category->color }}30;" @endif>
                                {{ $camp->category->name }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        @if($camp->instructor_name)
                            <span class="small">{{ $camp->instructor_name }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td>
                        <small class="d-block text-muted">
                            <i class="fe fe-calendar me-1"></i>{{ $camp->start_date->format('Y-m-d') }}
                        </small>
                        <small class="d-block text-muted">
                            <i class="fe fe-arrow-left me-1"></i>{{ $camp->end_date->format('Y-m-d') }}
                        </small>
                    </td>

                    <td>
                        <span class="group-show-chip group-show-chip--sm">{{ $camp->duration_days }} يوم</span>
                    </td>

                    <td>
                        <strong class="small">${{ number_format($camp->price, 2) }}</strong>
                    </td>

                    <td>
                        <span class="group-show-chip group-show-chip--sm">
                            {{ $camp->current_participants }}
                            @if($camp->max_participants)
                                / {{ $camp->max_participants }}
                            @endif
                        </span>
                        @if($camp->isFull())
                            <i class="fe fe-alert-circle text-danger ms-1" title="ممتلئ"></i>
                        @endif
                    </td>

                    <td>
                        <div class="d-flex flex-column gap-1 align-items-start">
                            @if($camp->isOngoing())
                                <span class="admin-camp-status admin-camp-status--ongoing">جاري</span>
                            @elseif($camp->hasEnded())
                                <span class="admin-camp-status admin-camp-status--completed">منتهي</span>
                            @else
                                <span class="admin-camp-status admin-camp-status--upcoming">قادم</span>
                            @endif
                            @if($camp->is_active)
                                <span class="admin-camp-status admin-camp-status--active">نشط</span>
                            @else
                                <span class="admin-camp-status admin-camp-status--inactive">معطّل</span>
                            @endif
                        </div>
                    </td>

                    <td>
                        <div class="d-flex align-items-center gap-1">
                            <a href="{{ route('training-camps.show', $camp->id) }}"
                               class="btn btn-sm btn-primary-light" title="عرض">
                                <i class="fe fe-eye"></i>
                            </a>
                            <a href="{{ route('training-camps.edit', $camp->id) }}"
                               class="btn btn-sm btn-info-light" title="تعديل">
                                <i class="fe fe-edit-2"></i>
                            </a>
                            <button type="button"
                                    class="btn btn-sm btn-danger-light js-delete-camp"
                                    data-camp-id="{{ $camp->id }}"
                                    data-camp-name="{{ e(Str::limit($camp->name, 50)) }}"
                                    title="حذف">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">
                        <div class="group-show-empty py-5">
                            <i class="fe fe-flag group-show-empty__icon"></i>
                            <h5 class="group-show-empty__title">لا توجد معسكرات</h5>
                            <p class="group-show-empty__desc mb-3">لم يتم العثور على معسكرات مطابقة للبحث أو الفلاتر الحالية.</p>
                            <a href="{{ route('training-camps.create') }}" class="btn btn-primary btn-sm">
                                <i class="fe fe-plus me-1"></i>إضافة معسكر جديد
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($camps->count() > 0)
    <div class="d-flex justify-content-center mt-4">
        {{ $camps->withQueryString()->links() }}
    </div>
@endif
