@extends('admin.layouts.master')

@section('page-title')
    {{ $lesson->title }}
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $lesson->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('lessons.index') }}">الدروس</a></li>
                            <li class="breadcrumb-item active">{{ $lesson->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('lessons.edit', $lesson->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>تعديل
                    </a>
                    <a href="{{ route('lessons.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-right me-2"></i>العودة
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-xl-8">
                    <!-- Lesson Content -->
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title">
                                <i class="fas fa-book-open me-2 text-primary"></i>محتوى الدرس
                            </div>
                            <div>
                                @if($lesson->is_published)
                                    <span class="badge bg-success">منشور</span>
                                @else
                                    <span class="badge bg-warning">مسودة</span>
                                @endif

                                @if($lesson->is_visible)
                                    <span class="badge bg-info">مرئي</span>
                                @else
                                    <span class="badge bg-secondary">مخفي</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if($lesson->description)
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>الوصف
                                    </h6>
                                    <p class="mb-0">{{ $lesson->description }}</p>
                                </div>
                            @endif

                            <div class="lesson-content">
                                {!! $lesson->content !!}
                            </div>

                            @if($lesson->objectives)
                                <div class="mt-4">
                                    <h6 class="fw-bold">
                                        <i class="fas fa-bullseye me-2 text-success"></i>أهداف الدرس
                                    </h6>
                                    <div class="ms-4">
                                        {!! $lesson->objectives !!}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Attachments -->
                    @if($lesson->attachments && count($lesson->attachments) > 0)
                        <div class="card custom-card mt-4">
                            <div class="card-header">
                                <div class="card-title">
                                    <i class="fas fa-paperclip me-2 text-warning"></i>المرفقات
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    @foreach($lesson->attachments as $index => $attachment)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <i class="fas fa-file me-2"></i>
                                                {{ $attachment['name'] ?? 'مرفق ' . ($index + 1) }}
                                            </div>
                                            <a href="{{ route('lessons.attachments.download', ['id' => $lesson->id, 'attachmentId' => $index]) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-xl-4">
                    <!-- Lesson Info -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2"></i>معلومات الدرس
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="text-muted mb-1">وقت القراءة</label>
                                <div class="fw-semibold">
                                    <i class="fas fa-clock me-2 text-primary"></i>
                                    {{ $lesson->reading_time ?? 'غير محدد' }} دقيقة
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted mb-1">السماح بالتعليقات</label>
                                <div class="fw-semibold">
                                    @if($lesson->allow_comments)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>مفعل
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times me-1"></i>معطل
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted mb-1">ترتيب العرض</label>
                                <div class="fw-semibold">
                                    <i class="fas fa-sort me-2 text-info"></i>
                                    {{ $lesson->sort_order }}
                                </div>
                            </div>

                            @if($lesson->available_from)
                                <div class="mb-3">
                                    <label class="text-muted mb-1">متاح من</label>
                                    <div class="fw-semibold">
                                        <i class="fas fa-calendar-alt me-2 text-success"></i>
                                        {{ $lesson->available_from->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            @endif

                            @if($lesson->available_until)
                                <div class="mb-3">
                                    <label class="text-muted mb-1">متاح حتى</label>
                                    <div class="fw-semibold">
                                        <i class="fas fa-calendar-times me-2 text-danger"></i>
                                        {{ $lesson->available_until->format('Y-m-d H:i') }}
                                    </div>
                                </div>
                            @endif

                            <hr>

                            <div class="mb-3">
                                <label class="text-muted mb-1">تاريخ الإنشاء</label>
                                <div class="fw-semibold text-muted">
                                    {{ $lesson->created_at->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="text-muted mb-1">آخر تحديث</label>
                                <div class="fw-semibold text-muted">
                                    {{ $lesson->updated_at->format('Y-m-d H:i') }}
                                </div>
                            </div>

                            @if($lesson->creator)
                                <div class="mb-0">
                                    <label class="text-muted mb-1">أنشأ بواسطة</label>
                                    <div class="fw-semibold">
                                        {{ $lesson->creator->name }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card custom-card">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-cog me-2"></i>الإجراءات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <!-- Toggle Publish -->
                                <form action="{{ route('lessons.toggle-publish', $lesson->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $lesson->is_published ? 'warning' : 'success' }} w-100">
                                        <i class="fas fa-{{ $lesson->is_published ? 'eye-slash' : 'eye' }} me-2"></i>
                                        {{ $lesson->is_published ? 'إلغاء النشر' : 'نشر' }}
                                    </button>
                                </form>

                                <!-- Toggle Visibility -->
                                <form action="{{ route('lessons.toggle-visibility', $lesson->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-{{ $lesson->is_visible ? 'secondary' : 'info' }} w-100">
                                        <i class="fas fa-{{ $lesson->is_visible ? 'eye-slash' : 'eye' }} me-2"></i>
                                        {{ $lesson->is_visible ? 'إخفاء' : 'إظهار' }}
                                    </button>
                                </form>

                                <!-- Duplicate -->
                                <form action="{{ route('lessons.duplicate', $lesson->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-copy me-2"></i>تكرار الدرس
                                    </button>
                                </form>

                                <!-- Delete -->
                                <form action="{{ route('lessons.destroy', $lesson->id) }}" method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الدرس؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i>حذف
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('styles')
<style>
    .lesson-content {
        font-size: 1.1rem;
        line-height: 1.8;
        color: #333;
    }

    .lesson-content p {
        margin-bottom: 1rem;
    }

    .lesson-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }

    .lesson-content ul,
    .lesson-content ol {
        margin-bottom: 1rem;
        padding-right: 2rem;
    }

    .lesson-content h1,
    .lesson-content h2,
    .lesson-content h3,
    .lesson-content h4 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #2c3e50;
    }

    .lesson-content code {
        background-color: #f8f9fa;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
    }

    .lesson-content pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        overflow-x: auto;
    }
</style>
@endsection
