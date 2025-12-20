@extends('admin.layouts.master')

@section('page-title')
    جميع الدروس
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
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item active">الدروس</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#selectModuleModal">
                        <i class="fas fa-plus me-2"></i>إضافة درس جديد
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-book-reader fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">إجمالي الدروس</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalLessons }}</h4>
                                    <span class="badge bg-primary-transparent">في جميع الكورسات</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-check-circle fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">دروس منشورة</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $publishedLessons }}</h4>
                                    <span class="badge bg-success-transparent">منشورة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-clock fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <div class="d-flex justify-content-between">
                                        <p class="fw-semibold mb-1">وقت القراءة الإجمالي</p>
                                    </div>
                                    <h4 class="fw-bold mb-2">{{ $totalReadingTime }}</h4>
                                    <span class="badge bg-info-transparent">دقيقة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('lessons.all') }}">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">البحث</label>
                                <input type="text" name="search" class="form-control"
                                       placeholder="ابحث عن درس..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الكورس</label>
                                <select name="course_id" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">الحالة</label>
                                <select name="is_published" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_published') == '1' ? 'selected' : '' }}>منشور</option>
                                    <option value="0" {{ request('is_published') == '0' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('lessons.all') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lessons Table -->
            <div class="card custom-card">
                <div class="card-header">
                    <div class="card-title">قائمة الدروس</div>
                </div>
                <div class="card-body">
                    @if($lessons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap">
                                <thead>
                                    <tr>
                                        <th>عنوان الدرس</th>
                                        <th>الكورس</th>
                                        <th>الموديول</th>
                                        <th>وقت القراءة</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lessons as $lesson)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-sm bg-primary-transparent me-2">
                                                        <i class="fas fa-book"></i>
                                                    </span>
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ $lesson->title }}</h6>
                                                        @if($lesson->description)
                                                            <small class="text-muted">{{ Str::limit($lesson->description, 60) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($lesson->module && $lesson->module->section && $lesson->module->section->course)
                                                    <a href="{{ route('courses.show', $lesson->module->section->course_id) }}">
                                                        {{ $lesson->module->section->course->title }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($lesson->module)->title ?? '-' }}</td>
                                            <td>
                                                @if($lesson->reading_time)
                                                    <span class="badge bg-info-transparent">
                                                        <i class="fas fa-clock me-1"></i>{{ $lesson->reading_time }} دقيقة
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($lesson->is_published)
                                                    <span class="badge bg-success">منشور</span>
                                                @else
                                                    <span class="badge bg-secondary">مسودة</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('lessons.edit', $lesson->id) }}"
                                                       class="btn btn-sm btn-primary" title="تعديل">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $lessons->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted">لا توجد دروس</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Select Module Modal -->
    <div class="modal fade" id="selectModuleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">اختر الموديول لإضافة درس</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">الكورس</label>
                        <select id="courseSelectForModule" class="form-select" onchange="loadModules()">
                            <option value="">-- اختر كورس أولاً --</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="moduleSelectContainer" style="display: none;">
                        <label class="form-label">الموديول</label>
                        <select id="moduleSelect" class="form-select">
                            <option value="">-- اختر موديول --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="redirectToCreateLesson()">
                        <i class="fas fa-arrow-left me-2"></i>متابعة
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
    function loadModules() {
        const courseId = document.getElementById('courseSelectForModule').value;
        const moduleContainer = document.getElementById('moduleSelectContainer');
        const moduleSelect = document.getElementById('moduleSelect');

        if (!courseId) {
            moduleContainer.style.display = 'none';
            moduleSelect.innerHTML = '<option value="">-- اختر موديول --</option>';
            return;
        }

        // Fetch modules for the selected course
        fetch(`/admin/courses/${courseId}/modules`)
            .then(response => response.json())
            .then(data => {
                moduleSelect.innerHTML = '<option value="">-- اختر موديول --</option>';

                if (data.modules && data.modules.length > 0) {
                    data.modules.forEach(module => {
                        const option = document.createElement('option');
                        option.value = module.id;
                        option.textContent = module.title;
                        moduleSelect.appendChild(option);
                    });
                    moduleContainer.style.display = 'block';
                } else {
                    moduleContainer.style.display = 'none';
                    alert('لا توجد موديولات في هذا الكورس');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ أثناء تحميل الموديولات');
            });
    }

    function redirectToCreateLesson() {
        const moduleId = document.getElementById('moduleSelect').value;
        if (!moduleId) {
            alert('الرجاء اختيار موديول أولاً');
            return;
        }
        window.location.href = `/admin/lessons/create?module=${moduleId}`;
    }
</script>
@stop
