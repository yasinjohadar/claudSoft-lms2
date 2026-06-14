@extends('admin.layouts.master')

@section('page-title')
    جميع الدروس
@stop

@section('styles')
<style>
    html:not(.loaded) .lessons-page-animate {
        animation-play-state: paused !important;
        opacity: 0;
    }

    html.loaded .lessons-page-animate {
        animation-play-state: running !important;
    }

    .lessons-lesson-icon {
        width: 2rem;
        height: 2rem;
        min-width: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: rgba(var(--primary-rgb), 0.1);
        color: rgb(var(--primary-rgb));
        font-size: 0.9rem;
    }

    .lessons-time-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.28rem 0.6rem;
        border-radius: 999px;
        background: rgba(6, 182, 212, 0.12);
        color: #0891b2;
    }

    .lessons-status-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
    }

    .lessons-status-chip--published {
        background: rgba(25, 135, 84, 0.12);
        color: #198754;
    }

    .lessons-status-chip--draft {
        background: rgba(108, 117, 125, 0.14);
        color: #6c757d;
    }

    .lessons-table-row {
        transition: background-color 0.2s ease;
    }

    .lessons-actions__btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }

    .lessons-empty-state__icon {
        width: 3.5rem;
        height: 3.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: rgba(var(--primary-rgb), 0.08);
        color: rgb(var(--primary-rgb));
        font-size: 1.5rem;
        margin-bottom: 0.75rem;
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            @include('admin.components.alerts')

            <div class="my-4 page-header-breadcrumb lessons-page-animate dashboard-fade-in">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                        <li class="breadcrumb-item active">الدروس</li>
                    </ol>
                </nav>
            </div>

            <div class="group-show-hero dashboard-fade-in lessons-page-animate mb-4">
                <div class="row align-items-start g-3">
                    <div class="col-lg-7">
                        <span class="group-show-hero__eyebrow">
                            <i class="fe fe-book-open me-1"></i>
                            إدارة المحتوى التعليمي
                        </span>
                        <h2 class="group-show-hero__title mb-2">إدارة الدروس</h2>
                        <p class="group-show-hero__desc mb-0">
                            عرض وتصفية جميع الدروس عبر الكورسات، متابعة حالة النشر ووقت القراءة، والانتقال السريع للتعديل.
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="group-show-actions">
                            <button type="button"
                                    class="group-show-action group-show-action--primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#selectModuleModal">
                                <span class="group-show-action__icon"><i class="fe fe-plus"></i></span>
                                <span class="group-show-action__text">إضافة درس جديد</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 lessons-page-animate">
                @include('admin.pages.lessons.partials.stats', [
                    'totalLessons' => $totalLessons,
                    'publishedLessons' => $publishedLessons,
                    'totalReadingTime' => $totalReadingTime,
                ])
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in lessons-page-animate mb-4">
                <div class="card-header border-0 pb-0">
                    <h4 class="card-title mb-1">تصفية الدروس</h4>
                    <p class="fs-12 text-muted mb-0">ابحث في عنوان الدرس أو فلتر حسب الكورس وحالة النشر.</p>
                </div>
                <div class="card-body pt-3">
                    <form method="GET" action="{{ route('lessons.all') }}" class="group-show-filters mb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-xl-4 col-lg-4 col-md-6">
                                <label class="form-label" for="lessonsSearch">البحث</label>
                                <input type="text" id="lessonsSearch" name="search" class="form-control"
                                       placeholder="ابحث عن درس..."
                                       value="{{ request('search') }}">
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="lessonsCourse">الكورس</label>
                                <select name="course_id" id="lessonsCourse" class="form-select">
                                    <option value="">جميع الكورسات</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-xl-3 col-lg-4 col-md-6">
                                <label class="form-label" for="lessonsStatus">الحالة</label>
                                <select name="is_published" id="lessonsStatus" class="form-select">
                                    <option value="">جميع الحالات</option>
                                    <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>منشور</option>
                                    <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>مسودة</option>
                                </select>
                            </div>
                            <div class="col-xl-12">
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fe fe-search me-1"></i>بحث
                                    </button>
                                    <a href="{{ route('lessons.all') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fe fe-rotate-cw me-1"></i>إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card custom-card group-show-members-card dashboard-fade-in lessons-page-animate">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 border-0 pb-0">
                    <h6 class="group-show-members-card__title mb-0">
                        قائمة الدروس
                        <span class="group-show-members-card__count">{{ $lessons->total() }}</span>
                    </h6>
                </div>
                <div class="card-body pt-3">
                    @include('admin.pages.lessons._lessons_table', ['lessons' => $lessons])
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="selectModuleModal" tabindex="-1" aria-labelledby="selectModuleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectModuleModalLabel">
                        <i class="fe fe-plus-circle text-primary me-2"></i>اختر الموديول لإضافة درس
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="courseSelectForModule">الكورس</label>
                        <select id="courseSelectForModule" class="form-select" onchange="loadModules()">
                            <option value="">— اختر كورس أولاً —</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="moduleSelectContainer" style="display: none;">
                        <label class="form-label" for="moduleSelect">الموديول</label>
                        <select id="moduleSelect" class="form-select">
                            <option value="">— اختر موديول —</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="button" class="btn btn-primary" onclick="redirectToCreateLesson()">
                        <i class="fe fe-arrow-left me-1"></i>متابعة
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('script')
<script>
(function () {
    function initLessonsCountup() {
        document.querySelectorAll('[data-countup]').forEach(function (el) {
            const target = parseFloat(el.dataset.countup || '0');
            const duration = 800;
            const start = performance.now();

            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = new Intl.NumberFormat('ar-EG').format(Math.round(target * eased));
                if (progress < 1) {
                    requestAnimationFrame(step);
                }
            }

            requestAnimationFrame(step);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLessonsCountup);
    } else {
        initLessonsCountup();
    }
})();

function loadModules() {
    const courseId = document.getElementById('courseSelectForModule').value;
    const moduleContainer = document.getElementById('moduleSelectContainer');
    const moduleSelect = document.getElementById('moduleSelect');

    if (!courseId) {
        moduleContainer.style.display = 'none';
        moduleSelect.innerHTML = '<option value="">— اختر موديول —</option>';
        return;
    }

    fetch(`/admin/courses/${courseId}/modules`)
        .then(response => response.json())
        .then(data => {
            moduleSelect.innerHTML = '<option value="">— اختر موديول —</option>';

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
