@extends('student.layouts.master')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Alexandria:wght@500;600;700;800&family=Cairo:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-card-edit-page {
            font-family: 'Cairo', sans-serif;
        }
        .profile-card-edit-page h4,
        .profile-card-edit-page .card-title {
            font-family: 'Alexandria', sans-serif;
            font-weight: 700;
        }
        .profile-card-edit-hero {
            position: relative;
            overflow: hidden;
            border-radius: 0.75rem;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, rgba(0, 102, 179, 0.08) 0%, rgba(51, 153, 224, 0.04) 100%);
            border: 1px solid rgba(0, 102, 179, 0.12);
        }
        .profile-card-edit-hero__glow {
            position: absolute;
            top: -40%;
            left: -10%;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(51, 153, 224, 0.18), transparent 70%);
            pointer-events: none;
        }
        .profile-card-edit-hero__title {
            font-family: 'Alexandria', sans-serif;
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 0.35rem;
            color: var(--default-text-color, #1e293b);
        }
        .profile-card-edit-hero__text {
            font-size: 0.8125rem;
            color: var(--text-muted, #64748b);
            margin: 0;
            max-width: 36rem;
        }
        .profile-card-edit-snippet {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-radius: 0.65rem;
            background: rgba(var(--primary-rgb, 0, 102, 179), 0.04);
            border: 1px solid rgba(var(--primary-rgb, 0, 102, 179), 0.1);
        }
        .profile-card-edit-snippet__avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 14px rgba(0, 102, 179, 0.15);
            flex-shrink: 0;
        }
        .profile-card-edit-snippet__name {
            font-family: 'Alexandria', sans-serif;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.15rem;
        }
        .profile-card-edit-sidebar {
            position: sticky;
            top: 5.5rem;
        }
        .profile-card-edit-qr-box {
            display: inline-flex;
            padding: 0.75rem;
            border-radius: 0.65rem;
            background: #fff;
            border: 1px solid rgba(0, 102, 179, 0.12);
            box-shadow: 0 4px 18px rgba(0, 76, 138, 0.08);
        }
        .profile-card-edit-qr-box img {
            display: block;
            width: 160px;
            height: 160px;
        }
        .profile-card-edit-status {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        .profile-card-social-row {
            padding: 1rem;
            margin-bottom: 0.75rem;
            border-radius: 0.65rem;
            background: rgba(var(--primary-rgb, 0, 102, 179), 0.02);
            border: 1px solid rgba(var(--primary-rgb, 0, 102, 179), 0.1);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .profile-card-social-row:hover {
            border-color: rgba(var(--primary-rgb, 0, 102, 179), 0.2);
            box-shadow: 0 4px 14px rgba(0, 76, 138, 0.06);
        }
        .profile-card-social-row:last-child {
            margin-bottom: 0;
        }
        .profile-card-edit-empty {
            text-align: center;
            padding: 2rem 1rem;
            border-radius: 0.65rem;
            border: 1px dashed rgba(var(--primary-rgb, 0, 102, 179), 0.2);
            color: var(--text-muted, #64748b);
            background: rgba(var(--primary-rgb, 0, 102, 179), 0.02);
        }
        .profile-card-edit-empty i {
            font-size: 1.75rem;
            opacity: 0.45;
            display: block;
            margin-bottom: 0.5rem;
        }
        [data-theme-mode="dark"] .profile-card-edit-hero {
            background: linear-gradient(135deg, rgba(51, 153, 224, 0.12) 0%, rgba(0, 102, 179, 0.06) 100%);
            border-color: rgba(51, 153, 224, 0.15);
        }
        [data-theme-mode="dark"] .profile-card-edit-snippet,
        [data-theme-mode="dark"] .profile-card-social-row {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
        }
        [data-theme-mode="dark"] .profile-card-edit-qr-box {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.1);
        }
    </style>
@endpush

@section('page-title')
    بطاقتي التعريفية
@stop

@section('content')
<div class="main-content app-content student-profile-page profile-card-edit-page">
    <div class="container-fluid">
        @include('student.components.alerts')

        <div class="d-md-flex d-block align-items-center justify-content-between my-4">
            <div>
                <h4 class="student-my-courses-welcome__title mb-1">بطاقتي التعريفية</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item active">بطاقتي التعريفية</li>
                    </ol>
                </nav>
                <p class="text-muted mb-0 mt-2 fs-13">أنشئ بطاقة احترافية لمشاركتها مع الآخرين — بدون تسجيل دخول.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">
                <a href="{{ route('student.profile-card.preview') }}" target="_blank" class="btn btn-outline-primary rounded-pill btn-sm">
                    <i class="fe fe-eye me-1"></i>معاينة
                </a>
                @if($card->is_public)
                    <a href="{{ $publicUrl }}" target="_blank" class="btn btn-success rounded-pill btn-sm">
                        <i class="fe fe-external-link me-1"></i>البطاقة العامة
                    </a>
                @endif
            </div>
        </div>

        <div class="profile-card-edit-hero">
            <div class="profile-card-edit-hero__glow" aria-hidden="true"></div>
            <div class="position-relative d-md-flex align-items-center justify-content-between gap-3">
                <div>
                    <p class="profile-card-edit-hero__title mb-1">
                        <i class="fe fe-credit-card text-primary me-1"></i>
                        بطاقة رقمية قابلة للمشاركة
                    </p>
                    <p class="profile-card-edit-hero__text">
                        عدّل بياناتك وروابط التواصل ثم شارك الرابط أو رمز QR مع أي شخص — دون الحاجة لتسجيل دخول.
                    </p>
                </div>
                <a href="{{ route('student.profile-card.preview') }}" target="_blank" class="btn btn-primary rounded-pill btn-sm flex-shrink-0">
                    <i class="fe fe-maximize-2 me-1"></i>معاينة مباشرة
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8 order-2 order-xl-1">
                <form action="{{ route('student.profile-card.update') }}" method="POST" id="profileCardForm">
                    @csrf
                    @method('PUT')

                    <div class="card custom-card student-quizzes-panel mb-4">
                        <div class="card-body">
                            @include('student.pages.profile-card.partials.panel-header', [
                                'icon' => 'fe fe-user',
                                'title' => 'المعلومات الأساسية',
                                'subtitle' => 'الاسم والصورة من ملفك الشخصي',
                            ])

                            <div class="profile-card-edit-snippet mb-4">
                                <img src="{{ student_profile_photo_url($user) }}" alt="" class="profile-card-edit-snippet__avatar">
                                <div class="min-w-0">
                                    <div class="profile-card-edit-snippet__name">{{ $user->name_ar ?: $user->name }}</div>
                                    <div class="text-muted fs-12 mb-1">{{ $user->name }}</div>
                                    <a href="{{ route('student.profile.edit') }}" class="fs-12 text-primary">
                                        <i class="fe fe-edit-2 me-1"></i>تعديل الصورة والاسم
                                    </a>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">المسمى الوظيفي</label>
                                <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $card->job_title) }}" placeholder="مثال: مطور ويب Full Stack">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">نبذة عنك</label>
                                <textarea name="bio" class="form-control" rows="4" maxlength="2000" placeholder="اكتب نبذة مختصرة تعرّف بها عن نفسك...">{{ old('bio', $card->bio) }}</textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">الرابط المختصر (slug)</label>
                                <div class="input-group">
                                    <span class="input-group-text text-muted fs-12">{{ url('/card') }}/</span>
                                    <input type="text" name="slug" class="form-control" dir="ltr" value="{{ old('slug', $card->slug) }}" pattern="[a-z0-9]+(?:-[a-z0-9]+)*">
                                </div>
                                <small class="text-muted d-block mt-1">تغيير الرابط يعيد توليد QR تلقائياً.</small>
                                @error('slug')<div class="text-danger fs-12 mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card student-quizzes-panel mb-4">
                        <div class="card-body">
                            @include('student.pages.profile-card.partials.panel-header', [
                                'icon' => 'fe fe-share-2',
                                'title' => 'روابط التواصل',
                                'subtitle' => 'أضف حساباتك على الشبكات الاجتماعية',
                                'action' => '<button type="button" class="btn btn-sm btn-primary rounded-pill" id="addSocialLink"><i class="fe fe-plus me-1"></i>إضافة رابط</button>',
                            ])

                            <div id="socialLinksContainer">
                                @php $links = old('social_links', $card->social_links ?? []); @endphp
                                @forelse($links as $index => $link)
                                    @include('student.pages.profile-card.partials.social-link-row', ['index' => $index, 'link' => $link, 'socialPlatforms' => $socialPlatforms])
                                @empty
                                    <div class="profile-card-edit-empty" id="noSocialLinksMsg">
                                        <i class="fe fe-link" aria-hidden="true"></i>
                                        <p class="mb-0 fs-13">لا توجد روابط بعد. اضغط «إضافة رابط».</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="card custom-card student-quizzes-panel mb-4">
                        <div class="card-body">
                            @include('student.pages.profile-card.partials.panel-header', [
                                'icon' => 'fe fe-sliders',
                                'title' => 'المظهر والإعدادات',
                                'subtitle' => 'الثيم والظهور العام و QR',
                            ])

                            @php $theme = old('theme', $card->resolvedTheme()); @endphp
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">الثيم</label>
                                    <select name="theme[preset]" class="form-select" id="themePreset">
                                        @foreach($themes as $key => $meta)
                                            <option value="{{ $key }}" @selected(($theme['preset'] ?? 'classic') === $key)>{{ $meta['label'] ?? $key }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">لون التمييز</label>
                                    <input type="color" name="theme[accent_color]" class="form-control form-control-color w-100" value="{{ $theme['accent_color'] ?? '#3b82f6' }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_public" value="1" id="isPublicToggle" @checked(old('is_public', $card->is_public))>
                                        <label class="form-check-label" for="isPublicToggle">إظهار البطاقة للعامة</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="qr_enabled" value="1" @checked(old('qr_enabled', $card->qr_enabled))>
                                        <label class="form-check-label">إظهار QR على البطاقة</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fe fe-save me-1"></i>حفظ البطاقة
                    </button>
                </form>
            </div>

            <div class="col-xl-4 order-1 order-xl-2">
                <div class="profile-card-edit-sidebar">
                    <div class="card custom-card student-quizzes-panel mb-4">
                        <div class="card-body">
                            @include('student.pages.profile-card.partials.panel-header', [
                                'icon' => 'fe fe-link-2',
                                'title' => 'الرابط والـ QR',
                                'subtitle' => 'شارك بطاقتك بسهولة',
                            ])

                            <label class="form-label fs-12 text-muted">رابط المشاركة</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control fs-12" id="publicUrlInput" readonly dir="ltr" value="{{ $publicUrl }}">
                                <button type="button" class="btn btn-outline-primary" id="copyPublicUrlBtn">
                                    <i class="fe fe-copy"></i>
                                </button>
                            </div>

                            @if($qrUrl)
                                <div class="text-center">
                                    <div class="profile-card-edit-qr-box mb-2">
                                        <img src="{{ $qrUrl }}" alt="QR" id="qrPreviewImg">
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="regenerateQrBtn">
                                            <i class="fe fe-refresh-cw me-1"></i>إعادة توليد
                                        </button>
                                        <a href="{{ $qrUrl }}" download class="btn btn-sm btn-light border rounded-pill">
                                            <i class="fe fe-download me-1"></i>تحميل
                                        </a>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted fs-12 text-center mb-0">فعّل QR من الإعدادات ثم احفظ البطاقة.</p>
                            @endif

                            <div class="profile-card-edit-status">
                                <span class="badge rounded-pill bg-{{ $card->is_public ? 'success' : 'secondary' }}-transparent text-{{ $card->is_public ? 'success' : 'secondary' }}">
                                    <i class="fe fe-{{ $card->is_public ? 'eye' : 'eye-off' }} me-1"></i>
                                    {{ $card->is_public ? 'ظاهرة للعامة' : 'مخفية' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('student.pages.profile-card.partials.scripts')
@endpush
