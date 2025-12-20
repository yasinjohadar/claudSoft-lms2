@extends('admin.layouts.master')

@section('page-title')
    الدروس - {{ $module->title }}
@stop

@section('css')
<style>
    .lesson-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s;
        cursor: pointer;
    }
    .lesson-card:hover {
        border-color: #667eea;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.15);
    }
    .lesson-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    .lesson-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 16px;
        margin-bottom: 2rem;
    }
    .drag-handle {
        cursor: move;
        color: #adb5bd;
        font-size: 1.2rem;
    }
    .drag-handle:hover {
        color: #667eea;
    }
    .lesson-duration {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
        padding: 0.3rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        display: inline-block;
    }
    .sortable-ghost {
        opacity: 0.4;
        background: #f8f9fa;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">إدارة الدروس</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.show', $module->section->course_id) }}">{{ $module->section->course->title }}</a></li>
                            <li class="breadcrumb-item active">{{ $module->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('lessons.create', ['module' => $module->id]) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>إضافة درس جديد
                    </a>
                </div>
            </div>

            <!-- Module Info Header -->
            <div class="lesson-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3 class="mb-2">{{ $module->title }}</h3>
                        @if($module->description)
                            <p class="mb-0 opacity-90">{{ $module->description }}</p>
                        @endif
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="d-inline-block text-center me-4">
                            <div class="h2 mb-0">{{ $lessons->count() }}</div>
                            <small class="opacity-90">درس</small>
                        </div>
                        <div class="d-inline-block text-center">
                            <div class="h2 mb-0">{{ $totalDuration ?? 0 }}</div>
                            <small class="opacity-90">دقيقة</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lessons List -->
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">قائمة الدروس</h6>
                    <span class="badge bg-primary">{{ $lessons->count() }} درس</span>
                </div>
                <div class="card-body">
                    @if($lessons->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-5x text-muted mb-4 opacity-25"></i>
                            <h4 class="text-muted mb-3">لا توجد دروس بعد</h4>
                            <p class="text-muted">ابدأ بإضافة الدرس الأول لهذا الموديول</p>
                            <a href="{{ route('lessons.create', ['module' => $module->id]) }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>إضافة درس جديد
                            </a>
                        </div>
                    @else
                        <div id="lessonsList">
                            @foreach($lessons as $lesson)
                                <div class="lesson-card" data-id="{{ $lesson->id }}">
                                    <div class="row align-items-center">
                                        <!-- Drag Handle -->
                                        <div class="col-auto">
                                            <i class="fas fa-grip-vertical drag-handle"></i>
                                        </div>

                                        <!-- Icon -->
                                        <div class="col-auto">
                                            <div class="lesson-icon">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        </div>

                                        <!-- Lesson Info -->
                                        <div class="col">
                                            <h5 class="mb-1">{{ $lesson->title }}</h5>
                                            @if($lesson->description)
                                                <p class="text-muted mb-2" style="font-size: 0.9rem;">
                                                    {{ Str::limit($lesson->description, 100) }}
                                                </p>
                                            @endif
                                            <div class="d-flex gap-3 flex-wrap">
                                                @if($lesson->reading_time)
                                                    <span class="lesson-duration">
                                                        <i class="fas fa-clock me-1"></i>{{ $lesson->reading_time }} دقيقة
                                                    </span>
                                                @endif
                                                @if($lesson->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-secondary">مسودة</span>
                                                @endif
                                                @if($lesson->is_visible)
                                                    <span class="badge bg-info">مرئي</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="col-auto">
                                            <div class="btn-group">
                                                <a href="{{ route('lessons.edit', $lesson->id) }}"
                                                   class="btn btn-sm btn-light"
                                                   title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-light"
                                                        onclick="toggleVisibility({{ $lesson->id }})"
                                                        title="إخفاء/إظهار">
                                                    <i class="fas fa-eye{{ $lesson->is_visible ? '' : '-slash' }}"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-light"
                                                        onclick="duplicateLesson({{ $lesson->id }})"
                                                        title="نسخ">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="deleteLesson({{ $lesson->id }})"
                                                        title="حذف">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Save Order Button -->
                        <div class="text-center mt-4" id="saveOrderBtn" style="display: none;">
                            <button type="button" class="btn btn-primary" onclick="saveOrder()">
                                <i class="fas fa-save me-2"></i>حفظ الترتيب الجديد
                            </button>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    // Initialize Sortable
    const lessonsList = document.getElementById('lessonsList');
    if (lessonsList) {
        new Sortable(lessonsList, {
            animation: 150,
            handle: '.drag-handle',
            ghostClass: 'sortable-ghost',
            onEnd: function() {
                document.getElementById('saveOrderBtn').style.display = 'block';
            }
        });
    }

    // Save Order
    function saveOrder() {
        const lessons = document.querySelectorAll('.lesson-card');
        const order = Array.from(lessons).map((lesson, index) => ({
            id: lesson.dataset.id,
            order: index + 1
        }));

        fetch('{{ route("lessons.reorder", $module->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ order })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('saveOrderBtn').style.display = 'none';
                alert('تم حفظ الترتيب بنجاح');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ أثناء حفظ الترتيب');
        });
    }

    // Toggle Visibility
    function toggleVisibility(lessonId) {
        fetch(`/admin/lessons/${lessonId}/toggle-visibility`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ');
        });
    }

    // Duplicate Lesson
    function duplicateLesson(lessonId) {
        if (confirm('هل تريد نسخ هذا الدرس؟')) {
            fetch(`/admin/lessons/${lessonId}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء النسخ');
            });
        }
    }

    // Delete Lesson
    function deleteLesson(lessonId) {
        if (confirm('هل أنت متأكد من حذف هذا الدرس؟ لن يمكن التراجع عن هذا الإجراء.')) {
            fetch(`/admin/lessons/${lessonId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء الحذف');
            });
        }
    }
</script>
@stop
