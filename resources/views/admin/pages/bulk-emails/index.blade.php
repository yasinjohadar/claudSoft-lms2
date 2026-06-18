@extends('admin.layouts.master')

@section('page-title')
    سجل إرسال البريد الجماعي
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

            <div class="my-4 page-header-breadcrumb">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">سجل إرسال البريد</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-8">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-mail me-1"></i>
                            البريد الجماعي
                        </span>
                        <h2 class="group-show-hero__title mb-2">سجل حملات البريد</h2>
                        <p class="group-show-hero__desc mb-0">
                            متابعة حالة الإرسال، الإحصائيات، وتفاصيل كل حملة بريد جماعي.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="group-show-actions group-show-actions--single">
                            <a href="{{ route('admin.bulk-emails.create') }}" class="group-show-action group-show-action--primary">
                                <span class="group-show-action__icon"><i class="fe fe-send"></i></span>
                                <span class="group-show-action__text">إرسال بريد جديد</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $kpiCards = [
                    ['variant' => 'blue', 'icon' => 'fe-layers', 'label' => 'إجمالي الحملات', 'value' => $stats['total'] ?? 0, 'sub' => 'كل العمليات'],
                    ['variant' => 'green', 'icon' => 'fe-check-circle', 'label' => 'مكتملة', 'value' => $stats['completed'] ?? 0, 'sub' => 'انتهت بنجاح'],
                    ['variant' => 'cyan', 'icon' => 'fe-loader', 'label' => 'قيد المعالجة', 'value' => $stats['processing'] ?? 0, 'sub' => 'جاري الإرسال'],
                    ['variant' => 'orange', 'icon' => 'fe-alert-circle', 'label' => 'فاشلة', 'value' => $stats['failed'] ?? 0, 'sub' => 'فشلت بالكامل'],
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

            <div class="card custom-card group-show-members-card dashboard-fade-in">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الحملات
                        <span class="group-show-members-card__count">{{ $campaigns->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @if ($campaigns->isEmpty())
                        <p class="text-muted mb-0">لا توجد حملات بريد جماعي سابقة.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>التاريخ</th>
                                        <th>الجمهور</th>
                                        <th>المحتوى</th>
                                        <th>الإجمالي</th>
                                        <th>تم الإرسال</th>
                                        <th>فشل</th>
                                        <th>تخطي</th>
                                        <th>الحالة</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($campaigns as $campaign)
                                        <tr>
                                            <td>{{ $campaign->id }}</td>
                                            <td>{{ $campaign->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @switch($campaign->audience_type)
                                                    @case('individual') طالب واحد @break
                                                    @case('selected') طلاب محددون @break
                                                    @case('group') {{ $campaign->group?->name ?? 'مجموعة' }} @break
                                                    @case('course') {{ $campaign->course?->title ?? 'كورس' }} @break
                                                    @case('course_group') {{ $campaign->course?->title ?? '—' }} / {{ $campaign->group?->name ?? '—' }} @break
                                                @endswitch
                                            </td>
                                            <td>{{ $campaign->content_mode === 'template' ? ($campaign->emailTemplate?->name_ar ?: $campaign->emailTemplate?->name ?: 'قالب') : 'مخصص' }}</td>
                                            <td>{{ $campaign->total_recipients }}</td>
                                            <td><span class="text-success">{{ $campaign->sent_count }}</span></td>
                                            <td><span class="text-danger">{{ $campaign->failed_count }}</span></td>
                                            <td><span class="text-muted">{{ $campaign->skipped_count }}</span></td>
                                            <td>
                                                @include('admin.pages.bulk-emails.partials.status-badge', ['status' => $campaign->status])
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.bulk-emails.show', $campaign) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fe fe-eye me-1"></i>التقرير
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $campaigns->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@endsection
