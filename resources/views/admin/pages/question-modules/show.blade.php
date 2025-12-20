@extends('admin.layouts.master')

@section('page-title')
    {{ $questionModule->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div>
                    <h4 class="mb-0">{{ $questionModule->title }}</h4>
                    <nav>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('question-modules.index') }}">وحدات الأسئلة</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $questionModule->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('question-modules.manage-questions', $questionModule->id) }}" class="btn btn-primary">
                        <i class="fas fa-list me-1"></i>إدارة الأسئلة
                    </a>
                    <a href="{{ route('question-modules.edit', $questionModule->id) }}" class="btn btn-secondary">
                        <i class="fas fa-edit me-1"></i>تعديل
                    </a>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row">
                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-lg bg-primary-transparent">
                                        <i class="fas fa-question-circle fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-muted">عدد الأسئلة</span>
                                    </div>
                                    <h5 class="fw-semibold mb-0">{{ $stats['total_questions'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-lg bg-success-transparent">
                                        <i class="fas fa-chart-line fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-muted">إجمالي الدرجات</span>
                                    </div>
                                    <h5 class="fw-semibold mb-0">{{ $stats['total_grade'] }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <span class="avatar avatar-lg bg-warning-transparent">
                                        <i class="fas fa-book fs-4"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="text-muted">مستخدم في</span>
                                    </div>
                                    <h5 class="fw-semibold mb-0">{{ $stats['used_in_modules'] }} قسم</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Details -->
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">تفاصيل الوحدة</div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="200">العنوان</th>
                                            <td>{{ $questionModule->title }}</td>
                                        </tr>
                                        @if($questionModule->description)
                                        <tr>
                                            <th>الوصف</th>
                                            <td>{{ $questionModule->description }}</td>
                                        </tr>
                                        @endif
                                        @if($questionModule->instructions)
                                        <tr>
                                            <th>التعليمات</th>
                                            <td>{{ $questionModule->instructions }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>الوقت المحدد</th>
                                            <td>{{ $questionModule->time_limit ?? 'غير محدد' }} {{ $questionModule->time_limit ? 'دقيقة' : '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>المحاولات المسموحة</th>
                                            <td>{{ $questionModule->attempts_allowed }}</td>
                                        </tr>
                                        <tr>
                                            <th>نسبة النجاح</th>
                                            <td>{{ $questionModule->pass_percentage ?? 'غير محددة' }}{{ $questionModule->pass_percentage ? '%' : '' }}</td>
                                        </tr>
                                        <tr>
                                            <th>خلط الأسئلة</th>
                                            <td>
                                                @if($questionModule->shuffle_questions)
                                                    <span class="badge bg-success">مفعل</span>
                                                @else
                                                    <span class="badge bg-secondary">معطل</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>إظهار النتائج</th>
                                            <td>
                                                @if($questionModule->show_results)
                                                    <span class="badge bg-success">مفعل</span>
                                                @else
                                                    <span class="badge bg-secondary">معطل</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الحالة</th>
                                            <td>
                                                @if($questionModule->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-warning">مسودة</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>الظهور</th>
                                            <td>
                                                @if($questionModule->is_visible)
                                                    <span class="badge bg-success">ظاهر</span>
                                                @else
                                                    <span class="badge bg-secondary">مخفي</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Questions Preview -->
            @if($questionModule->questions->count() > 0)
            <div class="row">
                <div class="col-xl-12">
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">الأسئلة ({{ $questionModule->questions->count() }})</div>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                @foreach($questionModule->questions as $index => $question)
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">
                                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                {!! Str::limit(strip_tags($question->question_text), 100) !!}
                                            </h6>
                                            <small>
                                                <span class="badge bg-info-transparent">{{ $question->questionType->display_name }}</span>
                                                <span class="badge bg-success-transparent">{{ $question->pivot->question_grade }} نقطة</span>
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
@stop
