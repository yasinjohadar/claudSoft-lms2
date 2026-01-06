@extends('student.layouts.master')

@section('page-title')
    اختباراتي
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('student.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">اختباراتي</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">اختباراتي</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-1"></i>الاختبارات المتاحة
                    </a>
                    <a href="{{ route('student.quizzes.review.analytics') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-line me-1"></i>التحليلات
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
                            <div class="text-success mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
                            <h4 class="fw-bold mb-1 text-success">{{ number_format($stats['passed_attempts'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">محاولات ناجحة</p>
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
                            <div class="text-warning mb-2"><i class="fas fa-clock fa-2x"></i></div>
                            <h4 class="fw-bold mb-1 text-warning">{{ number_format($stats['completed_attempts'] ?? 0) }}</h4>
                            <p class="text-muted mb-0">محاولات مكتملة</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.quizzes.review.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">حالة المحاولة</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الحالات</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>مكتملة</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>قيد التنفيذ</option>
                                <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>تم التسليم</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">النتيجة</label>
                            <select name="result" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع النتائج</option>
                                <option value="passed" {{ request('result') == 'passed' ? 'selected' : '' }}>ناجح</option>
                                <option value="failed" {{ request('result') == 'failed' ? 'selected' : '' }}>راسب</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">الاختبار</label>
                            <select name="quiz_id" class="form-select" onchange="this.form.submit()">
                                <option value="">جميع الاختبارات</option>
                                @if(isset($quizzes))
                                    @foreach($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}" {{ request('quiz_id') == $quiz->id ? 'selected' : '' }}>
                                            {{ $quiz->title }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <!-- قائمة المحاولات -->
            <div class="card custom-card border-0 shadow-sm">
                <div class="card-header bg-primary-transparent">
                    <div class="card-title mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>جميع المحاولات
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">الاختبار</th>
                                    <th>الكورس</th>
                                    <th>المحاولة</th>
                                    <th>تاريخ البدء</th>
                                    <th>تاريخ التسليم</th>
                                    <th>النتيجة</th>
                                    <th>الحالة</th>
                                    <th class="text-end pe-4">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attempts ?? [] as $attempt)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-md bg-primary-transparent rounded-circle">
                                                        <i class="fas fa-clipboard-check"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <strong class="d-block">{{ $attempt->quiz->title ?? 'غير محدد' }}</strong>
                                                    @if($attempt->quiz->description)
                                                        <small class="text-muted">{{ Str::limit($attempt->quiz->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($attempt->quiz->course)
                                                <span class="badge bg-info-transparent">
                                                    <i class="fas fa-book me-1"></i>{{ $attempt->quiz->course->title }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">المحاولة #{{ $attempt->attempt_number }}</span>
                                        </td>
                                        <td>
                                            @if($attempt->started_at)
                                                <div>
                                                    <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                                    <span>{{ $attempt->started_at->format('Y/m/d') }}</span>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $attempt->started_at->format('H:i') }}
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attempt->submitted_at)
                                                <div>
                                                    <i class="fas fa-calendar-check me-1 text-success"></i>
                                                    <span>{{ $attempt->submitted_at->format('Y/m/d') }}</span>
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $attempt->submitted_at->format('H:i') }}
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attempt->is_completed && $attempt->percentage_score !== null)
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 20px; width: 80px;">
                                                        <div class="progress-bar {{ $attempt->passed ? 'bg-success' : 'bg-danger' }}" 
                                                             style="width: {{ $attempt->percentage_score }}%">
                                                        </div>
                                                    </div>
                                                    <span class="fw-bold {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($attempt->percentage_score, 1) }}%
                                                    </span>
                                                </div>
                                                <small class="text-muted">
                                                    {{ number_format($attempt->score_obtained ?? 0, 1) }} / {{ number_format($attempt->max_score ?? 0, 1) }}
                                                </small>
                                            @else
                                                <span class="text-muted">غير مكتمل</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attempt->is_completed)
                                                @if($attempt->passed)
                                                    <span class="badge bg-success-transparent text-success border border-success">
                                                        <i class="fas fa-check-circle me-1"></i>ناجح
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-transparent text-danger border border-danger">
                                                        <i class="fas fa-times-circle me-1"></i>راسب
                                                    </span>
                                                @endif
                                            @elseif($attempt->status == 'in_progress')
                                                <span class="badge bg-warning-transparent text-warning border border-warning">
                                                    <i class="fas fa-spinner fa-spin me-1"></i>قيد التنفيذ
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-transparent text-secondary border border-secondary">
                                                    <i class="fas fa-clock me-1"></i>{{ ucfirst($attempt->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($attempt->is_completed)
                                                <a href="{{ route('student.quizzes.review.show', $attempt->id) }}" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i>مراجعة
                                                </a>
                                            @else
                                                <a href="{{ route('student.quizzes.take', $attempt->id) }}" 
                                                   class="btn btn-sm btn-warning">
                                                    <i class="fas fa-play me-1"></i>متابعة
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-inbox fa-4x text-muted opacity-50"></i>
                                            </div>
                                            <h5 class="mb-2">لا توجد محاولات حتى الآن</h5>
                                            <p class="text-muted mb-3">ابدأ بحل الاختبارات المتاحة لرؤية محاولاتك هنا</p>
                                            <a href="{{ route('student.quizzes.index') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i>تصفح الاختبارات المتاحة
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($attempts) && $attempts->hasPages())
                        <div class="card-footer">
                            {{ $attempts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop



