@php
    $pendingCount = $group->pendingRequests()->count();
    $registrationSettings = \App\Models\GroupRegistrationSetting::where('group_id', $group->id)->first();
@endphp

<div class="group-show-hero dashboard-fade-in mb-4">
    <div class="row align-items-start g-3">
        <div class="col-lg-7">
            <div class="group-show-hero__content">
                <span class="group-show-hero__eyebrow">
                    <i class="fe fe-layers me-1"></i>
                    تفاصيل المجموعة
                </span>
                <h2 class="group-show-hero__title mb-2">{{ $group->name }}</h2>
                @if($group->description)
                    <p class="group-show-hero__desc mb-0">{{ $group->description }}</p>
                @else
                    <p class="group-show-hero__desc mb-0 text-muted">لا يوجد وصف لهذه المجموعة.</p>
                @endif
                <div class="group-show-hero__meta mt-3">
                    @if($course)
                        <span class="badge bg-primary-transparent text-primary">
                            <i class="fe fe-book-open me-1"></i>{{ $course->title }}
                        </span>
                    @endif
                    @if($group->is_camp)
                        <span class="badge bg-success-transparent text-success">
                            <i class="fe fe-dollar-sign me-1"></i>معسكر — ${{ number_format((float) $group->price, 2) }}
                        </span>
                        @if($group->start_date || $group->end_date)
                            <span class="badge bg-info-transparent text-info">
                                <i class="fe fe-calendar me-1"></i>
                                {{ $group->start_date ? $group->start_date->format('Y-m-d') : '—' }}
                                →
                                {{ $group->end_date ? $group->end_date->format('Y-m-d') : '—' }}
                            </span>
                        @endif
                    @endif
                    @if($group->device_lock_enabled)
                        <span class="badge bg-warning-transparent text-warning">
                            <i class="fe fe-shield me-1"></i>تقييد الأجهزة الموثوقة مُفعّل
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="group-show-actions">
                @if($course)
                    <a href="{{ route('courses.groups.membership-requests', [$course->id, $group->id]) }}"
                       class="group-show-action group-show-action--info">
                        <span class="group-show-action__icon"><i class="fe fe-user-plus"></i></span>
                        <span class="group-show-action__text">
                            طلبات الانضمام
                            @if($pendingCount > 0)
                                <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                            @endif
                        </span>
                    </a>
                    @if($registrationSettings && $registrationSettings->is_registration_enabled)
                        <a href="{{ route('frontend.group-registration.create', $group->id) }}" target="_blank"
                           class="group-show-action group-show-action--success">
                            <span class="group-show-action__icon"><i class="fe fe-link"></i></span>
                            <span class="group-show-action__text">رابط التسجيل</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.group-registration-settings.index', $group->id) }}"
                       class="group-show-action group-show-action--warning">
                        <span class="group-show-action__icon"><i class="fe fe-settings"></i></span>
                        <span class="group-show-action__text">إعدادات التسجيل</span>
                    </a>
                    <a href="{{ route('courses.groups.edit', [$course->id, $group->id]) }}"
                       class="group-show-action group-show-action--primary">
                        <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                        <span class="group-show-action__text">تعديل</span>
                    </a>
                    <a href="{{ route('groups.lessons', $group->id) }}"
                       class="group-show-action group-show-action--info">
                        <span class="group-show-action__icon"><i class="fe fe-lock"></i></span>
                        <span class="group-show-action__text">دروس المجموعة</span>
                    </a>
                    <a href="{{ route('groups.notifications', $group->id) }}"
                       class="group-show-action group-show-action--warning">
                        <span class="group-show-action__icon"><i class="fe fe-bell"></i></span>
                        <span class="group-show-action__text">إشعارات المجموعة</span>
                    </a>
                @else
                    @php $firstCourse = $group->courses->first(); @endphp
                    @if($firstCourse)
                        <a href="{{ route('courses.groups.membership-requests', [$firstCourse->id, $group->id]) }}"
                           class="group-show-action group-show-action--info">
                            <span class="group-show-action__icon"><i class="fe fe-user-plus"></i></span>
                            <span class="group-show-action__text">
                                طلبات الانضمام
                                @if($pendingCount > 0)
                                    <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                                @endif
                            </span>
                        </a>
                    @endif
                    <a href="{{ route('groups.edit', $group->id) }}"
                       class="group-show-action group-show-action--primary">
                        <span class="group-show-action__icon"><i class="fe fe-edit-2"></i></span>
                        <span class="group-show-action__text">تعديل</span>
                    </a>
                    <a href="{{ route('groups.lessons', $group->id) }}"
                       class="group-show-action group-show-action--info">
                        <span class="group-show-action__icon"><i class="fe fe-lock"></i></span>
                        <span class="group-show-action__text">دروس المجموعة</span>
                    </a>
                    <a href="{{ route('groups.notifications', $group->id) }}"
                       class="group-show-action group-show-action--warning">
                        <span class="group-show-action__icon"><i class="fe fe-bell"></i></span>
                        <span class="group-show-action__text">إشعارات المجموعة</span>
                    </a>
                @endif
                <form action="{{ route('groups.delete', $group->id) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('هل أنت متأكد من حذف هذه المجموعة؟');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="group-show-action group-show-action--danger w-100">
                        <span class="group-show-action__icon"><i class="fe fe-trash-2"></i></span>
                        <span class="group-show-action__text">حذف</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
