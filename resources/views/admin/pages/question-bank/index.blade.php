@extends('admin.layouts.master')

@section('page-title')
    بنك الأسئلة
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">بنك الأسئلة</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">بنك الأسئلة</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('question-bank.import.excel') }}" class="btn btn-success me-2">
                        <i class="fas fa-file-excel me-2"></i>استيراد من Excel
                    </a>
                    <a href="{{ route('question-bank.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة سؤال جديد
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
                                    <p class="mb-1 text-muted">إجمالي الأسئلة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questions->total() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-question-circle fs-18"></i>
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
                                    <p class="mb-1 text-muted">قابلة لإعادة الاستخدام</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questions->where('is_reusable', true)->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-recycle fs-18"></i>
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
                                    <p class="mb-1 text-muted">أنواع الأسئلة</p>
                                    <h3 class="mb-0 fw-semibold">{{ $questionTypes->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-list fs-18"></i>
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
                                    <p class="mb-1 text-muted">الكورسات</p>
                                    <h3 class="mb-0 fw-semibold">{{ $courses->count() }}</h3>
                                </div>
                                <div>
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-book fs-18"></i>
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
                    <form method="GET" action="{{ route('question-bank.index') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث في نص السؤال..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
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
                                <label class="form-label">نوع السؤال</label>
                                <select name="question_type_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    @foreach($questionTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('question_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">الصعوبة</label>
                                <select name="difficulty" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">الكل</option>
                                    <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>سهل</option>
                                    <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>متوسط</option>
                                    <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>صعب</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">لغة البرمجة</label>
                                <select name="language_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">جميع اللغات</option>
                                    @foreach($programmingLanguages as $lang)
                                        <option value="{{ $lang->id }}" {{ request('language_id') == $lang->id ? 'selected' : '' }}>
                                            {{ $lang->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-2"></i>بحث
                                </button>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <a href="{{ route('question-bank.index') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-redo me-2"></i>إعادة تعيين
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Questions Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">
                        قائمة الأسئلة ({{ $questions->total() }})
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="30%">السؤال</th>
                                    <th width="10%">النوع</th>
                                    <th width="12%">اللغات</th>
                                    <th width="10%">الكورس</th>
                                    <th width="8%">الصعوبة</th>
                                    <th width="6%">الدرجة</th>
                                    <th width="6%">الاستخدام</th>
                                    <th width="13%">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($questions as $question)
                                    <tr>
                                        <td>{{ $loop->iteration + ($questions->currentPage() - 1) * $questions->perPage() }}</td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 400px;" title="{{ $question->question_text }}">
                                                {{ $question->question_text }}
                                            </div>
                                            <small class="text-muted">
                                                <i class="fas fa-user fs-10 me-1"></i>{{ $question->creator->name ?? 'غير محدد' }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-transparent">
                                                <i class="{{ $question->questionType->icon ?? 'fas fa-question' }} me-1"></i>
                                                {{ $question->questionType->display_name }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($question->programmingLanguages->count() > 0)
                                                @foreach($question->programmingLanguages as $lang)
                                                    <span class="badge mb-1" style="background-color: {{ $lang->color ?? '#6c757d' }}; color: white;">
                                                        <i class="{{ $lang->icon ?? 'fas fa-code' }} me-1"></i>{{ $lang->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($question->course)
                                                <span class="badge bg-primary-transparent">
                                                    {{ $question->course->title }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-transparent">
                                                    عام
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($question->difficulty_level == 'easy')
                                                <span class="badge bg-success">سهل</span>
                                            @elseif($question->difficulty_level == 'medium')
                                                <span class="badge bg-warning">متوسط</span>
                                            @elseif($question->difficulty_level == 'hard')
                                                <span class="badge bg-danger">صعب</span>
                                            @else
                                                <span class="badge bg-dark">خبير</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $question->default_grade ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-purple-transparent" title="عدد مرات الاستخدام">
                                                {{ $question->quizQuestions()->count() }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('question-bank.show', $question->id) }}"
                                                   class="btn btn-sm btn-info" title="عرض">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('question-bank.edit', $question->id) }}"
                                                   class="btn btn-sm btn-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('question-bank.duplicate', $question->id) }}"
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-secondary" title="نسخ">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('question-bank.destroy', $question->id) }}"
                                                      method="POST" class="d-inline"
                                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">
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
                                        <td colspan="9" class="text-center py-5">
                                            <div class="mb-3">
                                                <i class="fas fa-question-circle fs-48 text-muted"></i>
                                            </div>
                                            <p class="text-muted fs-16 mb-3">لا توجد أسئلة في البنك</p>
                                            <a href="{{ route('question-bank.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>إضافة سؤال جديد
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($questions->hasPages())
                    <div class="card-footer">
                        {{ $questions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
@stop
