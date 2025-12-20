@extends('admin.layouts.master')

@section('page-title')
    إدارة الاختبارات
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الاختبارات</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الاختبارات</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('quizzes.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة اختبار جديد
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">إجمالي الاختبارات</p>
                                    <h3 class="mb-0 fw-semibold">{{ $quizzes->total() }}</h3>
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
                <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">الاختبارات المنشورة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $quizzes->where('is_published', true)->count() }}</h3>
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
                                    <p class="mb-1 text-muted">المسودات</p>
                                    <h3 class="mb-0 fw-semibold">{{ $quizzes->where('is_published', false)->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-file fs-18"></i>
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
                                    <p class="mb-1 text-muted">بنك الأسئلة</p>
                                    <h3 class="mb-0 fw-semibold">-</h3>
                                    <a href="{{ route('question-bank.index') }}" class="text-primary fs-12">
                                        <i class="fas fa-arrow-left me-1"></i>عرض البنك
                                    </a>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-question-circle fs-18"></i>
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
                    <form method="GET" action="{{ route('quizzes.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث بعنوان الاختبار..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">نوع الاختبار</label>
                                <select name="quiz_type" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="practice" {{ request('quiz_type') == 'practice' ? 'selected' : '' }}>تدريبي</option>
                                    <option value="graded" {{ request('quiz_type') == 'graded' ? 'selected' : '' }}>مُقيّم</option>
                                    <option value="final_exam" {{ request('quiz_type') == 'final_exam' ? 'selected' : '' }}>اختبار نهائي</option>
                                    <option value="survey" {{ request('quiz_type') == 'survey' ? 'selected' : '' }}>استبيان</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الحالة</label>
                                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>منشور</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
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

            <!-- Quizzes Table -->
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">
                        قائمة الاختبارات ({{ $quizzes->total() }})
                    </div>
                    <div>
                        <a href="{{ route('question-bank.index') }}" class="btn btn-sm btn-info me-2">
                            <i class="fas fa-question-circle me-1"></i>بنك الأسئلة
                        </a>
                        <a href="{{ route('question-pools.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-layer-group me-1"></i>مجموعات الأسئلة
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">عنوان الاختبار</th>
                                    <th width="12%">الكورس</th>
                                    <th width="10%">النوع</th>
                                    <th width="8%">الأسئلة</th>
                                    <th width="8%">الدرجة</th>
                                    <th width="10%">المحاولات</th>
                                    <th width="10%">موعد الانتهاء</th>
                                    <th width="8%">الحالة</th>
                                    <th width="14%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quizzes as $quiz)
                                    <tr>
                                        <td>{{ $loop->iteration + ($quizzes->currentPage() - 1) * $quizzes->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <a href="{{ route('quizzes.show', $quiz->id) }}"
                                                       class="fw-semibold text-dark">{{ $quiz->title }}</a>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user fs-10 me-1"></i>
                                                        {{ $quiz->creator->name ?? 'غير محدد' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary-transparent">
                                                {{ $quiz->course->title }}
                                            </span>
                                            @if($quiz->lesson)
                                                <br>
                                                <small class="badge bg-info-transparent mt-1">
                                                    {{ $quiz->lesson->title }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($quiz->quiz_type == 'practice')
                                                <span class="badge bg-info">تدريبي</span>
                                            @elseif($quiz->quiz_type == 'graded')
                                                <span class="badge bg-warning">مُقيّم</span>
                                            @elseif($quiz->quiz_type == 'final_exam')
                                                <span class="badge bg-danger">نهائي</span>
                                            @else
                                                <span class="badge bg-secondary">استبيان</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-transparent">
                                                <i class="fas fa-question me-1"></i>
                                                {{ $quiz->getQuestionCount() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ number_format($quiz->max_score, 1) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('quizzes.show', $quiz->id) }}"
                                               class="badge bg-purple-transparent">
                                                {{ $quiz->attempts()->count() }} محاولة
                                            </a>
                                        </td>
                                        <td>
                                            @if($quiz->due_date)
                                                <small class="text-muted">
                                                    {{ $quiz->due_date->format('Y-m-d') }}
                                                    <br>
                                                    @if($quiz->due_date->isPast())
                                                        <span class="text-danger">
                                                            <i class="fas fa-clock"></i> منتهي
                                                        </span>
                                                    @else
                                                        <span class="text-success">
                                                            <i class="fas fa-clock"></i> نشط
                                                        </span>
                                                    @endif
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($quiz->is_published)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>منشور
                                                </span>
                                            @else
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-file me-1"></i>مسودة
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('quizzes.show', $quiz->id) }}"
                                                   class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('quizzes.edit', $quiz->id) }}"
                                                   class="btn btn-sm btn-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('quizzes.toggle-publish', $quiz->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-{{ $quiz->is_published ? 'warning' : 'success' }}"
                                                            title="{{ $quiz->is_published ? 'إلغاء النشر' : 'نشر' }}">
                                                        <i class="fas fa-{{ $quiz->is_published ? 'eye-slash' : 'check' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('quizzes.destroy', $quiz->id) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الاختبار؟')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-file-alt fs-48 text-muted"></i>
                                            </div>
                                            <p class="text-muted fs-16 mb-3">لا توجد اختبارات</p>
                                            <a href="{{ route('quizzes.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>إضافة اختبار جديد
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($quizzes->hasPages())
                    <div class="card-footer">
                        {{ $quizzes->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@stop
