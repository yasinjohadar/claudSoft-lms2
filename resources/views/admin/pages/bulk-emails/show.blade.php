@extends('admin.layouts.master')

@section('page-title')
    تقرير حملة البريد #{{ $campaign->id }}
@stop

@section('content')
    <div class="main-content app-content admin-bulk-emails-page">
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

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.bulk-emails.index') }}">سجل الإرسال</a></li>
                        <li class="breadcrumb-item active">تقرير #{{ $campaign->id }}</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-bar-chart-2 me-1"></i>
                            تقرير الحملة
                        </span>
                        <h2 class="group-show-hero__title mb-2">حملة بريد #{{ $campaign->id }}</h2>
                        <p class="group-show-hero__desc mb-0">
                            @include('admin.pages.bulk-emails.partials.status-badge', ['status' => $campaign->status])
                            <span class="ms-2 text-muted">{{ $campaign->created_at->format('Y-m-d H:i') }}</span>
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions">
                            @if ($campaign->failed_count > 0)
                                <form action="{{ route('admin.bulk-emails.retry-failed', $campaign) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="group-show-action group-show-action--primary">
                                        <span class="group-show-action__icon"><i class="fe fe-refresh-cw"></i></span>
                                        <span class="group-show-action__text">إعادة إرسال الفاشل</span>
                                    </button>
                                </form>
                            @endif
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
                    ['variant' => 'blue', 'icon' => 'fe-users', 'label' => 'إجمالي المستلمين', 'value' => $campaign->total_recipients],
                    ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'تم الإرسال', 'value' => $campaign->sent_count],
                    ['variant' => 'orange', 'icon' => 'fe-x-circle', 'label' => 'فشل', 'value' => $campaign->failed_count],
                    ['variant' => 'cyan', 'icon' => 'fe-minus-circle', 'label' => 'تخطي', 'value' => $campaign->skipped_count],
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
                                    <h3 class="admin-stats-card__value mb-0">{{ $card['value'] }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-4 mb-4">
                <div class="col-lg-7">
                    <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-0">ملخص الحملة</h4>
                        </div>
                        <div class="card-body pt-3">
                            <table class="table table-borderless admin-bulk-emails-summary-table mb-0">
                                <tr>
                                    <th>نوع الجمهور</th>
                                    <td>
                                        @switch($campaign->audience_type)
                                            @case('individual') طالب واحد @break
                                            @case('selected') طلاب محددون @break
                                            @case('group') مجموعة كاملة @break
                                            @case('course') كورس كامل @break
                                            @case('course_group') تقاطع كورس + مجموعة @break
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <th>الكورس</th>
                                    <td>{{ $campaign->course?->title ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>المجموعة</th>
                                    <td>{{ $campaign->group?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>نوع المحتوى</th>
                                    <td>{{ $campaign->content_mode === 'template' ? 'قالب' : 'مخصص' }}</td>
                                </tr>
                                <tr>
                                    <th>القالب / الموضوع</th>
                                    <td>
                                        @if ($campaign->content_mode === 'template')
                                            {{ $campaign->emailTemplate?->name_ar ?: $campaign->emailTemplate?->name ?: '—' }}
                                        @else
                                            {{ $campaign->subject ?: '—' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>إعداد SMTP</th>
                                    <td>
                                        @if ($campaign->emailSetting)
                                            {{ $campaign->emailSetting->mail_from_address }}
                                        @else
                                            الإعداد النشط الافتراضي
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>أنشئت بواسطة</th>
                                    <td>{{ $campaign->creator?->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>بدء الإرسال</th>
                                    <td>{{ $campaign->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th>اكتمال الإرسال</th>
                                    <td>{{ $campaign->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card custom-card group-show-members-card dashboard-fade-in h-100">
                        <div class="card-header border-0 pb-0">
                            <h4 class="card-title mb-0">معاينة المحتوى</h4>
                        </div>
                        <div class="card-body pt-3">
                            @if ($campaign->content_mode === 'custom')
                                <p class="mb-2"><strong>الموضوع:</strong> {{ $campaign->subject }}</p>
                                <div class="border rounded p-3 bg-light overflow-auto" style="max-height: 320px;">
                                    {!! $campaign->body !!}
                                </div>
                            @else
                                <p class="text-muted mb-0">يتم توليد المحتوى من القالب المحدد لكل مستلم على حدة.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        تفاصيل المستلمين
                        <span class="group-show-members-card__count">{{ $recipients->total() }}</span>
                    </h6>
                    <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                        <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            <option value="">كل الحالات</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
                            <option value="sent" {{ $statusFilter === 'sent' ? 'selected' : '' }}>تم الإرسال</option>
                            <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>فشل</option>
                            <option value="skipped" {{ $statusFilter === 'skipped' ? 'selected' : '' }}>تخطي</option>
                        </select>
                    </form>
                </div>
                <div class="card-body pt-3">
                    @if ($recipients->isEmpty())
                        <p class="text-muted mb-0">لا توجد سجلات مستلمين.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>الاسم</th>
                                        <th>البريد</th>
                                        <th>الحالة</th>
                                        <th>الموضوع المرسل</th>
                                        <th>تاريخ الإرسال</th>
                                        <th>سبب الفشل / التخطي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recipients as $recipient)
                                        <tr>
                                            <td>{{ $recipient->user?->name_ar ?: $recipient->user?->name ?: '—' }}</td>
                                            <td>{{ $recipient->email ?: $recipient->user?->email ?: '—' }}</td>
                                            <td>
                                                @if ($recipient->status === 'sent')
                                                    <span class="badge bg-success">تم الإرسال</span>
                                                @elseif ($recipient->status === 'failed')
                                                    <span class="badge bg-danger">فشل</span>
                                                @elseif ($recipient->status === 'skipped')
                                                    <span class="badge bg-secondary">تخطي</span>
                                                @else
                                                    <span class="badge bg-warning">قيد الانتظار</span>
                                                @endif
                                            </td>
                                            <td class="small">{{ $recipient->rendered_subject ? Str::limit($recipient->rendered_subject, 60) : '—' }}</td>
                                            <td>{{ $recipient->sent_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                            <td class="small">{{ $recipient->error_message ? e(Str::limit($recipient->error_message, 120)) : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $recipients->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
