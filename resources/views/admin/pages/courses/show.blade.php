@extends('admin.layouts.master')

@section('page-title')
    {{ $course->title }}
@stop

@section('css')
<style>
    .bg-danger-transparent {
        background: rgba(220, 53, 69, 0.1) !important;
    }

    
    /* Course Header Card */
    .course-header-card {
        background: white !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1) !important;
        margin-bottom: 2rem !important;
        overflow: hidden !important;
    }

    .course-header-top {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        padding: 2.5rem 2rem !important;
        color: white !important;
    }

    /* Stats Cards Grid */
    .stats-cards-grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 1.5rem !important;
        padding: 2rem !important;
        background: white !important;
    }

    @media (max-width: 992px) {
        .stats-cards-grid {
            grid-template-columns: repeat(2, 1fr) !important;
        }
    }

    @media (max-width: 576px) {
        .stats-cards-grid {
            grid-template-columns: 1fr !important;
        }
    }

    .stat-item-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%) !important;
        border-radius: 16px !important;
        padding: 2rem 1.5rem !important;
        text-align: center !important;
        border: 2px solid #e9ecef !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .stat-item-card::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        height: 4px !important;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%) !important;
    }

    .stat-item-card:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2) !important;
        border-color: #667eea !important;
    }

    .stat-icon-box {
        width: 64px !important;
        height: 64px !important;
        border-radius: 16px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin: 0 auto 1.25rem !important;
        font-size: 1.75rem !important;
    }

    .stat-icon-box.icon-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3) !important;
    }

    .stat-icon-box.icon-success {
        background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(86, 171, 47, 0.3) !important;
    }

    .stat-icon-box.icon-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3) !important;
    }

    .stat-icon-box.icon-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(240, 147, 251, 0.3) !important;
    }

    .stat-value {
        font-size: 2.25rem !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
        color: #1f2937 !important;
        margin-bottom: 0.5rem !important;
    }

    .stat-title {
        font-size: 0.95rem !important;
        color: #6b7280 !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }

    /* Tabs Styling */
    .course-tabs-wrapper {
        background: white !important;
        border-radius: 12px !important;
        padding: 1.5rem !important;
        margin-bottom: 2rem !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
    }

    .nav-tabs.custom-tabs {
        border-bottom: 2px solid #e9ecef !important;
        margin-bottom: 0 !important;
    }

    .nav-tabs.custom-tabs .nav-link {
        border: none !important;
        color: #6b7280 !important;
        padding: 1rem 2rem !important;
        font-weight: 600 !important;
        position: relative !important;
        transition: all 0.3s ease !important;
        background: transparent !important;
    }

    .nav-tabs.custom-tabs .nav-link:hover {
        color: #667eea !important;
        background: rgba(102, 126, 234, 0.05) !important;
    }

    .nav-tabs.custom-tabs .nav-link.active {
        color: #667eea !important;
        background: transparent !important;
    }

    .nav-tabs.custom-tabs .nav-link.active::after {
        content: '' !important;
        position: absolute !important;
        bottom: -2px !important;
        left: 0 !important;
        right: 0 !important;
        height: 3px !important;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%) !important;
        border-radius: 3px 3px 0 0 !important;
    }
    .section-card {
        background: white;
        border-radius: 10px;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        overflow: hidden;
        transition: all 0.3s;
    }
    .section-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* Section Header */
    .section-header-wrapper {
        display: flex;
        align-items: center;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    .section-header-main {
        flex-grow: 1;
        padding: 1rem 1.5rem;
        cursor: pointer;
        transition: background 0.2s;
    }
    .section-header-main:hover {
        background: #e9ecef;
    }
    .section-toggle-icon {
        transition: transform 0.3s;
        font-size: 0.875rem;
    }
    .section-header-main[aria-expanded="true"] .section-toggle-icon {
        transform: rotate(180deg);
    }

    /* Section Actions */
    .section-actions {
        display: flex;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-right: 1px solid #dee2e6;
    }

    /* Section Body */
    .section-body {
        padding: 1rem;
        background: #fafbfc;
    }

    /* Module Item */
    .module-item {
        display: flex;
        align-items: center;
        padding: 0.875rem 1rem;
        margin-bottom: 0.5rem;
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .module-item:hover {
        border-color: #667eea;
        box-shadow: 0 2px 6px rgba(102, 126, 234, 0.1);
    }
    .module-item:last-child {
        margin-bottom: 0;
    }

    /* Module Content */
    .module-content {
        flex-grow: 1;
    }
    .module-badges {
        display: flex;
        gap: 0.5rem;
        margin-right: 1rem;
    }

    /* Module Actions */
    .module-actions {
        display: flex;
        gap: 0.5rem;
    }
    .module-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-left: 1rem;
    }
    .action-btn-sm {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
    }
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #999;
    }
    .tab-custom {
        border-bottom: 2px solid #e9ecef;
        margin-bottom: 1.5rem;
    }
    .tab-custom .nav-link {
        border: none;
        color: #6c757d;
        padding: 1rem 1.5rem;
        font-weight: 600;
        position: relative;
    }
    .tab-custom .nav-link:hover {
        color: #667eea;
    }
    .tab-custom .nav-link.active {
        color: #667eea;
    }
    .tab-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    .add-section-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    /* Activity Styles */
    .activity-item {
        display: flex;
        align-items: center;
        gap: 2rem;
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
    }

    .activity-item .activity-icon {
        margin-left: 0;
    }

    .activity-item:hover {
        background: #e9ecef;
    }

    .activity-icon {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .icon-lesson { color: #0f6cbf; }
    .icon-video { color: #7c4dff; }
    .icon-assignment { color: #f57c00; }
    .icon-quiz { color: #e91e63; }
    .icon-resource { color: #43a047; }
    .icon-forum { color: #00897b; }
    .icon-programming_challenge { color: #5e35b1; }
    .icon-live_session { color: #d32f2f; }

    .activity-content {
        flex: 1;
    }

    .activity-link {
        color: #0f6cbf;
        text-decoration: none;
        font-weight: 500;
    }

    .activity-link:hover {
        text-decoration: underline;
    }

    .add-activity-link {
        display: block;
        margin-top: 1rem;
        padding: 0.75rem;
        text-align: center;
        border: 1px dashed #ced4da;
        border-radius: 4px;
        color: #6c757d;
        text-decoration: none;
    }

    .add-activity-link:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
    }

    /* Resource Modal Styles */
    .resource-source-option {
        position: relative;
        cursor: pointer;
        display: block;
    }
    
    .resource-source-option .form-check-input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .resource-source-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .resource-source-card:hover {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1);
    }
    
    .resource-source-option input:checked ~ .resource-source-card {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.05);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
</style>
@stop

@section('content')
    <div class="main-content app-content">
        <div class="container-fluid">

            <!-- Alerts -->
            @include('admin.components.alerts')

            <!-- Page Header -->
            <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
                <div class="my-auto">
                    <h5 class="page-title fs-21 mb-1">{{ $course->title }}</h5>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('courses.index') }}">الكورسات</a></li>
                            <li class="breadcrumb-item active">{{ $course->title }}</li>
                        </ol>
                    </nav>
                </div>
                <div class="mt-3 mt-md-0">
                    <div class="d-flex gap-2">
                        @if($course->category)
                            <span class="badge bg-primary-transparent px-3 py-2">{{ $course->category->name }}</span>
                        @endif
                        @if($course->is_published)
                            <span class="badge bg-success-transparent px-3 py-2">منشور</span>
                        @else
                            <span class="badge bg-warning-transparent px-3 py-2">مسودة</span>
                        @endif
                        @if($course->is_featured)
                            <span class="badge bg-warning-transparent px-3 py-2">
                                <i class="fas fa-star me-1"></i>مميز
                            </span>
                        @endif
                        <span id="course-visibility-badge" class="badge {{ $course->is_visible ? 'bg-info-transparent' : 'bg-secondary-transparent' }} px-3 py-2">
                            <i class="far fa-eye{{ $course->is_visible ? '' : '-slash' }} me-1"></i>
                            {{ $course->is_visible ? 'مرئي' : 'مخفي' }}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="course-toggle-visibility-btn"
                                onclick="toggleVisibility('course', {{ $course->id }})">
                            <i class="far fa-eye{{ $course->is_visible ? '' : '-slash' }} me-1"></i>
                            {{ $course->is_visible ? 'إخفاء' : 'إظهار' }}
                        </button>
                        <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>تعديل
                        </a>
                    </div>
                </div>
            </div>


            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-primary-transparent">
                                        <i class="fas fa-folder fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">إجمالي الأقسام</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['total_sections'] ?? 0 }}</h4>
                                    <span class="badge bg-primary-transparent">أقسام</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-success-transparent">
                                        <i class="fas fa-book-open fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">إجمالي الدروس</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['total_modules'] ?? 0 }}</h4>
                                    <span class="badge bg-success-transparent">دروس</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-info-transparent">
                                        <i class="fas fa-users fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">الطلاب المسجلين</p>
                                    <h4 class="fw-bold mb-2">{{ $stats['total_enrollments'] ?? 0 }}</h4>
                                    <span class="badge bg-info-transparent">طالب</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card custom-card">
                        <div class="card-body">
                            <div class="d-flex align-items-top">
                                <div class="me-3">
                                    <span class="avatar avatar-md bg-warning-transparent">
                                        <i class="fas fa-dollar-sign fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-fill">
                                    <p class="fw-semibold mb-1">سعر الكورس</p>
                                    <h4 class="fw-bold mb-2">${{ number_format($course->price ?? 0, 2) }}</h4>
                                    <span class="badge bg-warning-transparent">السعر</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Content Card -->
            <div class="card custom-card">
                <div class="card-header">
                    <ul class="nav nav-tabs nav-tabs-header mb-0" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#content" role="tab">
                                <i class="fas fa-book me-2"></i>المحتوى
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#enrollments" role="tab">
                                <i class="fas fa-users me-2"></i>التسجيلات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#settings" role="tab">
                                <i class="fas fa-cog me-2"></i>الإعدادات
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('courses.completion-summary', $course) }}">
                                <i class="fas fa-chart-bar me-2"></i>ملخص الإكمال
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <!-- Tab Content -->
                    <div class="tab-content">

                <!-- Content Tab -->
                <div class="tab-pane fade show active" id="content" role="tabpanel">

                    <!-- Add Section Button -->
                    <div class="text-center mb-4">
                        <a href="{{ route('courses.sections.create', $course->id) }}" class="add-section-btn">
                            <i class="fas fa-plus me-2"></i>إضافة قسم جديد
                        </a>
                    </div>

                    <!-- Bulk module restrictions -->
                    <div class="card custom-card mb-3 border-warning border-opacity-50" id="bulkModuleRestrictionsToolbar">
                        <div class="card-body py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="text-muted small" id="bulk-module-selected-count">0 وحدة محددة</span>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="bulk-module-select-all">
                                    تحديد الكل
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="bulk-module-deselect-all">
                                    إلغاء التحديد
                                </button>
                            </div>
                            <button type="button" class="btn btn-sm btn-warning" id="bulk-module-restrictions-btn" disabled>
                                <i class="fas fa-users-cog me-1"></i>قيود على المحدد
                            </button>
                        </div>
                    </div>

                    <!-- Sections Accordion -->
                    <div class="accordion accordion-customicon1 accordion-primary" id="sectionsAccordion">
                    @forelse($course->sections()->orderBy('order_index')->get() as $section)
                        <div class="accordion-item">
                            <h2 class="accordion-header d-flex align-items-stretch" id="heading-{{ $section->id }}">
                                <button class="accordion-button collapsed flex-grow-1" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#section-{{ $section->id }}"
                                        aria-expanded="false" aria-controls="section-{{ $section->id }}"
                                        style="border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                    <div class="d-flex align-items-center w-100 justify-content-between me-3">
                                        <div>
                                            <i class="fas fa-folder me-2"></i>
                                            {{ $section->title }}
                                            <span id="section-restrictions-badge-{{ $section->id }}" class="badge bg-warning text-dark ms-2" title="هذا القسم له قيود وصول" style="display: {{ $section->accessRestrictions && $section->accessRestrictions->count() > 0 ? 'inline-block' : 'none' }};">
                                                <i class="fas fa-lock me-1"></i>قيود
                                            </span>
                                            @if($section->description)
                                                <br><small class="text-muted fw-normal">{{ $section->description }}</small>
                                            @endif
                                        </div>
                                        <span id="section-modules-count-badge-{{ $section->id }}" class="badge bg-light text-default">
                                            {{ $section->modules->count() }} {{ $section->modules->count() == 1 ? 'درس' : 'دروس' }}
                                        </span>
                                    </div>
                                </button>
                                <a href="{{ route('sections.questions.manage', $section->id) }}"
                                   class="btn btn-success d-flex align-items-center px-3"
                                   style="border-radius: 0; border-top-left-radius: var(--bs-border-radius); border-bottom-left-radius: var(--bs-border-radius);"
                                   title="إدارة الأسئلة">
                                    <i class="fas fa-question-circle me-1"></i>أسئلة
                                </a>
                            </h2>
                            <div id="section-{{ $section->id }}" class="accordion-collapse collapse"
                                 aria-labelledby="heading-{{ $section->id }}" data-bs-parent="#sectionsAccordion">
                                <div class="accordion-body">
                                    <!-- Add Activity Buttons (Top) -->
                                    <div class="mb-3 p-3 bg-light rounded">
                                        <p class="text-muted mb-2 fw-semibold"><i class="fas fa-plus-circle me-2"></i>إضافة محتوى جديد:</p>
                                        <div class="d-flex gap-2 flex-wrap">
                                            <a href="{{ route('sections.modules.create', $section->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-book-open me-1"></i>درس
                                            </a>
                                            <a href="{{ route('videos.create', ['section_id' => $section->id, 'course_id' => $section->course_id]) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-play me-1"></i>فيديو
                                            </a>
                                            <a href="{{ route('assignments.create', ['section_id' => $section->id]) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-tasks me-1"></i>واجب
                                            </a>
                                            <a href="{{ route('quizzes.create', ['section_id' => $section->id]) }}" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-question-circle me-1"></i>اختبار
                                            </a>
                                            <a href="{{ route('question-modules.create', ['section_id' => $section->id]) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-clipboard-question me-1"></i>وحدة أسئلة
                                            </a>
                                            <a href="{{ route('sections.questions.manage', $section->id) }}" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-clipboard-question me-1"></i>أسئلة
                                            </a>
                                            <a href="{{ route('resources.create', ['section_id' => $section->id, 'course_id' => $section->course_id]) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file me-1"></i>مورد
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal" data-section-id="{{ $section->id }}" data-course-id="{{ $section->course_id }}">
                                                <i class="fas fa-file-plus me-1"></i>مورد (مودال)
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Section Header with Actions -->
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom flex-wrap gap-2">
                                        <div class="d-flex flex-wrap gap-2 align-items-center">
                                            <span class="text-muted small">هذا القسم فقط:</span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-section-select-all-modules" data-section-id="{{ $section->id }}" title="تحديد كل وحدات هذا القسم">
                                                تحديد كل الوحدات
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary js-section-deselect-all-modules" data-section-id="{{ $section->id }}" title="إلغاء تحديد وحدات هذا القسم">
                                                إلغاء تحديد الوحدات
                                            </button>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-warning manage-restrictions-btn"
                                                    data-type="section"
                                                    data-id="{{ $section->id }}"
                                                    data-title="{{ $section->title }}"
                                                    title="إدارة القيود للمجموعات">
                                                <i class="fas fa-users-cog me-1"></i>قيود المجموعات
                                            </button>
                                            <a href="{{ route('sections.questions.manage', $section->id) }}"
                                               class="btn btn-sm btn-outline-success"
                                               title="إدارة الأسئلة"
                                               onclick="event.stopPropagation();">
                                                <i class="fas fa-question-circle me-1"></i>الأسئلة
                                            </a>
                                            <a href="{{ route('courses.sections.edit', [$course->id, $section->id]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>تحرير
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary section-visibility-btn"
                                                    id="section-visibility-btn-{{ $section->id }}"
                                                    onclick="toggleVisibility('section', {{ $section->id }})">
                                                <i class="far fa-eye{{ $section->is_visible ? '' : '-slash' }} me-1"></i>
                                                {{ $section->is_visible ? 'إخفاء' : 'إظهار' }}
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteSectionModal{{ $section->id }}"
                                                    data-section-title="{{ $section->title }}">
                                                <i class="fas fa-trash me-1"></i>حذف
                                            </button>
                                        </div>
                                        <form id="delete-section-{{ $section->id }}"
                                              action="{{ route('courses.sections.destroy', [$course->id, $section->id]) }}"
                                              method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>

                                        <!-- Delete Section Modal -->
                                        <div class="modal fade" id="deleteSectionModal{{ $section->id }}" tabindex="-1" aria-labelledby="deleteSectionModalLabel{{ $section->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow-lg">
                                                    <div class="modal-body p-5">
                                                        <!-- Icon -->
                                                        <div class="text-center mb-4">
                                                            <span class="avatar avatar-xl bg-danger-transparent text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                                                <i class="fas fa-folder-minus fa-3x"></i>
                                                            </span>
                                                        </div>

                                                        <!-- Title -->
                                                        <h5 class="modal-title text-center mb-4 fw-bold" id="deleteSectionModalLabel{{ $section->id }}">
                                                            <i class="fas fa-exclamation-triangle me-2 text-warning"></i>
                                                            حذف القسم
                                                        </h5>

                                                        <!-- Message -->
                                                        <div class="alert alert-danger d-flex align-items-start mb-4" role="alert">
                                                            <i class="fas fa-exclamation-triangle me-2 fs-5 mt-1"></i>
                                                            <div>
                                                                <strong>هل أنت متأكد من حذف هذا القسم؟</strong>
                                                                <div class="mt-2">
                                                                    <span class="badge bg-primary fs-6">{{ $section->title }}</span>
                                                                </div>
                                                                <small class="text-muted d-block mt-2">
                                                                    <i class="fas fa-info-circle me-1"></i>
                                                                    سيتم حذف القسم مع جميع المحتوى المرتبط به ولا يمكن التراجع عن هذه العملية.
                                                                </small>
                                                                @if($section->modules()->count() > 0)
                                                                    <small class="text-danger d-block mt-2">
                                                                        <i class="fas fa-warning me-1"></i>
                                                                        <strong>تحذير:</strong> هذا القسم يحتوي على {{ $section->modules()->count() }} {{ $section->modules()->count() == 1 ? 'درس' : 'دروس' }}. سيتم حذفها أيضاً.
                                                                    </small>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Actions -->
                                                        <div class="d-flex justify-content-center gap-3 mt-4">
                                                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                                                                <i class="fas fa-times me-2"></i>إلغاء
                                                            </button>
                                                            <button type="button" class="btn btn-danger px-4" onclick="document.getElementById('delete-section-{{ $section->id }}').submit();">
                                                                <i class="fas fa-trash-alt me-2"></i>حذف نهائياً
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Activities List -->
                                    @forelse($section->modules()->orderBy('sort_order')->get() as $module)
                                        <div id="module-container-{{ $module->id }}" class="mb-3 border rounded" style="transition: all 0.3s ease;">
                                            <div class="d-flex align-items-center justify-content-between p-3">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <div class="form-check me-2 flex-shrink-0 align-self-center">
                                                        <input type="checkbox" class="form-check-input js-module-bulk-check" value="{{ $module->id }}" id="module-bulk-check-{{ $module->id }}" data-section-id="{{ $section->id }}" title="تحديد للقيود الجماعية" aria-label="تحديد الوحدة">
                                                    </div>
                                                    <span class="avatar avatar-md me-3
                                                        {{ $module->module_type == 'lesson' ? 'bg-primary-transparent text-primary' : '' }}
                                                        {{ $module->module_type == 'video' ? 'bg-danger-transparent text-danger' : '' }}
                                                        {{ $module->module_type == 'quiz' ? 'bg-success-transparent text-success' : '' }}
                                                        {{ $module->module_type == 'assignment' ? 'bg-warning-transparent text-warning' : '' }}
                                                        {{ $module->module_type == 'question_module' ? 'bg-info-transparent text-info' : '' }}">
                                                        @if($module->module_type == 'lesson')
                                                            <i class="fas fa-book-open"></i>
                                                        @elseif($module->module_type == 'video')
                                                            <i class="fas fa-play"></i>
                                                        @elseif($module->module_type == 'quiz')
                                                            <i class="fas fa-question-circle"></i>
                                                        @elseif($module->module_type == 'assignment')
                                                            <i class="fas fa-tasks"></i>
                                                        @elseif($module->module_type == 'question_module')
                                                            <i class="fas fa-clipboard-question"></i>
                                                        @else
                                                            <i class="fas fa-file"></i>
                                                        @endif
                                                    </span>
                                                    <div>
                                                        @php
                                                            // أسماء المجموعات المرتبطة بقيود هذه الوحدة
                                                            $groupNames = $module->accessRestrictions && $module->accessRestrictions->count() > 0
                                                                ? $module->accessRestrictions
                                                                    ->pluck('group.name')
                                                                    ->filter()
                                                                    ->unique()
                                                                    ->values()
                                                                : collect();
                                                            $displayGroups = $groupNames->take(3);
                                                            $moreCount = max($groupNames->count() - $displayGroups->count(), 0);
                                                            $hasRestrictions = $module->accessRestrictions && $module->accessRestrictions->count() > 0;
                                                            
                                                            // التحقق من أن المورد هو رابط
                                                            $isResourceUrl = false;
                                                            if ($module->module_type == 'resource' && $module->modulable) {
                                                                $isResourceUrl = $module->modulable->resource_source == 'url';
                                                            }
                                                        @endphp
                                                        <h6 class="mb-1 fw-semibold text-dark d-flex align-items-center gap-2 flex-wrap flex-row-reverse justify-content-end">
                                                            <span>{{ $module->title }}</span>
                                                            @if($isResourceUrl)
                                                                <i class="fas fa-link text-info" title="رابط خارجي"></i>
                                                            @endif
                                                            <span class="badge bg-secondary-transparent text-secondary">#{{ $loop->iteration }}</span>
                                                        </h6>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <span id="module-main-badge-{{ $module->id }}" class="badge bg-warning text-dark" style="display: {{ $hasRestrictions ? 'inline-block' : 'none' }};"
                                                                  @if($hasRestrictions && $groupNames->isNotEmpty())
                                                                      title="هذه الوحدة مقيدة على المجموعات: {{ $groupNames->implode('، ') }}"
                                                                  @elseif($hasRestrictions)
                                                                      title="هذه الوحدة لها قيود وصول"
                                                                  @endif
                                                            >
                                                                <i class="fas fa-lock me-1"></i>قيود
                                                            </span>
                                                            <span id="module-groups-container-{{ $module->id }}">
                                                                @if($hasRestrictions && $displayGroups->isNotEmpty())
                                                                    @foreach($displayGroups as $index => $groupName)
                                                                        <span class="badge bg-primary-transparent text-primary module-group-badge" data-module-id="{{ $module->id }}" data-group-name="{{ $groupName }}">
                                                                            <i class="fas fa-users me-1"></i>{{ $groupName }}
                                                                        </span>
                                                                    @endforeach
                                                                    @if($moreCount > 0)
                                                                        <span class="badge bg-light text-muted" id="module-more-badge-{{ $module->id }}">
                                                                            +{{ $moreCount }}
                                                                        </span>
                                                                    @endif
                                                                @endif
                                                            </span>
                                                        </div>
                                                        <small class="text-muted">
                                                            <span class="badge bg-light text-default me-1">
                                                                @if($module->module_type == 'lesson') درس
                                                                @elseif($module->module_type == 'video') فيديو
                                                                @elseif($module->module_type == 'quiz') اختبار
                                                                @elseif($module->module_type == 'assignment') واجب
                                                                @elseif($module->module_type == 'question_module') وحدة أسئلة
                                                                @endif
                                                            </span>
                                                            @if($module->module_type == 'question_module' && $module->modulable)
                                                                <span class="badge bg-info-transparent text-info badge-sm ms-1">
                                                                    {{ $module->modulable->questions->count() }} سؤال
                                                                </span>
                                                                @if($module->modulable->getTotalGrade() > 0)
                                                                    <span class="badge bg-success-transparent text-success badge-sm ms-1">
                                                                        {{ $module->modulable->getTotalGrade() }} نقطة
                                                                    </span>
                                                                @endif
                                                            @endif
                                                            <span id="module-visibility-badge-{{ $module->id }}" class="badge badge-sm ms-1 {{ $module->is_visible ? 'bg-success text-white' : 'bg-secondary' }}">
                                                                {{ $module->is_visible ? 'ظاهر' : 'مخفي' }}
                                                            </span>
                                                            @if($module->is_required)
                                                                <i class="fas fa-asterisk text-danger ms-1" style="font-size: 8px;" title="مطلوب"></i>
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-warning manage-restrictions-btn"
                                                        data-type="module"
                                                        data-id="{{ $module->id }}"
                                                        data-title="{{ $module->title }}"
                                                        title="إدارة القيود للمجموعات">
                                                    <i class="fas fa-users-cog me-1"></i>قيود
                                                </button>
                                                <a href="{{ route('courses.modules.completions', ['course' => $course->id, 'module' => $module->id]) }}"
                                                   class="btn btn-sm btn-outline-success"
                                                   title="تقدم وإكمال الطلاب لهذه الوحدة">
                                                    <i class="fas fa-user-check me-1"></i>تقدم الطلاب
                                                </a>
                                                @if($module->module_type == 'assignment' && $module->modulable_id)
                                                    <a href="{{ route('assignments.show', $module->modulable_id) }}"
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye me-1"></i>معاينة
                                                    </a>
                                                @elseif($module->module_type == 'quiz' && $module->modulable_id)
                                                    <a href="{{ route('quizzes.show', $module->modulable_id) }}"
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye me-1"></i>معاينة
                                                    </a>
                                                @elseif($module->module_type == 'question_module' && $module->modulable_id)
                                                    <a href="{{ route('question-modules.show', $module->modulable_id) }}"
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye me-1"></i>معاينة
                                                    </a>
                                                @else
                                                    <a href="{{ route('sections.modules.show', [$section->id, $module->id]) }}"
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye me-1"></i>معاينة
                                                    </a>
                                                @endif
                                                @if($module->module_type == 'assignment' && $module->modulable_id)
                                                    <a href="{{ route('assignments.edit', $module->modulable_id) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit me-1"></i>تحرير
                                                    </a>
                                                @elseif($module->module_type == 'quiz' && $module->modulable_id)
                                                    <a href="{{ route('quizzes.edit', $module->modulable_id) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit me-1"></i>تحرير
                                                    </a>
                                                @elseif($module->module_type == 'question_module' && $module->modulable_id)
                                                    <a href="{{ route('question-modules.manage-questions', $module->modulable_id) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit me-1"></i>تحرير
                                                    </a>
                                                @elseif($module->module_type == 'video' && $module->modulable_id)
                                                    {{-- زر تعديل الفيديو مباشرة --}}
                                                    <a href="{{ route('videos.edit', $module->modulable_id) }}"
                                                       class="btn btn-sm btn-outline-warning"
                                                       title="تعديل الفيديو مباشرة">
                                                        <i class="fas fa-video me-1"></i>تعديل الفيديو
                                                    </a>
                                                    {{-- زر تعديل الوحدة --}}
                                                    <a href="{{ route('sections.modules.edit', [$section->id, $module->id]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit me-1"></i>تحرير الوحدة
                                                    </a>
                                                @else
                                                    <a href="{{ route('sections.modules.edit', [$section->id, $module->id]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit me-1"></i>تحرير
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-secondary module-visibility-btn"
                                                        id="module-visibility-btn-{{ $module->id }}"
                                                        onclick="toggleVisibility('module', {{ $module->id }})">
                                                    <i class="far fa-eye{{ $module->is_visible ? '' : '-slash' }} me-1"></i>
                                                    {{ $module->is_visible ? 'إخفاء' : 'إظهار' }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-module-btn"
                                                        id="delete-module-btn-{{ $module->id }}"
                                                        data-section-id="{{ $section->id }}"
                                                        data-module-id="{{ $module->id }}"
                                                        data-module-title="{{ $module->title }}">
                                                    <i class="fas fa-trash me-1"></i>حذف
                                                </button>
                                            </div>
                                        </div>

                                        @if($module->module_type == 'question_module' && $module->modulable && $module->modulable->questions->count() > 0)
                                            <!-- Questions List for Question Module -->
                                            <div class="border-top bg-light p-3">
                                                <h6 class="mb-3 text-muted">
                                                    <i class="fas fa-list me-2"></i>الأسئلة ({{ $module->modulable->questions->count() }})
                                                </h6>
                                                <div class="list-group">
                                                    @foreach($module->modulable->questions as $index => $question)
                                                        <div class="list-group-item d-flex justify-content-between align-items-start py-2 px-3">
                                                            <div class="flex-grow-1">
                                                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                                <span class="text-dark">
                                                                    {!! Str::limit(strip_tags($question->question_text), 80) !!}
                                                                </span>
                                                            </div>
                                                            <div class="text-end" style="min-width: 150px;">
                                                                <span class="badge bg-info-transparent text-info me-1">
                                                                    {{ $question->questionType->display_name }}
                                                                </span>
                                                                <span class="badge bg-success-transparent text-success">
                                                                    {{ $question->pivot->question_grade }} نقطة
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                        <form id="delete-form-{{ $module->id }}"
                                              action="{{ route('sections.modules.destroy', [$section->id, $module->id]) }}"
                                              method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @empty
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-inbox fs-3 mb-2 opacity-25"></i>
                                            <p class="mb-0">لا توجد دروس في هذا القسم</p>
                                        </div>
                                    @endforelse

                                    <!-- Section Questions -->
                                    @if($section->questions->count() > 0)
                                        <div class="mt-4 border-top pt-3">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-question-circle me-2 text-success"></i>
                                                    الأسئلة المرتبطة بالقسم ({{ $section->questions->count() }})
                                                </h6>
                                                <a href="{{ route('sections.questions.manage', $section->id) }}" 
                                                   class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-cog me-1"></i>إدارة الأسئلة
                                                </a>
                                            </div>
                                            <div class="list-group">
                                                @foreach($section->questions as $index => $question)
                                                    <div class="list-group-item d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center mb-2">
                                                                <span class="badge bg-primary me-2">{{ $index + 1 }}</span>
                                                                <a href="{{ route('question-bank.show', $question->id) }}" 
                                                                   target="_blank" 
                                                                   class="fw-semibold text-dark">
                                                                    {!! Str::limit(strip_tags($question->question_text), 100) !!}
                                                                </a>
                                                            </div>
                                                            <div class="d-flex gap-2 flex-wrap">
                                                                <span class="badge bg-info-transparent">
                                                                    {{ $question->questionType->display_name ?? 'غير محدد' }}
                                                                </span>
                                                                @if($question->pivot->question_grade)
                                                                    <span class="badge bg-success-transparent">
                                                                        {{ $question->pivot->question_grade }} نقطة
                                                                    </span>
                                                                @endif
                                                                @if($question->pivot->is_required)
                                                                    <span class="badge bg-warning-transparent">
                                                                        <i class="fas fa-asterisk"></i> إجباري
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="empty-state">
                                    <i class="fas fa-folder-open fa-5x mb-4 opacity-25"></i>
                                    <h5 class="mb-3">لا توجد أقسام في هذا الكورس</h5>
                                    <p class="text-muted mb-4">ابدأ ببناء محتوى الكورس بإضافة الأقسام والدروس</p>
                                    <a href="{{ route('courses.sections.create', $course->id) }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus me-2"></i>إضافة أول قسم
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforelse
                    </div>
                    <!-- End Accordion -->

                </div>

                <!-- Enrollments Tab -->
                <div class="tab-pane fade" id="enrollments" role="tabpanel">
                    <div class="card custom-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">الطلاب المسجلين ({{ $stats['total_enrollments'] ?? 0 }})</h6>
                            <a href="{{ route('courses.enrollments.index', $course->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-users me-2"></i>إدارة التسجيلات
                            </a>
                        </div>
                        <div class="card-body">
                            @if(($stats['total_enrollments'] ?? 0) > 0)
                                <p class="text-muted">عرض وإدارة الطلاب المسجلين في الكورس</p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('courses.enrollments.create', $course->id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-user-plus me-2"></i>تسجيل طالب
                                    </a>
                                    <a href="{{ route('courses.enrollments.bulk', $course->id) }}" class="btn btn-outline-success">
                                        <i class="fas fa-file-excel me-2"></i>تسجيل جماعي
                                    </a>
                                    <a href="{{ route('courses.enrollments.progress-report', $course->id) }}" class="btn btn-outline-info">
                                        <i class="fas fa-chart-line me-2"></i>تقرير التقدم
                                    </a>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-users fa-3x mb-3 text-muted opacity-25"></i>
                                    <p class="text-muted">لا يوجد طلاب مسجلين في هذا الكورس حتى الآن</p>
                                    <a href="{{ route('courses.enrollments.create', $course->id) }}" class="btn btn-primary">
                                        <i class="fas fa-user-plus me-2"></i>تسجيل أول طالب
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div class="tab-pane fade" id="settings" role="tabpanel">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">معلومات الكورس</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">الكود:</th>
                                    <td>{{ $course->slug }}</td>
                                </tr>
                                <tr>
                                    <th>المستوى:</th>
                                    <td>
                                        @if($course->level)
                                            <span class="badge bg-{{ $course->level == 'beginner' ? 'success' : ($course->level == 'intermediate' ? 'info' : 'danger') }}">
                                                {{ $course->level == 'beginner' ? 'مبتدئ' : ($course->level == 'intermediate' ? 'متوسط' : 'متقدم') }}
                                            </span>
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>اللغة:</th>
                                    <td>{{ $course->language == 'ar' ? 'العربية' : 'الإنجليزية' }}</td>
                                </tr>
                                <tr>
                                    <th>المدرب:</th>
                                    <td>{{ $course->instructor->name ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <th>المدة:</th>
                                    <td>{{ $course->duration_hours ?? 0 }} ساعة</td>
                                </tr>
                                <tr>
                                    <th>متاح من:</th>
                                    <td>{{ $course->available_from ? $course->available_from->format('Y-m-d H:i') : 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <th>متاح حتى:</th>
                                    <td>{{ $course->available_until ? $course->available_until->format('Y-m-d H:i') : 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <th>تاريخ الإنشاء:</th>
                                    <td>{{ $course->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>آخر تحديث:</th>
                                    <td>{{ $course->updated_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@stop

@section('script')
<script>
    // Toggle Visibility
    function toggleVisibility(type, id) {
        let url;
        if (type === 'section') {
            url = `/admin/sections/${id}/toggle-visibility`;
        } else if (type === 'module') {
            url = `/admin/modules/${id}/toggle-visibility`;
        } else if (type === 'course') {
            url = `/admin/courses/${id}/toggle-visibility`;
        } else {
            console.error('Unknown type:', type);
            return;
        }

        // Show loading state
        const modal = new bootstrap.Modal(document.getElementById('visibilityModal'));
        const modalBody = document.getElementById('visibilityModalBody');
        const modalTitle = document.getElementById('visibilityModalTitle');
        modalTitle.textContent = 'جاري التحديث...';
        modalBody.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>';
        modal.show();

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    // Try to parse as JSON, if fails, show the text
                    try {
                        return JSON.parse(text);
                    } catch {
                        throw new Error('حدث خطأ في الاستجابة من الخادم');
                    }
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                modalTitle.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i>نجح التحديث';
                modalBody.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        ${data.message || 'تم التحديث بنجاح'}
                    </div>
                `;
                
                // Update UI directly without reload
                if (type === 'course') {
                    // Update course visibility badge and button
                    const badge = document.getElementById('course-visibility-badge');
                    const button = document.getElementById('course-toggle-visibility-btn');
                    if (badge && button) {
                        if (data.is_visible) {
                            badge.className = 'badge bg-info-transparent px-3 py-2';
                            badge.innerHTML = '<i class="far fa-eye me-1"></i>مرئي';
                            button.innerHTML = '<i class="far fa-eye me-1"></i>إخفاء';
                        } else {
                            badge.className = 'badge bg-secondary-transparent px-3 py-2';
                            badge.innerHTML = '<i class="far fa-eye-slash me-1"></i>مخفي';
                            button.innerHTML = '<i class="far fa-eye-slash me-1"></i>إظهار';
                        }
                    }
                } else if (type === 'section') {
                    // Update section visibility button
                    const button = document.getElementById(`section-visibility-btn-${id}`);
                    if (button) {
                        if (data.is_visible) {
                            button.innerHTML = '<i class="far fa-eye me-1"></i>إخفاء';
                        } else {
                            button.innerHTML = '<i class="far fa-eye-slash me-1"></i>إظهار';
                        }
                    }
                } else if (type === 'module') {
                    // Update module visibility button
                    const button = document.getElementById(`module-visibility-btn-${id}`);
                    if (button) {
                        if (data.is_visible) {
                            button.innerHTML = '<i class="far fa-eye me-1"></i>إخفاء';
                        } else {
                            button.innerHTML = '<i class="far fa-eye-slash me-1"></i>إظهار';
                        }
                    }
                    // Update module visibility badge
                    const badge = document.getElementById(`module-visibility-badge-${id}`);
                    if (badge) {
                        if (data.is_visible) {
                            badge.className = 'badge badge-sm ms-1 bg-success text-white';
                            badge.textContent = 'ظاهر';
                        } else {
                            badge.className = 'badge badge-sm ms-1 bg-secondary';
                            badge.textContent = 'مخفي';
                        }
                    }
                }
                
                // Close modal after 1 second (NO RELOAD for all types)
                setTimeout(() => {
                    modal.hide();
                }, 1000);
            } else {
                modalTitle.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-2"></i>حدث خطأ';
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message || 'حدث خطأ'}
                    </div>
                    <div class="text-end mt-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalTitle.innerHTML = '<i class="fas fa-exclamation-triangle text-danger me-2"></i>حدث خطأ';
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>حدث خطأ في الاتصال:</strong><br>
                    ${error.message || 'يرجى المحاولة مرة أخرى'}
                </div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            `;
        });
    }

    // Toggle Lock
    function toggleLock(type, id) {
        const url = `/admin/sections/${id}/toggle-lock`;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'حدث خطأ');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('حدث خطأ في الاتصال');
        });
    }

    // Duplicate Module
    function duplicateModule(moduleId) {
        if (confirm('هل تريد نسخ هذا الدرس؟')) {
            fetch(`/admin/modules/${moduleId}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'حدث خطأ');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('حدث خطأ في الاتصال');
            });
        }
    }

    // Fade out alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Access Restrictions Management
    const courseRestrictionsUrls = {
        groups: @json(route('courses.restrictions.groups', $course)),
        bulkState: @json(route('courses.restrictions.bulk-state', $course)),
        syncBulk: @json(route('courses.modules.restrictions.sync-bulk', $course)),
    };

    let currentRestrictions = {
        mode: 'single',
        type: null,
        id: null,
        title: null,
        moduleIds: [],
    };

    function setRestrictionsModalBulkUi(isBulk) {
        const notice = document.getElementById('restrictionsModalBulkNotice');
        if (notice) {
            notice.classList.toggle('d-none', !isBulk);
        }
    }

    function updateBulkModuleToolbar() {
        const checks = document.querySelectorAll('.js-module-bulk-check:checked');
        const n = checks.length;
        const countEl = document.getElementById('bulk-module-selected-count');
        const btn = document.getElementById('bulk-module-restrictions-btn');
        if (countEl) {
            countEl.textContent = n + (n === 1 ? ' وحدة محددة' : ' وحدات محددة');
        }
        if (btn) {
            btn.disabled = n === 0;
        }
    }

    function renderRestrictionsGroupsList(allGroups, restrictedGroupIds, options) {
        options = options || {};
        const bulkCoverageById = options.bulkCoverageById || null;

        const groupsList = document.getElementById('restrictionsGroupsList');
        const toolbar = document.getElementById('restrictionsGroupsToolbar');
        if (!groupsList) {
            return;
        }
        groupsList.innerHTML = '';

        const restrictedSet = new Set(
            (restrictedGroupIds || []).map(function (x) {
                return parseInt(x, 10);
            })
        );

        if (allGroups && allGroups.length > 0) {
            if (toolbar) {
                toolbar.classList.remove('d-none');
            }
            allGroups.forEach(function (group) {
                const groupId = parseInt(group.id, 10);
                let isChecked = false;
                let setIndeterminate = false;
                let bulkModeAttr = '';

                if (bulkCoverageById && bulkCoverageById[groupId]) {
                    const cov = bulkCoverageById[groupId];
                    if (cov.all_have) {
                        bulkModeAttr = ' data-initial-bulk-mode="all"';
                        isChecked = true;
                        setIndeterminate = false;
                    } else if (!cov.any_have) {
                        bulkModeAttr = ' data-initial-bulk-mode="none"';
                        isChecked = false;
                        setIndeterminate = false;
                    } else {
                        bulkModeAttr = ' data-initial-bulk-mode="mixed"';
                        isChecked = false;
                        setIndeterminate = true;
                    }
                } else {
                    isChecked = restrictedSet.has(groupId);
                }

                const groupItem = document.createElement('div');
                groupItem.className = 'form-check mb-3';
                groupItem.innerHTML =
                    '<input class="form-check-input restriction-group-cb" type="checkbox" ' +
                    'value="' + groupId + '" id="group_' + groupId + '"' + bulkModeAttr + ' ' +
                    (isChecked ? 'checked' : '') + '>' +
                    '<label class="form-check-label" for="group_' + groupId + '">' +
                    '<strong>' + group.name + '</strong>' +
                    (group.description
                        ? '<br><small class="text-muted">' + group.description + '</small>'
                        : '') +
                    '</label>';
                groupsList.appendChild(groupItem);

                if (setIndeterminate) {
                    const cb = groupItem.querySelector('.restriction-group-cb');
                    if (cb) {
                        cb.indeterminate = true;
                    }
                }
            });
        } else {
            if (toolbar) {
                toolbar.classList.add('d-none');
            }
            groupsList.innerHTML =
                '<div class="alert alert-info">لا توجد مجموعات مرتبطة بهذا الكورس</div>';
        }
    }

    function showRestrictionsModalSpinner() {
        const groupsList = document.getElementById('restrictionsGroupsList');
        const toolbar = document.getElementById('restrictionsGroupsToolbar');
        if (toolbar) {
            toolbar.classList.add('d-none');
        }
        if (groupsList) {
            groupsList.innerHTML =
                '<div class="text-center py-3"><div class="spinner-border text-primary" role="status">' +
                '<span class="visually-hidden">جاري التحميل...</span></div></div>';
        }
    }

    // Handle manage restrictions button click
    document.addEventListener('DOMContentLoaded', function() {
        // Manage restrictions buttons
        document.querySelectorAll('.manage-restrictions-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const id = this.getAttribute('data-id');
                const title = this.getAttribute('data-title');

                currentRestrictions.mode = 'single';
                currentRestrictions.type = type;
                currentRestrictions.id = id;
                currentRestrictions.title = title;
                currentRestrictions.moduleIds = [];

                loadRestrictions(type, id);
            });
        });

        const bulkSelectAll = document.getElementById('bulk-module-select-all');
        const bulkDeselectAll = document.getElementById('bulk-module-deselect-all');
        const bulkOpenBtn = document.getElementById('bulk-module-restrictions-btn');
        if (bulkSelectAll) {
            bulkSelectAll.addEventListener('click', function () {
                document.querySelectorAll('.js-module-bulk-check').forEach(function (cb) {
                    cb.checked = true;
                });
                updateBulkModuleToolbar();
            });
        }
        if (bulkDeselectAll) {
            bulkDeselectAll.addEventListener('click', function () {
                document.querySelectorAll('.js-module-bulk-check').forEach(function (cb) {
                    cb.checked = false;
                });
                updateBulkModuleToolbar();
            });
        }
        if (bulkOpenBtn) {
            bulkOpenBtn.addEventListener('click', function () {
                openBulkModuleRestrictionsModal();
            });
        }
        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList && e.target.classList.contains('js-module-bulk-check')) {
                updateBulkModuleToolbar();
            }
        });

        const sectionsAccordion = document.getElementById('sectionsAccordion');
        if (sectionsAccordion) {
            sectionsAccordion.addEventListener('click', function (e) {
                const selectBtn = e.target.closest('.js-section-select-all-modules');
                const deselectBtn = e.target.closest('.js-section-deselect-all-modules');
                if (selectBtn) {
                    e.preventDefault();
                    const sid = selectBtn.getAttribute('data-section-id');
                    if (!sid) {
                        return;
                    }
                    document
                        .querySelectorAll('.js-module-bulk-check[data-section-id="' + sid + '"]')
                        .forEach(function (cb) {
                            cb.checked = true;
                        });
                    updateBulkModuleToolbar();
                    return;
                }
                if (deselectBtn) {
                    e.preventDefault();
                    const sid = deselectBtn.getAttribute('data-section-id');
                    if (!sid) {
                        return;
                    }
                    document
                        .querySelectorAll('.js-module-bulk-check[data-section-id="' + sid + '"]')
                        .forEach(function (cb) {
                            cb.checked = false;
                        });
                    updateBulkModuleToolbar();
                }
            });
        }

        const gSelectAll = document.getElementById('restrictionsGroupsSelectAllBtn');
        const gDeselectAll = document.getElementById('restrictionsGroupsDeselectAllBtn');
        if (gSelectAll) {
            gSelectAll.addEventListener('click', function () {
                document
                    .querySelectorAll('#restrictionsGroupsList .restriction-group-cb')
                    .forEach(function (cb) {
                        cb.checked = true;
                        cb.indeterminate = false;
                    });
            });
        }
        if (gDeselectAll) {
            gDeselectAll.addEventListener('click', function () {
                document
                    .querySelectorAll('#restrictionsGroupsList .restriction-group-cb')
                    .forEach(function (cb) {
                        cb.checked = false;
                        cb.indeterminate = false;
                    });
            });
        }

        const restrictionsModalEl = document.getElementById('restrictionsModal');
        if (restrictionsModalEl) {
            restrictionsModalEl.addEventListener('hidden.bs.modal', function () {
                currentRestrictions.mode = 'single';
                currentRestrictions.moduleIds = [];
                setRestrictionsModalBulkUi(false);
            });
        }

        updateBulkModuleToolbar();

        // Delete module buttons
        document.querySelectorAll('.delete-module-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const sectionId = parseInt(this.getAttribute('data-section-id'));
                const moduleId = parseInt(this.getAttribute('data-module-id'));
                const moduleTitle = this.getAttribute('data-module-title');

                deleteModule(sectionId, moduleId, moduleTitle);
            });
        });

        // Confirm delete button
        const confirmDeleteBtn = document.getElementById('confirmDeleteModuleBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function() {
                confirmDeleteModule();
            });
        }
    });

    function openBulkModuleRestrictionsModal() {
        const selected = Array.from(document.querySelectorAll('.js-module-bulk-check:checked')).map(function (cb) {
            return parseInt(cb.value, 10);
        });
        if (selected.length === 0) {
            alert('حدد وحدة واحدة على الأقل.');
            return;
        }

        currentRestrictions.mode = 'bulk';
        currentRestrictions.type = 'module';
        currentRestrictions.id = null;
        currentRestrictions.title = null;
        currentRestrictions.moduleIds = selected;

        showRestrictionsModalSpinner();
        setRestrictionsModalBulkUi(true);
        document.getElementById('restrictionsModalTitle').textContent =
            'قيود جماعية — ' + selected.length + ' وحدة';

        const params = new URLSearchParams();
        selected.forEach(function (id) {
            params.append('module_ids[]', id);
        });

        fetch(courseRestrictionsUrls.bulkState + '?' + params.toString(), {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (err) {
                        throw new Error(err.message || 'حدث خطأ في تحميل حالة القيود الجماعية');
                    });
                }
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    const bulkCoverageById = {};
                    (data.group_coverage || []).forEach(function (row) {
                        bulkCoverageById[row.id] = {
                            all_have: !!row.all_have,
                            any_have: !!row.any_have,
                        };
                    });
                    renderRestrictionsGroupsList(data.all_groups || [], [], {
                        bulkCoverageById: bulkCoverageById,
                    });
                    const modal = new bootstrap.Modal(document.getElementById('restrictionsModal'));
                    modal.show();
                } else {
                    alert(data.message || 'حدث خطأ في تحميل المجموعات');
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
                alert(error.message || 'حدث خطأ في الاتصال');
            });
    }

    // Load restrictions
    function loadRestrictions(type, id) {
        const url =
            type === 'section'
                ? `/admin/sections/${id}/restrictions`
                : `/admin/modules/${id}/restrictions`;

        showRestrictionsModalSpinner();
        setRestrictionsModalBulkUi(false);

        fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (err) {
                        throw new Error(err.message || 'حدث خطأ في تحميل القيود');
                    });
                }
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    document.getElementById('restrictionsModalTitle').textContent =
                        'إدارة القيود - ' + currentRestrictions.title;

                    renderRestrictionsGroupsList(data.all_groups || [], data.restricted_group_ids || []);

                    const modal = new bootstrap.Modal(document.getElementById('restrictionsModal'));
                    modal.show();
                } else {
                    alert(data.message || 'حدث خطأ في تحميل القيود');
                }
            })
            .catch(function (error) {
                console.error('Error:', error);
                alert(error.message || 'حدث خطأ في الاتصال');
            });
    }

    /**
     * Update restrictions badges in the UI after saving
     * @param {string} type - 'section' or 'module'
     * @param {number} id - Section or module ID
     * @param {Array} groups - Array of group objects with {id, name, description}
     */
    function updateRestrictionsBadges(type, id, groups) {
        console.log('Updating badges:', { type, id, groups });

        if (type === 'section') {
            // Update section badge
            const sectionBadge = document.getElementById(`section-restrictions-badge-${id}`);
            if (sectionBadge) {
                if (groups && groups.length > 0) {
                    sectionBadge.style.display = 'inline-block';
                    sectionBadge.title = `هذا القسم له قيود وصول (${groups.length} مجموعة)`;
                } else {
                    sectionBadge.style.display = 'none';
                }
            }
        } else if (type === 'module') {
            // Update module badges
            const mainBadge = document.getElementById(`module-main-badge-${id}`);
            const groupsContainer = document.getElementById(`module-groups-container-${id}`);
            const moreBadge = document.getElementById(`module-more-badge-${id}`);

            if (!mainBadge || !groupsContainer) {
                console.error('Module badge elements not found:', { id, mainBadge, groupsContainer });
                return;
            }

            if (groups && groups.length > 0) {
                // Show main badge
                mainBadge.style.display = 'inline-block';
                
                // Build group names for title
                const groupNames = groups.map(g => g.name).join('، ');
                mainBadge.title = `هذه الوحدة مقيدة على المجموعات: ${groupNames}`;

                // Clear existing group badges
                groupsContainer.innerHTML = '';

                // Display first 3 groups
                const displayGroups = groups.slice(0, 3);
                const remainingCount = Math.max(groups.length - 3, 0);

                displayGroups.forEach(group => {
                    const groupBadge = document.createElement('span');
                    groupBadge.className = 'badge bg-primary-transparent text-primary ms-1 module-group-badge';
                    groupBadge.setAttribute('data-module-id', id);
                    groupBadge.setAttribute('data-group-name', group.name);
                    groupBadge.innerHTML = `<i class="fas fa-users me-1"></i>${group.name}`;
                    groupsContainer.appendChild(groupBadge);
                });

                // Show "more" badge if there are more than 3 groups
                if (remainingCount > 0) {
                    if (!moreBadge) {
                        const moreBadgeEl = document.createElement('span');
                        moreBadgeEl.id = `module-more-badge-${id}`;
                        moreBadgeEl.className = 'badge bg-light text-muted ms-1';
                        moreBadgeEl.title = 'مجموعات أخرى لها نفس القيود';
                        moreBadgeEl.textContent = `+${remainingCount}`;
                        groupsContainer.appendChild(moreBadgeEl);
                    } else {
                        moreBadge.textContent = `+${remainingCount}`;
                        moreBadge.style.display = 'inline-block';
                    }
                } else {
                    // Hide "more" badge if it exists
                    if (moreBadge) {
                        moreBadge.style.display = 'none';
                    }
                }
            } else {
                // Hide all badges if no restrictions
                mainBadge.style.display = 'none';
                groupsContainer.innerHTML = '';
                if (moreBadge) {
                    moreBadge.style.display = 'none';
                }
            }
        }
    }

    // Save restrictions
    function saveRestrictions() {
        const groupsList = document.getElementById('restrictionsGroupsList');
        const saveBtn = document.getElementById('saveRestrictionsBtn');
        const originalBtnText = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري الحفظ...';

        const isBulk = currentRestrictions.mode === 'bulk';
        let url;
        let body;

        if (isBulk) {
            const addGroupIds = [];
            const removeGroupIds = [];
            groupsList.querySelectorAll('.restriction-group-cb').forEach(function (cb) {
                const gid = parseInt(cb.value, 10);
                const initial = cb.getAttribute('data-initial-bulk-mode');
                if (!initial) {
                    return;
                }
                if (initial === 'mixed' && cb.indeterminate) {
                    return;
                }
                if (initial === 'all') {
                    if (!cb.checked) {
                        removeGroupIds.push(gid);
                    }
                } else if (initial === 'none') {
                    if (cb.checked) {
                        addGroupIds.push(gid);
                    }
                } else if (initial === 'mixed') {
                    if (cb.checked) {
                        addGroupIds.push(gid);
                    } else {
                        removeGroupIds.push(gid);
                    }
                }
            });

            if (addGroupIds.length === 0 && removeGroupIds.length === 0) {
                alert(
                    'لم يُرسل أي تغيير. المجموعات ذات الحالة «بعض الوحدات فقط» تبقى دون تعديل ما لم تحددها صراحةً (أو استخدم «تحديد كل المجموعات» / «إلغاء التحديد»).'
                );
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnText;
                return;
            }

            url = courseRestrictionsUrls.syncBulk;
            body = JSON.stringify({
                module_ids: currentRestrictions.moduleIds,
                add_group_ids: addGroupIds,
                remove_group_ids: removeGroupIds,
            });
        } else {
            const checkboxes = groupsList.querySelectorAll('.restriction-group-cb:checked');
            const groupIds = Array.from(checkboxes).map(function (cb) {
                return parseInt(cb.value, 10);
            });
            const type = currentRestrictions.type;
            const id = currentRestrictions.id;
            url =
                type === 'section'
                    ? '/admin/sections/' + id + '/restrictions/sync'
                    : '/admin/modules/' + id + '/restrictions/sync';
            body = JSON.stringify({
                group_ids: groupIds,
            });
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                Accept: 'application/json',
            },
            body: body,
        })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (err) {
                        throw new Error(err.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('restrictionsModal'));
                    modal.hide();

                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'تم تحديث القيود بنجاح');
                    } else {
                        alert(data.message || 'تم تحديث القيود بنجاح');
                    }

                    if (isBulk) {
                        const pm = data.per_module_groups || {};
                        (data.updated_module_ids || []).forEach(function (mid) {
                            const key = String(mid);
                            updateRestrictionsBadges('module', mid, pm[key] || []);
                        });
                        document.querySelectorAll('.js-module-bulk-check').forEach(function (cb) {
                            cb.checked = false;
                        });
                        updateBulkModuleToolbar();
                    } else {
                        updateRestrictionsBadges(currentRestrictions.type, currentRestrictions.id, data.groups || []);
                    }
                } else {
                    alert(data.message || 'حدث خطأ في حفظ القيود');
                }
            })
            .catch(function (error) {
                console.error('Error saving restrictions:', error);
                alert('حدث خطأ في الاتصال: ' + error.message);
            })
            .finally(function () {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnText;
            });
    }

    // Store current deletion data
    let currentDeleteData = {
        sectionId: null,
        moduleId: null,
        moduleTitle: null
    };

    /**
     * Show delete confirmation modal
     * @param {number} sectionId - Section ID
     * @param {number} moduleId - Module ID
     * @param {string} moduleTitle - Module title for confirmation
     */
    function deleteModule(sectionId, moduleId, moduleTitle) {
        // Store deletion data
        currentDeleteData.sectionId = sectionId;
        currentDeleteData.moduleId = moduleId;
        currentDeleteData.moduleTitle = moduleTitle;

        // Update title display (textContent automatically escapes HTML for security)
        const titleDisplay = document.getElementById('delete-module-title-display');
        if (titleDisplay) {
            titleDisplay.textContent = `"${moduleTitle}"`;
        }

        // Show modal
        const deleteModalElement = document.getElementById('deleteModuleModal');
        if (deleteModalElement) {
            const deleteModal = new bootstrap.Modal(deleteModalElement);
            deleteModal.show();
        } else {
            console.error('Delete module modal not found');
            // Fallback to browser confirm if modal doesn't exist
            if (confirm('هل أنت متأكد من حذف هذا الدرس: "' + moduleTitle + '"؟')) {
                const deleteBtn = document.getElementById('delete-module-btn-' + moduleId);
                if (deleteBtn) {
                    performDeleteModule(deleteBtn, sectionId, moduleId);
                }
            }
        }
    }

    /**
     * Confirm deletion and proceed
     */
    function confirmDeleteModule() {
        const { sectionId, moduleId } = currentDeleteData;

        // Get delete button
        const deleteBtn = document.getElementById('delete-module-btn-' + moduleId);
        if (!deleteBtn) {
            console.error('Delete button not found for module:', moduleId);
            return;
        }

        // Hide modal
        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModuleModal'));
        if (deleteModal) {
            deleteModal.hide();
        }

        // Perform deletion
        performDeleteModule(deleteBtn, sectionId, moduleId);
    }

    /**
     * Perform the actual deletion
     * @param {HTMLElement} deleteBtn - Delete button element
     * @param {number} sectionId - Section ID
     * @param {number} moduleId - Module ID
     */
    function performDeleteModule(deleteBtn, sectionId, moduleId) {

        // Disable button and show loading
        const originalBtnHtml = deleteBtn.innerHTML;
        deleteBtn.disabled = true;
        deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري الحذف...';

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        // Get module container for removal
        const moduleContainer = document.getElementById('module-container-' + moduleId);

        // Send DELETE request
        fetch(`/admin/sections/${sectionId}/modules/${moduleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Delete response:', data);
            if (data.success) {
                // Show success message
                if (data.warning) {
                    if (typeof toastr !== 'undefined') {
                        toastr.warning(data.warning);
                        toastr.success(data.message || 'تم حذف الوحدة بنجاح');
                    } else {
                        alert(data.warning + '\n\n' + (data.message || 'تم حذف الوحدة بنجاح'));
                    }
                } else {
                    if (typeof toastr !== 'undefined') {
                        toastr.success(data.message || 'تم حذف الوحدة بنجاح');
                    } else {
                        alert(data.message || 'تم حذف الوحدة بنجاح');
                    }
                }

                // Remove module from DOM with animation
                if (moduleContainer) {
                    // Add fade out animation
                    moduleContainer.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    moduleContainer.style.opacity = '0';
                    moduleContainer.style.transform = 'translateX(-20px)';
                    
                    // Remove after animation
                    setTimeout(() => {
                        moduleContainer.remove();

                        // Update lessons count in section header if needed
                        updateSectionModulesCount(sectionId);
                    }, 300);
                } else {
                    console.warn('Module container not found for removal:', 'module-container-' + moduleId);
                    // Fallback: reload page if container not found
                    location.reload();
                }
            } else {
                throw new Error(data.message || 'فشل حذف الوحدة');
            }
        })
        .catch(error => {
            console.error('Error deleting module:', error);
            
            // Re-enable button
            deleteBtn.disabled = false;
            deleteBtn.innerHTML = originalBtnHtml;

            // Show error message
            if (typeof toastr !== 'undefined') {
                toastr.error('حدث خطأ أثناء حذف الوحدة: ' + error.message);
            } else {
                alert('حدث خطأ أثناء حذف الوحدة: ' + error.message);
            }
        });
    }

    /**
     * Update the modules count in section header after deletion
     * @param {number} sectionId - Section ID
     */
    function updateSectionModulesCount(sectionId) {
        // Find the section accordion collapse element
        const sectionAccordion = document.getElementById(`section-${sectionId}`);
        if (!sectionAccordion) {
            console.warn('Section accordion not found:', `section-${sectionId}`);
            return;
        }

        // Count remaining modules (excluding empty message)
        const moduleContainers = sectionAccordion.querySelectorAll('[id^="module-container-"]');
        const modulesCount = moduleContainers.length;

        // Update count badge in section header
        const countBadge = document.getElementById(`section-modules-count-badge-${sectionId}`);
        if (countBadge) {
            const countText = modulesCount === 0 
                ? 'لا توجد دروس'
                : modulesCount === 1 
                    ? '1 درس'
                    : `${modulesCount} دروس`;
            countBadge.textContent = countText;
        } else {
            console.warn('Section modules count badge not found:', `section-modules-count-badge-${sectionId}`);
        }

        // If no modules left, show empty message
        if (modulesCount === 0) {
            const accordionBody = sectionAccordion.querySelector('.accordion-body');
            if (accordionBody) {
                // Check if empty message already exists
                let emptyMessage = accordionBody.querySelector('.text-center.text-muted');
                if (!emptyMessage) {
                    // Find the "Add Activity Buttons" div and insert empty message after it
                    const addActivityDiv = accordionBody.querySelector('.mb-3.p-3.bg-light.rounded');
                    const emptyDiv = document.createElement('div');
                    emptyDiv.className = 'text-center text-muted py-3';
                    emptyDiv.innerHTML = '<i class="fas fa-inbox fs-3 mb-2 opacity-25"></i><p class="mb-0">لا توجد دروس في هذا القسم</p>';
                    
                    // Insert after add activity buttons or at the end of accordion body
                    if (addActivityDiv && addActivityDiv.nextSibling) {
                        accordionBody.insertBefore(emptyDiv, addActivityDiv.nextSibling);
                    } else {
                        accordionBody.appendChild(emptyDiv);
                    }
                }
            }
        } else {
            // Remove empty message if modules exist
            const accordionBody = sectionAccordion.querySelector('.accordion-body');
            if (accordionBody) {
                const emptyMessage = accordionBody.querySelector('.text-center.text-muted');
                if (emptyMessage && emptyMessage.textContent.includes('لا توجد دروس')) {
                    emptyMessage.remove();
                }
            }
        }
    }
</script>

<!-- Access Restrictions Modal -->
<div class="modal fade" id="restrictionsModal" tabindex="-1" aria-labelledby="restrictionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="restrictionsModalTitle">
                    <i class="fas fa-users-cog me-2"></i>
                    إدارة القيود
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>ملاحظة:</strong> حدد المجموعات التي يمكنها الوصول إلى هذا المحتوى. إذا لم تحدد أي مجموعة، سيكون المحتوى متاحاً لجميع المجموعات.
                </div>
                <div id="restrictionsModalBulkNotice" class="alert alert-warning d-none" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>قيود جماعية (دمج):</strong> التحديد يضيف المجموعة إلى كل الوحدات المختارة دون حذف بقية القيود على كل وحدة. إلغاء التحديد يزيل المجموعة من الوحدات المختارة فقط. الصناديق «بعض الوحدات» (غير محددة بشكل قاطع) تبقى كما هي ما لم تغيّرها صراحةً أو تستخدم أزرار تحديد/إلغاء الكل.
                </div>
                <div id="restrictionsGroupsToolbar" class="mb-3 d-none d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="restrictionsGroupsSelectAllBtn">تحديد كل المجموعات</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="restrictionsGroupsDeselectAllBtn">إلغاء تحديد المجموعات</button>
                </div>
                <div id="restrictionsGroupsList">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">جاري التحميل...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    إلغاء
                </button>
                <button type="button" class="btn btn-primary" id="saveRestrictionsBtn" onclick="saveRestrictions()">
                    <i class="fas fa-save me-1"></i>
                    حفظ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Visibility Toggle Modal -->
<div class="modal fade" id="visibilityModal" tabindex="-1" aria-labelledby="visibilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="visibilityModalTitle">
                    <i class="fas fa-info-circle me-2"></i>تحديث الحالة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="visibilityModalBody">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Module Confirmation Modal -->
<div class="modal fade" id="deleteModuleModal" tabindex="-1" aria-labelledby="deleteModuleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModuleModalTitle">
                    <i class="fas fa-exclamation-triangle me-2"></i>تأكيد الحذف
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="deleteModuleModalBody">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>تحذير:</strong> هل أنت متأكد من حذف هذا الدرس؟
                </div>
                <p class="mb-0">
                    <strong>عنوان الدرس:</strong> <span class="text-primary" id="delete-module-title-display"></span>
                </p>
                <p class="text-muted mt-2 mb-0">
                    <small>لن تتمكن من التراجع عن هذا الإجراء بعد الحذف.</small>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>
                    إلغاء
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteModuleBtn">
                    <i class="fas fa-trash me-1"></i>
                    حذف
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Resource Modal -->
<div class="modal fade" id="addResourceModal" tabindex="-1" aria-labelledby="addResourceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addResourceModalLabel">
                    <i class="fas fa-file-plus me-2"></i>إضافة مورد جديد
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-resource-form" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="section_id" id="modal-section-id">
                    <input type="hidden" name="course_id" id="modal-course-id">

                    <!-- Error Messages Container -->
                    <div id="resource-form-errors" class="alert alert-danger" style="display: none;">
                        <strong><i class="fas fa-exclamation-triangle me-2"></i>هناك أخطاء في النموذج:</strong>
                        <ul id="resource-errors-list" class="mb-0 mt-2"></ul>
                    </div>

                    <!-- Basic Information -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-info-circle me-2"></i>المعلومات الأساسية
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">عنوان المورد <span class="text-danger">*</span></label>
                                    <input type="text" name="title" class="form-control" placeholder="مثال: كتاب مقدمة في البرمجة" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">نوع المورد <span class="text-danger">*</span></label>
                                    <select name="resource_type" class="form-select" required>
                                        <option value="">اختر النوع</option>
                                        <option value="pdf" selected>PDF</option>
                                        <option value="doc">DOC/DOCX</option>
                                        <option value="ppt">PPT/PPTX</option>
                                        <option value="excel">Excel</option>
                                        <option value="image">صورة</option>
                                        <option value="audio">صوت</option>
                                        <option value="archive">أرشيف</option>
                                        <option value="other">أخرى</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">الكورس</label>
                                    <select name="course_id" class="form-select" id="modal-course-select" disabled>
                                        <option value="">اختر الكورس</option>
                                        @foreach($allCourses ?? [] as $courseOption)
                                            <option value="{{ $courseOption->id }}">{{ $courseOption->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">الوصف</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="اكتب وصفاً للمورد التعليمي..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resource Source Selection -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-link me-2"></i>مصدر المورد <span class="text-danger">*</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label mb-3">اختر نوع المصدر</label>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="resource-source-option" for="modalSourceFile">
                                            <input class="form-check-input" type="radio" name="resource_source" id="modalSourceFile" value="file" onchange="toggleResourceSourceModal()">
                                            <div class="card border h-100 resource-source-card">
                                                <div class="card-body text-center p-4">
                                                    <i class="fas fa-cloud-upload-alt fs-24 text-primary mb-3"></i>
                                                    <h6 class="mb-0 fw-semibold">رفع ملف</h6>
                                                    <small class="text-muted d-block mt-2">رفع ملف من جهازك</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="resource-source-option" for="modalSourceUrl">
                                            <input class="form-check-input" type="radio" name="resource_source" id="modalSourceUrl" value="url" checked onchange="toggleResourceSourceModal()">
                                            <div class="card border h-100 resource-source-card">
                                                <div class="card-body text-center p-4">
                                                    <i class="fas fa-link fs-24 text-success mb-3"></i>
                                                    <h6 class="mb-0 fw-semibold">رابط خارجي</h6>
                                                    <small class="text-muted d-block mt-2">إدخال رابط مباشر</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="resource-source-option" for="modalSourceExisting">
                                            <input class="form-check-input" type="radio" name="resource_source" id="modalSourceExisting" value="existing" onchange="toggleResourceSourceModal()">
                                            <div class="card border h-100 resource-source-card">
                                                <div class="card-body text-center p-4">
                                                    <i class="fas fa-list fs-24 text-info mb-3"></i>
                                                    <h6 class="mb-0 fw-semibold">اختر مورد موجود</h6>
                                                    <small class="text-muted d-block mt-2">اختر من الموارد المضافة مسبقاً</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- File Upload Section -->
                            <div id="modalFileUploadSection" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">اختر الملف</label>
                                    <input type="file" id="modalFileInput" name="file" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.txt">
                                    <small class="text-muted">الحد الأقصى لحجم الملف: 50 MB</small>
                                </div>

                                <!-- File Preview -->
                                <div id="modalFilePreview" class="alert alert-info" style="display: none;">
                                    <h6 class="mb-2"><i class="fas fa-file me-2"></i>معاينة الملف</h6>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>اسم الملف:</strong>
                                            <span id="modalFileName" class="d-block text-muted"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>نوع الملف:</strong>
                                            <span id="modalFileType" class="d-block text-muted"></span>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>حجم الملف:</strong>
                                            <span id="modalFileSize" class="d-block text-muted"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- URL Input Section -->
                            <div id="modalUrlInputSection">
                                <div class="mb-3">
                                    <label class="form-label">رابط المورد <span class="text-danger">*</span></label>
                                    <input type="url" id="modalResourceUrlInput" name="resource_url" class="form-control" placeholder="https://example.com/resource.pdf">
                                    <small class="text-muted">أدخل رابط مباشر للمورد (PDF، DOC، فيديو، إلخ)</small>
                                </div>

                                <!-- Link Display Mode -->
                                <div class="mb-3">
                                    <label class="form-label d-block">طريقة عرض الرابط</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="display_mode" id="modalDisplayModeExternal" value="external">
                                        <label class="form-check-label" for="modalDisplayModeExternal">فتح في تبويب جديد</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="display_mode" id="modalDisplayModeEmbedded" value="embedded" checked>
                                        <label class="form-check-label" for="modalDisplayModeEmbedded">تضمين داخل صفحة الدرس</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Resource Selection Section -->
                            <div id="modalExistingResourceSection" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">اختر مورد موجود <span class="text-danger">*</span></label>
                                    <select name="existing_resource_id" id="modalExistingResourceSelect" class="form-select">
                                        <option value="">اختر مورد...</option>
                                        @foreach($existingResources ?? [] as $existingResource)
                                            <option value="{{ $existingResource->id }}" data-title="{{ $existingResource->title }}" data-type="{{ $existingResource->resource_type }}">
                                                {{ $existingResource->title }} ({{ $existingResource->resource_type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <div class="card-title">
                                <i class="fas fa-cog me-2"></i>الإعدادات
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_published" id="modalIsPublished" checked>
                                        <label class="form-check-label" for="modalIsPublished">منشور</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="is_visible" id="modalIsVisible" checked>
                                        <label class="form-check-label" for="modalIsVisible">مرئي للطلاب</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="allow_download" id="modalAllowDownload" checked>
                                        <label class="form-check-label" for="modalAllowDownload">السماح بالتحميل</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>إلغاء
                </button>
                <button type="button" class="btn btn-primary" id="submit-resource-form">
                    <i class="fas fa-save me-1"></i>حفظ
                </button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Resource Modal JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const addResourceModal = document.getElementById('addResourceModal');
        const submitResourceForm = document.getElementById('submit-resource-form');
        const resourceForm = document.getElementById('add-resource-form');
        const modalSectionId = document.getElementById('modal-section-id');
        const modalCourseId = document.getElementById('modal-course-id');
        const modalCourseSelect = document.getElementById('modal-course-select');

        // Handle modal open - set section_id and course_id
        if (addResourceModal) {
            addResourceModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const sectionId = button.getAttribute('data-section-id');
                const courseId = button.getAttribute('data-course-id');
                
                modalSectionId.value = sectionId || '';
                modalCourseId.value = courseId || '';
                
                // Set course select value
                if (courseId && modalCourseSelect) {
                    modalCourseSelect.value = courseId;
                }
                
                // Reset form
                resourceForm.reset();
                document.getElementById('resource-form-errors').style.display = 'none';
                document.getElementById('resource-errors-list').innerHTML = '';
                
                // Reset to default values
                document.getElementById('modalSourceUrl').checked = true;
                toggleResourceSourceModal();
            });
        }

        // Handle form submission
        if (submitResourceForm) {
            submitResourceForm.addEventListener('click', function(e) {
                e.preventDefault();
                
                const formData = new FormData(resourceForm);
                const submitBtn = this;
                const originalBtnHtml = submitBtn.innerHTML;
                
                // Disable button and show loading
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>جاري الحفظ...';
                
                // Hide previous errors
                document.getElementById('resource-form-errors').style.display = 'none';
                document.getElementById('resource-errors-list').innerHTML = '';

                fetch('{{ route("resources.store-ajax") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        if (typeof toastr !== 'undefined') {
                            toastr.success(data.message || 'تم إنشاء المورد بنجاح');
                        } else {
                            alert(data.message || 'تم إنشاء المورد بنجاح');
                        }
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(addResourceModal);
                        if (modal) {
                            modal.hide();
                        }
                        
                        // Reset form
                        resourceForm.reset();
                        
                        // Add module to the list dynamically
                        if (data.module && data.module_html) {
                            const sectionId = data.module.section_id;
                            const sectionElement = document.getElementById('section-' + sectionId);
                            
                            if (sectionElement) {
                                // Ensure section is expanded (show the accordion)
                                const sectionButton = document.querySelector('[data-bs-target="#section-' + sectionId + '"]');
                                if (sectionButton && sectionButton.classList.contains('collapsed')) {
                                    sectionButton.click(); // Expand the section
                                }
                                
                                // Wait a bit for section to expand, then add module
                                setTimeout(() => {
                                    // Check if module already exists
                                    const existingModule = document.getElementById('module-container-' + data.module.id);
                                    if (existingModule) {
                                        return; // Module already exists
                                    }
                                    
                                    // Find the "Section Header with Actions" div (after "Add Activity Buttons")
                                    const sectionHeader = sectionElement.querySelector('.border-bottom');
                                    const addActivityButtons = sectionElement.querySelector('.bg-light.rounded');
                                    
                                    if (sectionHeader && addActivityButtons) {
                                        // Insert module HTML after the section header (which comes after add activity buttons)
                                        sectionHeader.insertAdjacentHTML('afterend', data.module_html);
                                    } else if (addActivityButtons) {
                                        // If no section header, insert after add activity buttons
                                        addActivityButtons.insertAdjacentHTML('afterend', data.module_html);
                                    } else {
                                        // Append to accordion body
                                        sectionElement.insertAdjacentHTML('beforeend', data.module_html);
                                    }

                                    if (typeof updateBulkModuleToolbar === 'function') {
                                        updateBulkModuleToolbar();
                                    }
                                    
                                    // Update modules count badge
                                    const modulesCountBadge = document.getElementById('section-modules-count-badge-' + sectionId);
                                    if (modulesCountBadge) {
                                        const currentText = modulesCountBadge.textContent.trim();
                                        const currentCount = parseInt(currentText.match(/\d+/)?.[0] || '0');
                                        const newCount = currentCount + 1;
                                        modulesCountBadge.textContent = newCount + ' ' + (newCount === 1 ? 'درس' : 'دروس');
                                    }
                                    
                                    // Scroll to and highlight the new module
                                    const newModuleElement = document.getElementById('module-container-' + data.module.id);
                                    if (newModuleElement) {
                                        // Highlight the new module
                                        newModuleElement.style.transition = 'all 0.3s ease';
                                        newModuleElement.style.backgroundColor = '#d4edda';
                                        newModuleElement.style.borderColor = '#28a745';
                                        
                                        // Scroll into view
                                        setTimeout(() => {
                                            newModuleElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                            
                                            // Remove highlight after 3 seconds
                                            setTimeout(() => {
                                                newModuleElement.style.backgroundColor = '';
                                                newModuleElement.style.borderColor = '';
                                            }, 3000);
                                        }, 300);
                                    }
                                }, 300);
                            }
                        }
                    } else {
                        // Show errors
                        const errorsDiv = document.getElementById('resource-form-errors');
                        const errorsList = document.getElementById('resource-errors-list');
                        
                        if (data.errors) {
                            errorsList.innerHTML = '';
                            Object.keys(data.errors).forEach(function(key) {
                                data.errors[key].forEach(function(error) {
                                    const li = document.createElement('li');
                                    li.textContent = error;
                                    errorsList.appendChild(li);
                                });
                            });
                            errorsDiv.style.display = 'block';
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(data.message || 'حدث خطأ');
                            } else {
                                alert(data.message || 'حدث خطأ');
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (typeof toastr !== 'undefined') {
                        toastr.error('حدث خطأ في الاتصال');
                    } else {
                        alert('حدث خطأ في الاتصال');
                    }
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                });
            });
        }

        // Initialize resource source toggle on modal open
        if (addResourceModal) {
            addResourceModal.addEventListener('shown.bs.modal', function() {
                toggleResourceSourceModal();
            });
        }
    });

    // Toggle resource source sections in modal
    function toggleResourceSourceModal() {
        const sourceFile = document.getElementById('modalSourceFile');
        const sourceUrl = document.getElementById('modalSourceUrl');
        const sourceExisting = document.getElementById('modalSourceExisting');
        const fileUploadSection = document.getElementById('modalFileUploadSection');
        const urlInputSection = document.getElementById('modalUrlInputSection');
        const existingResourceSection = document.getElementById('modalExistingResourceSection');
        const modalFileInput = document.getElementById('modalFileInput');
        const modalResourceUrlInput = document.getElementById('modalResourceUrlInput');
        const modalExistingResourceSelect = document.getElementById('modalExistingResourceSelect');

        // Hide all sections first
        if (fileUploadSection) fileUploadSection.style.display = 'none';
        if (urlInputSection) urlInputSection.style.display = 'none';
        if (existingResourceSection) existingResourceSection.style.display = 'none';

        // Remove required from all inputs
        if (modalFileInput) modalFileInput.removeAttribute('required');
        if (modalResourceUrlInput) modalResourceUrlInput.removeAttribute('required');
        if (modalExistingResourceSelect) modalExistingResourceSelect.removeAttribute('required');

        if (sourceFile && sourceFile.checked) {
            if (fileUploadSection) fileUploadSection.style.display = 'block';
            if (modalFileInput) modalFileInput.setAttribute('required', 'required');
        } else if (sourceUrl && sourceUrl.checked) {
            if (urlInputSection) urlInputSection.style.display = 'block';
            if (modalResourceUrlInput) modalResourceUrlInput.setAttribute('required', 'required');
        } else if (sourceExisting && sourceExisting.checked) {
            if (existingResourceSection) existingResourceSection.style.display = 'block';
            if (modalExistingResourceSelect) modalExistingResourceSelect.setAttribute('required', 'required');
        }
    }

    // File input change handler for modal
    const modalFileInput = document.getElementById('modalFileInput');
    if (modalFileInput) {
        modalFileInput.addEventListener('change', function() {
            const file = this.files[0];
            const filePreview = document.getElementById('modalFilePreview');
            
            if (!file) {
                if (filePreview) filePreview.style.display = 'none';
                return;
            }

            // Validate file size (50MB)
            if (file.size > 50 * 1024 * 1024) {
                alert('حجم الملف كبير جداً. الحد الأقصى 50MB');
                this.value = '';
                if (filePreview) filePreview.style.display = 'none';
                return;
            }

            // Display file info
            const fileName = document.getElementById('modalFileName');
            const fileType = document.getElementById('modalFileType');
            const fileSize = document.getElementById('modalFileSize');
            
            if (fileName) fileName.textContent = file.name;
            if (fileType) fileType.textContent = file.type || 'غير معروف';
            if (fileSize) fileSize.textContent = formatFileSize(file.size);
            if (filePreview) filePreview.style.display = 'block';
        });
    }

    // Format File Size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Auto-fill title when existing resource is selected in modal
    const modalExistingResourceSelect = document.getElementById('modalExistingResourceSelect');
    const modalTitleInput = document.querySelector('#add-resource-form input[name="title"]');
    
    if (modalExistingResourceSelect && modalTitleInput) {
        modalExistingResourceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const title = selectedOption.getAttribute('data-title');
                if (title && !modalTitleInput.value) {
                    modalTitleInput.value = title;
                }
            }
        });
    }
</script>
@stop

