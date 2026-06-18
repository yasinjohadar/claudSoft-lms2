@extends('admin.layouts.master')

@section('page-title')
    إعدادات الإرسال الجماعي
@stop

@section('content')
    <div class="main-content app-content admin-bulk-emails-settings-page">
        <div class="container-fluid">

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fe fe-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-triangle me-2"></i>
                    <strong>يرجى تصحيح الأخطاء التالية:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                </div>
            @endif

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bulk-emails.index') }}">سجل الإرسال</a></li>
                        <li class="breadcrumb-item active">إعدادات الإرسال الجماعي</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-sliders me-1"></i>
                            التحكم في الإرسال
                        </span>
                        <h2 class="group-show-hero__title mb-2">إعدادات الإرسال الجماعي</h2>
                        <p class="group-show-hero__desc mb-0">
                            ضبط التأخير الذكي بين الرسائل، استراحات الدفعات، وحدود الحماية — كل الإعدادات من لوحة التحكم دون الحاجة لتعديل ملفات النظام.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            <a href="{{ route('admin.bulk-emails.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-send"></i></span>
                                <span class="group-show-action__text">إرسال بريد جديد</span>
                            </a>
                            <a href="{{ route('admin.bulk-emails.index') }}" class="group-show-action">
                                <span class="group-show-action__icon"><i class="fe fe-list"></i></span>
                                <span class="group-show-action__text">سجل الإرسال</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-mail', 'label' => 'مرسل اليوم', 'value' => $kpis['today_sent'], 'sub' => 'رسائل ناجحة'],
                    ['variant' => 'cyan', 'icon' => 'fe-loader', 'label' => 'حملات نشطة', 'value' => $kpis['active_campaigns'], 'sub' => 'قيد المعالجة'],
                    ['variant' => 'orange', 'icon' => 'fe-users', 'label' => 'حد المستلمين', 'value' => $kpis['max_recipients'] > 0 ? $kpis['max_recipients'] : '∞', 'sub' => 'لكل حملة'],
                    ['variant' => 'green', 'icon' => 'fe-shield', 'label' => 'الحد اليومي', 'value' => $kpis['daily_limit'] > 0 ? $kpis['daily_limit'] : '∞', 'sub' => 'رسالة / يوم'],
                ];
            @endphp

            <div class="row g-3 dashboard-fade-in mb-4">
                @foreach ($kpiCards as $index => $card)
                    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 dashboard-stagger-item" style="--stagger-delay: {{ $index * 70 }}ms">
                        <div class="card admin-stats-card admin-stats-card--{{ $card['variant'] }}">
                            <div class="card-body d-flex align-items-center gap-3">
                                <div class="admin-stats-card__icon-wrap">
                                    <i class="fe {{ $card['icon'] }} admin-stats-card__icon"></i>
                                </div>
                                <div class="admin-stats-card__content flex-fill min-w-0">
                                    <p class="admin-stats-card__label mb-1">{{ $card['label'] }}</p>
                                    <h3 class="admin-stats-card__value mb-1">{{ $card['value'] }}</h3>
                                    <p class="admin-stats-card__sub mb-0">{{ $card['sub'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form action="{{ route('admin.bulk-emails.settings.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-4 dashboard-fade-in">
                    <div class="col-lg-6">
                        <div class="card custom-card group-show-members-card h-100">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">التأخير بين الرسائل</h6>
                                <p class="fs-12 text-muted mb-0">الفاصل الزمني الأساسي قبل إرسال كل رسالة.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="mb-0">
                                    <label class="form-label fw-semibold" for="base_delay_seconds">التأخير الأساسي (ثانية)</label>
                                    <input type="number" name="base_delay_seconds" id="base_delay_seconds"
                                           class="form-control @error('base_delay_seconds') is-invalid @enderror"
                                           value="{{ old('base_delay_seconds', $settings['base_delay_seconds']) }}"
                                           min="0" max="300" required>
                                    @error('base_delay_seconds')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <p class="admin-bulk-emails-settings-hint mb-0 mt-2">يُضاف هذا التأخير قبل كل رسالة في قائمة الانتظار.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card custom-card group-show-members-card h-100">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">التباين العشوائي</h6>
                                <p class="fs-12 text-muted mb-0">إضافة تذبذب عشوائي لتجنب أنماط إرسال ثابتة.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="admin-bulk-emails-settings-toggle mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="random_delay_enabled" id="random_delay_enabled"
                                               value="1" {{ old('random_delay_enabled', $settings['random_delay_enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold" for="random_delay_enabled">تفعيل التباين العشوائي</label>
                                    </div>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="min_jitter_seconds">الحد الأدنى (ثانية)</label>
                                        <input type="number" name="min_jitter_seconds" id="min_jitter_seconds"
                                               class="form-control @error('min_jitter_seconds') is-invalid @enderror"
                                               value="{{ old('min_jitter_seconds', $settings['min_jitter_seconds']) }}"
                                               min="0" max="60" required>
                                        @error('min_jitter_seconds')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="max_jitter_seconds">الحد الأقصى (ثانية)</label>
                                        <input type="number" name="max_jitter_seconds" id="max_jitter_seconds"
                                               class="form-control @error('max_jitter_seconds') is-invalid @enderror"
                                               value="{{ old('max_jitter_seconds', $settings['max_jitter_seconds']) }}"
                                               min="0" max="120" required>
                                        @error('max_jitter_seconds')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card custom-card group-show-members-card h-100">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">استراحة الدفعات</h6>
                                <p class="fs-12 text-muted mb-0">إيقاف مؤقت بعد كل مجموعة من الرسائل.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="batch_size">حجم الدفعة (رسالة)</label>
                                        <input type="number" name="batch_size" id="batch_size"
                                               class="form-control @error('batch_size') is-invalid @enderror"
                                               value="{{ old('batch_size', $settings['batch_size']) }}"
                                               min="1" max="1000" required>
                                        @error('batch_size')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="batch_pause_seconds">مدة الاستراحة (ثانية)</label>
                                        <input type="number" name="batch_pause_seconds" id="batch_pause_seconds"
                                               class="form-control @error('batch_pause_seconds') is-invalid @enderror"
                                               value="{{ old('batch_pause_seconds', $settings['batch_pause_seconds']) }}"
                                               min="0" max="600" required>
                                        @error('batch_pause_seconds')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <p class="admin-bulk-emails-settings-hint mb-0 mt-3">تُضاف استراحة إضافية بعد كل {{ $settings['batch_size'] }} رسالة.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card custom-card group-show-members-card h-100">
                            <div class="card-header border-0 pb-0">
                                <h6 class="group-show-members-card__title mb-1">حدود الحماية</h6>
                                <p class="fs-12 text-muted mb-0">منع الإرسال المفرط وحماية سمعة الخادم.</p>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="max_recipients_per_campaign">حد المستلمين / حملة</label>
                                        <input type="number" name="max_recipients_per_campaign" id="max_recipients_per_campaign"
                                               class="form-control @error('max_recipients_per_campaign') is-invalid @enderror"
                                               value="{{ old('max_recipients_per_campaign', $settings['max_recipients_per_campaign']) }}"
                                               min="0" max="100000" required>
                                        @error('max_recipients_per_campaign')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <p class="admin-bulk-emails-settings-hint mb-0 mt-2">0 = بلا حد</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="daily_send_limit">الحد اليومي للإرسال</label>
                                        <input type="number" name="daily_send_limit" id="daily_send_limit"
                                               class="form-control @error('daily_send_limit') is-invalid @enderror"
                                               value="{{ old('daily_send_limit', $settings['daily_send_limit']) }}"
                                               min="0" max="1000000" required>
                                        @error('daily_send_limit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <p class="admin-bulk-emails-settings-hint mb-0 mt-2">0 = بلا حد</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4 dashboard-fade-in">
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-save me-1"></i>حفظ الإعدادات
                    </button>
                    <a href="{{ route('admin.bulk-emails.create') }}" class="btn btn-light">إلغاء</a>
                </div>
            </form>

        </div>
    </div>
@endsection
