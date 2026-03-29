@extends('frontend2.layouts.master')

@php
    $displayName = trim((string) ($student->name_ar ?: $student->name)) ?: $student->name;
    $pageTitle = $displayName . ' | الملف الشخصي | أكاديمية كلاودسوفت';
    $pageDescription = 'الملف الشخصي العام للطالب ' . $displayName . ($student->bio ? ' — ' . Str::limit(strip_tags($student->bio), 155) : ' — كورسات، شهادات، أوسمة وإنجازات على منصة كلاودسوفت.');
    $canonicalUrl = route('frontend.students.show', $student->id);
    $ogImage = $student->avatar ? asset('storage/' . $student->avatar) : asset('frontend2/assets/images/logo.png');
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
@endphp

@section('title', $pageTitle)
@section('meta_description', $pageDescription)

@push('head')
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="index, follow">
    <meta property="og:type" content="profile">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ Str::limit(strip_tags($pageDescription), 300) }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="ar_SY">
    <meta property="og:site_name" content="{{ config('app.name', 'أكاديمية كلاودسوفت') }}">
    <meta property="profile:first_name" content="{{ explode(' ', $displayName)[0] ?? $displayName }}">
    <meta property="profile:username" content="{{ $displayName }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ Str::limit(strip_tags($pageDescription), 200) }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@@type": "ListItem",
                "position": 1,
                "name": "الرئيسية",
                "item": "{{ route('frontend.home') }}"
            },
            {
                "@@type": "ListItem",
                "position": 2,
                "name": "الطلاب",
                "item": "{{ route('frontend.students.index') }}"
            },
            {
                "@@type": "ListItem",
                "position": 3,
                "name": "{{ Str::limit($displayName, 80) }}",
                "item": "{{ $canonicalUrl }}"
            }
        ]
    }
    </script>
    <script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $displayName,
    'url' => $canonicalUrl,
    'image' => $student->avatar ? asset('storage/' . $student->avatar) : null,
    'description' => $student->bio ? Str::limit(strip_tags($student->bio), 280) : null,
    'memberOf' => [
        '@type' => 'EducationalOrganization',
        'name' => config('app.name', 'أكاديمية كلاودسوفت'),
    ],
]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>
@endpush

@section('content')

    <section class="page-banner page-banner-students page-banner-student-detail">
        <div class="page-banner-overlay"></div>
        <div class="container position-relative">
            <div class="page-banner-content animate-on-scroll">
                <div class="page-banner-icon"><i class="fas fa-id-card"></i></div>
                <h1 class="page-banner-title student-detail-banner-title">{{ $displayName }}</h1>
                <p class="page-banner-desc">ملف شخصي عام — طالب في أكاديمية كلاودسوفت</p>
                <nav class="page-banner-breadcrumb" aria-label="breadcrumb">
                    <a href="{{ route('frontend.home') }}">الرئيسية</a>
                    <span class="page-banner-sep">/</span>
                    <a href="{{ route('frontend.students.index') }}">الطلاب</a>
                    <span class="page-banner-sep">/</span>
                    <span class="student-detail-bc-current">{{ Str::limit($displayName, 36) }}</span>
                </nav>
            </div>
        </div>
        <div class="page-banner-shape"></div>
    </section>

    <section class="section-padding student-detail-page">
        <div class="container">
            <div class="mb-4 animate-on-scroll">
                <a href="{{ route('frontend.students.index') }}" class="student-detail-back-link">
                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    <span>العودة لقائمة الطلاب</span>
                </a>
            </div>

            <div class="row g-4">
                <aside class="col-lg-4">
                    <div class="glass-panel student-detail-profile animate-on-scroll h-100">
                        <div class="student-detail-profile-top text-center">
                            <div class="student-showcase-avatar-wrap student-detail-avatar-xl mx-auto">
                                @if($student->avatar)
                                    <img src="{{ asset('storage/' . $student->avatar) }}" alt="{{ $displayName }}" class="student-showcase-avatar-img" width="140" height="140" loading="lazy">
                                @else
                                    <span class="student-showcase-avatar-placeholder student-detail-placeholder-xl" aria-hidden="true">{{ $initial }}</span>
                                @endif
                            </div>
                            <h2 class="student-detail-profile-name">{{ $displayName }}</h2>
                            <span class="student-detail-role-badge"><i class="fas fa-graduation-cap"></i> طالب</span>
                        </div>
                        <div class="student-detail-profile-body">
                            <h3 class="student-detail-section-heading"><i class="fas fa-circle-info"></i> المعلومات</h3>
                            @if($student->address)
                                <div class="student-detail-info-row">
                                    <span class="student-detail-info-label"><i class="fas fa-location-dot"></i> العنوان</span>
                                    <span class="student-detail-info-value">{{ $student->address }}</span>
                                </div>
                            @endif
                            @if($student->date_of_birth)
                                <div class="student-detail-info-row">
                                    <span class="student-detail-info-label"><i class="fas fa-calendar-days"></i> تاريخ الميلاد</span>
                                    <span class="student-detail-info-value">{{ \Carbon\Carbon::parse($student->date_of_birth)->format('Y-m-d') }}</span>
                                </div>
                            @endif
                            @if($student->gender)
                                <div class="student-detail-info-row">
                                    <span class="student-detail-info-label"><i class="fas fa-venus-mars"></i> الجنس</span>
                                    <span class="student-detail-info-value">{{ $student->gender == 'male' ? 'ذكر' : ($student->gender == 'female' ? 'أنثى' : $student->gender) }}</span>
                                </div>
                            @endif
                            <div class="student-detail-info-row">
                                <span class="student-detail-info-label"><i class="fas fa-clock"></i> تاريخ التسجيل</span>
                                <span class="student-detail-info-value">{{ $student->created_at->format('Y-m-d') }}</span>
                            </div>
                            <div class="student-detail-info-row">
                                <span class="student-detail-info-label"><i class="fas fa-calendar-check"></i> عضو منذ</span>
                                <span class="student-detail-info-value">{{ $student->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="col-lg-8">
                    <div class="row g-3 g-md-4 mb-4">
                        <div class="col-6 col-xl-3 animate-on-scroll">
                            <div class="glass-panel student-detail-stat h-100">
                                <div class="student-detail-stat-icon"><i class="fas fa-book"></i></div>
                                <div class="student-detail-stat-text">
                                    <span class="student-detail-stat-num">{{ $stats['total_courses'] ?? 0 }}</span>
                                    <span class="student-detail-stat-label">مسجّل</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3 animate-on-scroll animate-delay-1">
                            <div class="glass-panel student-detail-stat h-100">
                                <div class="student-detail-stat-icon"><i class="fas fa-circle-check"></i></div>
                                <div class="student-detail-stat-text">
                                    <span class="student-detail-stat-num">{{ $stats['completed_courses'] ?? 0 }}</span>
                                    <span class="student-detail-stat-label">مكتمل</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3 animate-on-scroll animate-delay-2">
                            <div class="glass-panel student-detail-stat h-100">
                                <div class="student-detail-stat-icon"><i class="fas fa-certificate"></i></div>
                                <div class="student-detail-stat-text">
                                    <span class="student-detail-stat-num">{{ $stats['certificates_count'] ?? 0 }}</span>
                                    <span class="student-detail-stat-label">شهادات</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-xl-3 animate-on-scroll animate-delay-3">
                            <div class="glass-panel student-detail-stat h-100">
                                <div class="student-detail-stat-icon"><i class="fas fa-award"></i></div>
                                <div class="student-detail-stat-text">
                                    <span class="student-detail-stat-num">{{ $stats['badges_count'] ?? 0 }}</span>
                                    <span class="student-detail-stat-label">أوسمة</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="glass-panel student-detail-tabs-shell animate-on-scroll overflow-hidden">
                        <div class="student-detail-tabs-scroll">
                            <ul class="nav nav-tabs student-detail-tabs border-0" id="studentDetailTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="sd-courses-tab" data-bs-toggle="tab" data-bs-target="#sd-courses" type="button" role="tab" aria-controls="sd-courses" aria-selected="true">
                                        <i class="fas fa-book me-1"></i>الكورسات
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="sd-cert-tab" data-bs-toggle="tab" data-bs-target="#sd-cert" type="button" role="tab" aria-controls="sd-cert" aria-selected="false">
                                        <i class="fas fa-certificate me-1"></i>الشهادات
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="sd-badges-tab" data-bs-toggle="tab" data-bs-target="#sd-badges" type="button" role="tab" aria-controls="sd-badges" aria-selected="false">
                                        <i class="fas fa-award me-1"></i>الأوسمة
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="sd-ach-tab" data-bs-toggle="tab" data-bs-target="#sd-ach" type="button" role="tab" aria-controls="sd-ach" aria-selected="false">
                                        <i class="fas fa-trophy me-1"></i>الإنجازات
                                    </button>
                                </li>
                                @if($student->bio)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="sd-about-tab" data-bs-toggle="tab" data-bs-target="#sd-about" type="button" role="tab" aria-controls="sd-about" aria-selected="false">
                                            <i class="fas fa-user me-1"></i>نبذة
                                        </button>
                                    </li>
                                @endif
                            </ul>
                        </div>

                        <div class="tab-content student-detail-tab-panels" id="studentDetailTabsContent">
                            <div class="tab-pane fade show active" id="sd-courses" role="tabpanel" aria-labelledby="sd-courses-tab" tabindex="0">
                                @if($enrollments && $enrollments->count() > 0)
                                    <div class="d-lg-none">
                                        @foreach($enrollments as $enrollment)
                                            <div class="glass-panel student-detail-mobile-enroll mb-3">
                                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <strong class="student-detail-mobile-course-title">{{ $enrollment->course->title ?? 'كورس محذوف' }}</strong>
                                                    @if($enrollment->enrollment_status == 'completed')
                                                        <span class="student-detail-pill student-detail-pill--success">مكتمل</span>
                                                    @elseif($enrollment->enrollment_status == 'active')
                                                        <span class="student-detail-pill student-detail-pill--primary">نشط</span>
                                                    @elseif($enrollment->enrollment_status == 'suspended')
                                                        <span class="student-detail-pill student-detail-pill--warn">متوقف</span>
                                                    @else
                                                        <span class="student-detail-pill">{{ $enrollment->enrollment_status }}</span>
                                                    @endif
                                                </div>
                                                <div class="student-detail-progress-wrap mb-2">
                                                    <div class="progress student-detail-progress">
                                                        <div class="progress-bar student-detail-progress-bar" role="progressbar" style="width: {{ min(100, (float) ($enrollment->completion_percentage ?? 0)) }}%" aria-valuenow="{{ (float) ($enrollment->completion_percentage ?? 0) }}" aria-valuemin="0" aria-valuemax="100">
                                                            {{ number_format((float) ($enrollment->completion_percentage ?? 0), 1) }}%
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="small student-detail-meta-line mb-2">
                                                    <i class="fas fa-calendar me-1"></i>{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : '—' }}
                                                </div>
                                                @if($enrollment->course && $enrollment->course->slug)
                                                    <a href="{{ route('frontend.courses.show', $enrollment->course->slug) }}" class="btn btn-sm student-detail-btn-outline w-100"><i class="fas fa-eye"></i> عرض الكورس</a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="table-responsive d-none d-lg-block">
                                        <table class="table table-hover student-detail-table align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>اسم الكورس</th>
                                                    <th>الحالة</th>
                                                    <th>نسبة الإنجاز</th>
                                                    <th>تاريخ التسجيل</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($enrollments as $enrollment)
                                                    <tr>
                                                        <td><strong>{{ $enrollment->course->title ?? 'كورس محذوف' }}</strong></td>
                                                        <td>
                                                            @if($enrollment->enrollment_status == 'completed')
                                                                <span class="student-detail-pill student-detail-pill--success">مكتمل</span>
                                                            @elseif($enrollment->enrollment_status == 'active')
                                                                <span class="student-detail-pill student-detail-pill--primary">نشط</span>
                                                            @elseif($enrollment->enrollment_status == 'suspended')
                                                                <span class="student-detail-pill student-detail-pill--warn">متوقف</span>
                                                            @else
                                                                <span class="student-detail-pill">{{ $enrollment->enrollment_status }}</span>
                                                            @endif
                                                        </td>
                                                        <td style="min-width: 140px;">
                                                            <div class="progress student-detail-progress" style="height: 10px;">
                                                                <div class="progress-bar student-detail-progress-bar" style="width: {{ min(100, (float) ($enrollment->completion_percentage ?? 0)) }}%"></div>
                                                            </div>
                                                            <span class="small student-detail-meta-line">{{ number_format((float) ($enrollment->completion_percentage ?? 0), 1) }}%</span>
                                                        </td>
                                                        <td>{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : '—' }}</td>
                                                        <td class="text-nowrap">
                                                            @if($enrollment->course && $enrollment->course->slug)
                                                                <a href="{{ route('frontend.courses.show', $enrollment->course->slug) }}" class="btn btn-sm student-detail-btn-outline"><i class="fas fa-eye"></i></a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="student-detail-empty text-center py-5">
                                        <i class="fas fa-book fa-3x mb-3" aria-hidden="true"></i>
                                        <p class="mb-0">لا توجد كورسات مسجّلة</p>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="sd-cert" role="tabpanel" aria-labelledby="sd-cert-tab" tabindex="0">
                                @if($certificates && $certificates->count() > 0)
                                    <div class="row g-3">
                                        @foreach($certificates as $certificate)
                                            <div class="col-md-6">
                                                <div class="glass-panel student-detail-cert-card h-100">
                                                    <div class="student-detail-cert-icon"><i class="fas fa-certificate"></i></div>
                                                    <div class="flex-grow-1">
                                                        <h3 class="h6 fw-bold mb-2" style="color: var(--clr-text);">{{ $certificate->course->title ?? 'كورس محذوف' }}</h3>
                                                        <p class="small student-detail-meta-line mb-3">
                                                            <i class="fas fa-calendar me-1"></i>{{ $certificate->completed_at ? $certificate->completed_at->format('Y-m-d') : '—' }}
                                                        </p>
                                                        @if($certificate->course && $certificate->course->slug)
                                                            <a href="{{ route('frontend.courses.show', $certificate->course->slug) }}" class="btn btn-sm btn-primary-custom" style="justify-content: center;"><i class="fas fa-external-link-alt"></i> صفحة الكورس</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="student-detail-empty text-center py-5">
                                        <i class="fas fa-certificate fa-3x mb-3" aria-hidden="true"></i>
                                        <p class="mb-0">لا توجد شهادات بعد</p>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="sd-badges" role="tabpanel" aria-labelledby="sd-badges-tab" tabindex="0">
                                @if($badges && $badges->count() > 0)
                                    <div class="row g-3">
                                        @foreach($badges as $badge)
                                            <div class="col-sm-6 col-lg-4">
                                                <div class="glass-panel student-detail-badge-card text-center h-100">
                                                    <div class="student-detail-badge-icon" style="background: {{ $badge->color ?? 'var(--clr-primary)' }};">
                                                        <i class="{{ $badge->icon ?? 'fas fa-award' }}"></i>
                                                    </div>
                                                    <h3 class="h6 fw-bold mt-2 mb-1" style="color: var(--clr-text);">{{ $badge->name }}</h3>
                                                    @if($badge->pivot->awarded_at)
                                                        <small class="student-detail-meta-line"><i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($badge->pivot->awarded_at)->format('Y-m-d') }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="student-detail-empty text-center py-5">
                                        <i class="fas fa-award fa-3x mb-3" aria-hidden="true"></i>
                                        <p class="mb-0">لا توجد أوسمة</p>
                                    </div>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="sd-ach" role="tabpanel" aria-labelledby="sd-ach-tab" tabindex="0">
                                @if($achievements && $achievements->count() > 0)
                                    <div class="row g-3">
                                        @foreach($achievements as $achievement)
                                            <div class="col-md-6">
                                                <div class="glass-panel student-detail-ach-card d-flex gap-3 h-100">
                                                    <div class="student-detail-ach-icon"><i class="{{ $achievement->icon ?? 'fas fa-trophy' }}"></i></div>
                                                    <div>
                                                        <h3 class="h6 fw-bold mb-1" style="color: var(--clr-text);">{{ $achievement->name }}</h3>
                                                        @if($achievement->description)
                                                            <p class="small student-detail-meta-line mb-2">{{ $achievement->description }}</p>
                                                        @endif
                                                        @if($achievement->pivot->completed_at)
                                                            <small class="student-detail-meta-line"><i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($achievement->pivot->completed_at)->format('Y-m-d') }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="student-detail-empty text-center py-5">
                                        <i class="fas fa-trophy fa-3x mb-3" aria-hidden="true"></i>
                                        <p class="mb-0">لا توجد إنجازات مكتملة</p>
                                    </div>
                                @endif
                            </div>

                            @if($student->bio)
                                <div class="tab-pane fade" id="sd-about" role="tabpanel" aria-labelledby="sd-about-tab" tabindex="0">
                                    <div class="student-detail-bio">
                                        <h3 class="student-detail-section-heading border-0 pb-0 mb-3"><i class="fas fa-user"></i> نبذة عني</h3>
                                        <div class="student-detail-bio-text">{{ $student->bio }}</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
