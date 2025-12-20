@extends('student.layouts.master')

@section('page-title')
    مقارنة المحاولات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">مقارنة المحاولات - {{ $quiz->title ?? 'غير محدد' }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes.review.index') }}">مراجعة الاختبارات</a></li>
                            <li class="breadcrumb-item active">مقارنة المحاولات</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('student.quizzes.review.history', $quiz->id ?? 0) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-arrow-right me-1"></i>عرض التاريخ
                </a>
            </div>

            <!-- تحسين الأداء -->
            @if(isset($improvement) && count($improvement) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>تحسين الأداء</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-2">تغيير النتيجة</h6>
                                    <h4 class="fw-bold {{ ($improvement['score_change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($improvement['score_change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($improvement['score_change'] ?? 0, 1) }}%
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-2">تغيير الوقت</h6>
                                    <h4 class="fw-bold {{ ($improvement['time_change'] ?? 0) <= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($improvement['time_change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($improvement['time_change'] ?? 0, 0) }} دقيقة
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-2">تغيير الدقة</h6>
                                    <h4 class="fw-bold {{ ($improvement['accuracy_change'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ ($improvement['accuracy_change'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($improvement['accuracy_change'] ?? 0, 1) }}%
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- جدول المقارنة -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>مقارنة المحاولات</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>المحاولة</th>
                                    <th>التاريخ</th>
                                    <th>النتيجة</th>
                                    <th>الوقت المستغرق</th>
                                    <th>الإجابات الصحيحة</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($comparisonData ?? [] as $data)
                                    @php
                                        $attempt = $data['attempt'];
                                        $stats = $data['stats'];
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#{{ $attempt->attempt_number }}</span>
                                        </td>
                                        <td>
                                            {{ $attempt->started_at ? $attempt->started_at->format('Y/m/d H:i') : '-' }}
                                        </td>
                                        <td>
                                            <span class="fw-bold {{ $stats['passed'] ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($stats['percentage'] ?? 0, 1) }}%
                                            </span>
                                        </td>
                                        <td>{{ number_format($stats['time_spent'] ?? 0, 0) }} دقيقة</td>
                                        <td>
                                            <span class="badge bg-info">{{ $stats['correct_answers'] ?? 0 }}</span>
                                        </td>
                                        <td>
                                            @if($stats['passed'])
                                                <span class="badge bg-success">ناجح</span>
                                            @else
                                                <span class="badge bg-danger">راسب</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('student.quizzes.review.show', $attempt->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i>مراجعة
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد محاولات للمقارنة
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop



