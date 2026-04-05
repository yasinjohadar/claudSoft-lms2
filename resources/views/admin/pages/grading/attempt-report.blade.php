@extends('admin.layouts.master')

@section('page-title')
    تقرير إجابات المحاولة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            @include('admin.components.alerts')

            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb flex-wrap gap-2">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">تقرير إجابات المحاولة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('grading.index') }}">التصحيح</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('grading.show', $attempt->id) }}">تصحيح المحاولة</a></li>
                            <li class="breadcrumb-item active">تقرير الإجابات</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('grading.show', $attempt->id) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-edit me-1"></i>العودة للتصحيح
                    </a>
                    <a href="{{ route('grading.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-list me-1"></i>قائمة التصحيح
                    </a>
                </div>
            </div>

            <div class="card custom-card mb-4">
                <div class="card-header bg-primary-transparent">
                    <div class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>بيانات المحاولة</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>الاختبار:</strong> {{ $attempt->quiz?->title ?? '—' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>الطالب:</strong> {{ $attempt->student?->name ?? '—' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>البريد:</strong> {{ $attempt->student?->email ?? '—' }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>المحاولة رقم:</strong> #{{ $attempt->attempt_number }}
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>تاريخ التسليم:</strong> {{ $attempt->submitted_at ? $attempt->submitted_at->format('Y-m-d H:i') : '—' }}
                        </div>
                        @if($attempt->graded_at)
                            <div class="col-md-6 mb-2">
                                <strong>تاريخ التصحيح:</strong> {{ $attempt->graded_at->format('Y-m-d H:i') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card custom-card mb-4">
                <div class="card-header bg-info-transparent">
                    <div class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>النتيجة النهائية</div>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap gap-4 mb-3">
                                <div>
                                    <span class="text-muted d-block small">الدرجة المحصلة</span>
                                    <strong class="fs-4 text-primary">{{ number_format((float) ($attempt->total_score ?? 0), 2) }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted d-block small">من</span>
                                    <strong class="fs-5">{{ number_format((float) ($attempt->max_score ?? 0), 2) }}</strong>
                                </div>
                                <div>
                                    <span class="text-muted d-block small">النسبة المئوية</span>
                                    <strong class="fs-5 text-{{ $attempt->passed ? 'success' : 'danger' }}">{{ number_format((float) ($attempt->percentage_score ?? 0), 1) }}٪</strong>
                                </div>
                                <div>
                                    <span class="text-muted d-block small">الحالة</span>
                                    @if($attempt->passed)
                                        <span class="badge bg-success fs-6">ناجح</span>
                                    @else
                                        <span class="badge bg-danger fs-6">راسب</span>
                                    @endif
                                </div>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-{{ $attempt->passed ? 'success' : 'danger' }}"
                                     style="width: {{ min(100, (float) ($attempt->percentage_score ?? 0)) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card custom-card mb-4">
                <div class="card-header">
                    <div class="card-title mb-0"><i class="fas fa-list-ol me-2"></i>تفاصيل الأسئلة ({{ $responses->count() }})</div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:48px;">#</th>
                                    <th>السؤال</th>
                                    <th>النوع</th>
                                    <th>إجابة الطالب</th>
                                    <th>الإجابة الصحيحة</th>
                                    <th>درجة السؤال</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($responses as $idx => $response)
                                    @php
                                        $q = $response->question;
                                        $studentAns = $presenter->studentAnswerPlain($response);
                                        $correctAns = $presenter->correctAnswerPlain($q);
                                        $typeLabel = $response->questionType?->display_name ?? $q?->questionType?->display_name ?? '—';
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td>
                                            @if($q)
                                                <div class="mb-1">{!! nl2br(e(strip_tags($q->question_text))) !!}</div>
                                                @if(!empty($q->media_url) && ($q->media_type ?? '') === 'image')
                                                    <div class="small text-muted mb-1">صورة مرفقة بالسؤال</div>
                                                    <img src="{{ $q->media_url }}" alt="" class="img-fluid rounded border" style="max-width: 220px;">
                                                @elseif(!empty($q->question_image))
                                                    <div class="small text-muted">يحتوي السؤال على صورة</div>
                                                @endif
                                            @else
                                                <span class="text-warning">سؤال محذوف من البنك</span>
                                                <div class="small text-muted">معرّف السؤال: {{ $response->question_id }}</div>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info-transparent">{{ $typeLabel }}</span></td>
                                        <td class="small">{{ $studentAns }}</td>
                                        <td class="small">{{ $correctAns }}</td>
                                        <td class="text-nowrap">
                                            {{ $response->score_obtained !== null ? number_format((float) $response->score_obtained, 2) : '—' }}
                                            / {{ number_format((float) $response->max_score, 2) }}
                                        </td>
                                        <td>
                                            @if($response->score_obtained === null)
                                                <span class="badge bg-secondary">غير مصحح</span>
                                            @elseif($response->is_correct)
                                                <span class="badge bg-success">صحيح</span>
                                            @else
                                                <span class="badge bg-danger">خطأ</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
