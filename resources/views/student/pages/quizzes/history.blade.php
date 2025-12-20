@extends('student.layouts.master')

@section('page-title')
    تاريخ الاختبار
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">تاريخ الاختبار - {{ $quiz->title ?? 'غير محدد' }}</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('student.quizzes.review.index') }}">مراجعة الاختبارات</a></li>
                            <li class="breadcrumb-item active">تاريخ الاختبار</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('student.quizzes.review.compare', $quiz->id ?? 0) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-chart-bar me-1"></i>مقارنة المحاولات
                    </a>
                </div>
            </div>

            <!-- إحصائيات -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-primary mb-2"><i class="fas fa-list fa-2x"></i></div>
                            <h4 class="fw-bold mb-1">{{ number_format($stats['total_attempts'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">إجمالي المحاولات</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-success mb-2"><i class="fas fa-trophy fa-2x"></i></div>
                            <h4 class="fw-bold mb-1 text-success">{{ number_format($stats['best_score'] ?? 0, 1) }}%</h4>
                            <p class="text-muted mb-0">أفضل نتيجة</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-info mb-2"><i class="fas fa-percentage fa-2x"></i></div>
                            <h4 class="fw-bold mb-1 text-info">{{ number_format($stats['average_score'] ?? 0, 1) }}%</h4>
                            <p class="text-muted mb-0">متوسط النتيجة</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card text-center border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-warning mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
                            <h4 class="fw-bold mb-1 text-warning">{{ number_format($stats['passed_count'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">محاولات ناجحة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- معلومات الاختبار -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2 text-info"></i>معلومات الاختبار</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>عنوان الاختبار:</strong> {{ $quiz->title ?? 'غير محدد' }}
                        </div>
                        @if($quiz->course)
                            <div class="col-md-6 mb-3">
                                <strong>الكورس:</strong> {{ $quiz->course->title }}
                            </div>
                        @endif
                        @if($quiz->description)
                            <div class="col-12 mb-3">
                                <strong>الوصف:</strong> {{ $quiz->description }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- قائمة المحاولات -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>جميع المحاولات</h5>
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
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attempts ?? [] as $attempt)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#{{ $attempt->attempt_number }}</span>
                                        </td>
                                        <td>
                                            {{ $attempt->started_at ? $attempt->started_at->format('Y/m/d H:i') : '-' }}
                                        </td>
                                        <td>
                                            @if($attempt->is_completed)
                                                <span class="fw-bold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($attempt->percentage_score ?? 0, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-muted">غير مكتمل</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attempt->is_completed)
                                                {{ number_format($attempt->time_spent ?? 0, 0) }} دقيقة
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attempt->is_completed)
                                                @if($attempt->passed)
                                                    <span class="badge bg-success">ناجح</span>
                                                @else
                                                    <span class="badge bg-danger">راسب</span>
                                                @endif
                                            @else
                                                <span class="badge bg-warning">قيد التنفيذ</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attempt->is_completed)
                                                <a href="{{ route('student.quizzes.review.show', $attempt->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i>مراجعة
                                                </a>
                                            @else
                                                <a href="{{ route('student.quizzes.take', $attempt->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-play me-1"></i>متابعة
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                            لا توجد محاولات حتى الآن
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



