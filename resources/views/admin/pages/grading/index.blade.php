@extends('admin.layouts.master')

@section('page-title')
    لوحة التصحيح
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">لوحة التصحيح</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">التصحيح</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">بانتظار التصحيح</p>
                                    <h3 class="mb-0 fw-semibold text-danger">{{ $stats['pending_grading'] }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-danger-transparent">
                                        <i class="fas fa-exclamation-circle fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">مُصحح جزئياً</p>
                                    <h3 class="mb-0 fw-semibold text-warning">{{ $stats['partially_graded'] }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-tasks fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">مُصحح بالكامل (اليوم)</p>
                                    <h3 class="mb-0 fw-semibold text-success">{{ $stats['fully_graded'] }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">إجمالي المحاولات</p>
                                    <h3 class="mb-0 fw-semibold">{{ $attempts->total() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-file-alt fs-18"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('grading.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">البحث عن طالب</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث باسم الطالب أو البريد الإلكتروني..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الاختبار</label>
                                <select name="quiz_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع الاختبارات</option>
                                    @foreach($quizzes as $quiz)
                                        <option value="{{ $quiz->id }}" {{ request('quiz_id') == $quiz->id ? 'selected' : '' }}>
                                            {{ $quiz->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">حالة التصحيح</label>
                                <select name="grade_status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="not_graded" {{ request('grade_status') == 'not_graded' ? 'selected' : '' }}>لم يُصحح</option>
                                    <option value="partially_graded" {{ request('grade_status') == 'partially_graded' ? 'selected' : '' }}>مُصحح جزئياً</option>
                                    <option value="fully_graded" {{ request('grade_status') == 'fully_graded' ? 'selected' : '' }}>مُصحح بالكامل</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>بحث
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Attempts Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        قائمة المحاولات المُسلمة ({{ $attempts->total() }})
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">الطالب</th>
                                    <th width="20%">الاختبار</th>
                                    <th width="10%">المحاولة</th>
                                    <th width="12%">تاريخ التسليم</th>
                                    <th width="10%">الدرجة</th>
                                    <th width="13%">حالة التصحيح</th>
                                    <th width="10%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attempts as $attempt)
                                    <tr class="{{ $attempt->grade_status == 'not_graded' ? 'bg-danger-transparent' : '' }}">
                                        <td>{{ $loop->iteration + ($attempts->currentPage() - 1) * $attempts->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-primary-transparent me-2">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold">{{ $attempt->student->name }}</span>
                                                    <br>
                                                    <small class="text-muted">{{ $attempt->student->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">{{ $attempt->quiz->title }}</span>
                                            <br>
                                            <small class="badge bg-primary-transparent">{{ $attempt->quiz->course->title }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-transparent">#{{ $attempt->attempt_number }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $attempt->submitted_at ? $attempt->submitted_at->format('Y-m-d H:i') : '-' }}</small>
                                        </td>
                                        <td>
                                            @if($attempt->total_score !== null)
                                                <span class="badge bg-{{ $attempt->passed ? 'success' : 'danger' }}">
                                                    {{ number_format($attempt->percentage_score, 1) }}%
                                                </span>
                                                <br>
                                                <small class="text-muted">{{ number_format($attempt->total_score, 1) }}/{{ $attempt->max_score }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($attempt->grade_status == 'not_graded')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-exclamation-circle me-1"></i>لم يُصحح
                                                </span>
                                            @elseif($attempt->grade_status == 'partially_graded')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-tasks me-1"></i>جزئي
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>مُصحح
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('grading.show', $attempt->id) }}"
                                                   class="btn btn-sm btn-{{ $attempt->grade_status == 'not_graded' ? 'danger' : 'primary' }}"
                                                   title="تصحيح">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                                @if($attempt->grade_status == 'fully_graded')
                                                    <form action="{{ route('grading.regrade', $attempt->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning"
                                                                title="إعادة تصحيح"
                                                                onclick="return confirm('هل تريد إعادة تصحيح هذه المحاولة؟')">
                                                            <i class="fas fa-redo"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-inbox fs-48 text-muted"></i>
                                            </div>
                                            <p class="text-muted fs-16">لا توجد محاولات للتصحيح</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($attempts->hasPages())
                    <div class="card-footer">
                        {{ $attempts->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@stop
