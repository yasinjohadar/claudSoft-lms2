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

                    <!-- Sections Accordion -->
                    <div class="accordion accordion-customicon1 accordion-primary" id="sectionsAccordion">
                    @forelse($course->sections()->orderBy('order_index')->get() as $section)
                        <div class="accordion-item">
                            <h2 class="accordion-header d-flex align-items-stretch" id="heading-{{ $section->id }}">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} flex-grow-1" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#section-{{ $section->id }}"
                                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="section-{{ $section->id }}"
                                        style="border-left: none; border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                    <div class="d-flex align-items-center w-100 justify-content-between me-3">
                                        <div>
                                            <i class="fas fa-folder me-2"></i>
                                            {{ $section->title }}
                                            @if($section->description)
                                                <br><small class="text-muted fw-normal">{{ $section->description }}</small>
                                            @endif
                                        </div>
                                        <span class="badge bg-light text-default">
                                            {{ $section->modules->count() }} {{ $section->modules->count() == 1 ? 'درس' : 'دروس' }}
                                        </span>
                                    </div>
                                </button>
                                <a href="{{ route('sections.questions.manage', $section->id) }}"
                                   class="btn btn-success d-flex align-items-center px-3"
                                   style="border-radius: 0; border-top-left-radius: var(--bs-border-radius); border-bottom-left-radius: {{ $loop->first ? '0' : 'var(--bs-border-radius)' }};"
                                   title="إدارة الأسئلة">
                                    <i class="fas fa-question-circle me-1"></i>أسئلة
                                </a>
                            </h2>
                            <div id="section-{{ $section->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                 aria-labelledby="heading-{{ $section->id }}" data-bs-parent="#sectionsAccordion">
                                <div class="accordion-body">
                                    <!-- Section Header with Actions -->
                                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                        <div></div>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('sections.questions.manage', $section->id) }}"
                                               class="btn btn-sm btn-outline-success"
                                               title="إدارة الأسئلة"
                                               onclick="event.stopPropagation();">
                                                <i class="fas fa-question-circle me-1"></i>الأسئلة
                                            </a>
                                            <a href="{{ route('courses.sections.edit', [$course->id, $section->id]) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit me-1"></i>تحرير
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-secondary"
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
                                        <div class="mb-3 border rounded" style="transition: all 0.3s ease;">
                                            <div class="d-flex align-items-center justify-content-between p-3">
                                                <div class="d-flex align-items-center flex-grow-1">
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
                                                        <h6 class="mb-1 fw-semibold text-dark">{{ $module->title }}</h6>
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
                                                            @if(!$module->is_visible)
                                                                <span class="badge bg-secondary badge-sm ms-1">مخفي</span>
                                                            @endif
                                                            @if($module->is_required)
                                                                <i class="fas fa-asterisk text-danger ms-1" style="font-size: 8px;" title="مطلوب"></i>
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            <div class="btn-group" role="group">
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
                                                @else
                                                    <a href="{{ route('sections.modules.edit', [$section->id, $module->id]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-edit me-1"></i>تحرير
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                                        onclick="toggleVisibility('module', {{ $module->id }})">
                                                    <i class="far fa-eye{{ $module->is_visible ? '' : '-slash' }} me-1"></i>
                                                    {{ $module->is_visible ? 'إخفاء' : 'إظهار' }}
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                        onclick="if(confirm('هل أنت متأكد من حذف هذا الدرس؟')) document.getElementById('delete-form-{{ $module->id }}').submit();">
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

                                    <!-- Add Activity Buttons -->
                                    <div class="mt-3 p-3 bg-light rounded">
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
                                        </div>
                                    </div>
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
        const url = type === 'section'
            ? `/admin/sections/${id}/toggle-visibility`
            : `/admin/modules/${id}/toggle-visibility`;

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
</script>
@stop
