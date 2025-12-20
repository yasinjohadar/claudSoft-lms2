@extends('admin.layouts.master')

@section('page-title')
    {{ $course->title }} - التعلم
@stop

@section('css')
<style>
    /* Learning Page Layout */
    .learning-container {
        display: flex;
        gap: 0;
        margin: -1.5rem;
        min-height: calc(100vh - 80px);
    }

    /* Sidebar */
    .learning-sidebar {
        width: 350px;
        background: white;
        border-left: 1px solid #e9ecef;
        overflow-y: auto;
        position: sticky;
        top: 0;
        height: calc(100vh - 80px);
    }

    .learning-sidebar-header {
        padding: 1.5rem;
        border-bottom: 2px solid #e9ecef;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .course-progress-ring {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: conic-gradient(
            #fff 0deg,
            #fff calc(var(--progress) * 3.6deg),
            rgba(255,255,255,0.3) calc(var(--progress) * 3.6deg)
        );
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        position: relative;
    }

    .course-progress-inner {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
    }

    /* Sections & Modules */
    .sidebar-sections {
        padding: 0;
    }

    .section-item {
        border-bottom: 1px solid #f0f0f0;
    }

    .section-header {
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
        user-select: none;
    }

    .section-header:hover {
        background: #e9ecef;
    }

    .section-header.active {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        border-right: 4px solid #667eea;
    }

    .section-modules {
        display: none;
        background: white;
    }

    .section-modules.show {
        display: block;
    }

    .module-item {
        padding: 0.75rem 1.5rem 0.75rem 2.5rem;
        border-bottom: 1px solid #f8f9fa;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s;
    }

    .module-item:hover {
        background: #f8f9fa;
        padding-right: 1.25rem;
    }

    .module-item.active {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.15) 0%, rgba(118, 75, 162, 0.15) 100%);
        border-right: 3px solid #667eea;
        font-weight: 600;
    }

    .module-item.completed {
        opacity: 0.7;
    }

    .module-icon {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        margin-left: 0.75rem;
    }

    .completion-check {
        width: 20px;
        height: 20px;
        border: 2px solid #ddd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        transition: all 0.3s;
    }

    .completion-check.completed {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-color: #28a745;
        color: white;
    }

    /* Main Content Area */
    .learning-content {
        flex: 1;
        overflow-y: auto;
        background: #f8f9fa;
        padding: 2rem;
    }

    .content-header {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .content-body {
        background: white;
        border-radius: 12px;
        padding: 2rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        min-height: 500px;
    }

    /* Mark as Complete Button - CRITICAL */
    .completion-actions {
        position: sticky;
        bottom: 0;
        background: white;
        padding: 1.5rem;
        border-top: 2px solid #e9ecef;
        box-shadow: 0 -5px 20px rgba(0,0,0,0.05);
        margin: 2rem -2rem -2rem;
        border-radius: 0 0 12px 12px;
    }

    .btn-mark-complete {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        padding: 1rem 2rem;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        transition: all 0.3s;
        position: relative;
        overflow: hidden;
    }

    .btn-mark-complete:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
    }

    .btn-mark-complete:active {
        transform: translateY(0);
    }

    .btn-mark-complete.completed {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
    }

    .btn-mark-complete .check-icon {
        display: inline-block;
        margin-left: 0.5rem;
        font-size: 1.3rem;
    }

    /* Navigation Buttons */
    .nav-buttons {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .btn-nav {
        flex: 1;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    /* Video Player */
    .video-container {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .video-container iframe,
    .video-container video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    /* Lesson Content */
    .lesson-content {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #333;
    }

    .lesson-content h1, .lesson-content h2, .lesson-content h3 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        color: #2c3e50;
    }

    .lesson-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }

    .lesson-content pre {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border-right: 4px solid #667eea;
        overflow-x: auto;
    }

    /* Resources */
    .resource-item {
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.3s;
    }

    .resource-item:hover {
        background: #e9ecef;
        transform: translateX(-5px);
    }

    .resource-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-left: 1rem;
    }

    /* Progress Indicator */
    .module-progress-indicator {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: rgba(255,255,255,0.95);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Quiz Styles */
    .quiz-question {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border-right: 4px solid #667eea;
    }

    .quiz-option {
        padding: 1rem;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.3s;
    }

    .quiz-option:hover {
        border-color: #667eea;
        background: #f8f9fa;
    }

    .quiz-option.selected {
        border-color: #667eea;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    }

    /* Responsive */
    @media (max-width: 992px) {
        .learning-container {
            flex-direction: column;
        }

        .learning-sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }
    }

    /* Loading Animation */
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@stop

@section('content')
    <div class="learning-container">

        <!-- Sidebar -->
        <div class="learning-sidebar">
            <!-- Sidebar Header -->
            <div class="learning-sidebar-header">
                <div class="course-progress-ring" style="--progress: {{ $enrollment->completion_percentage }}">
                    <div class="course-progress-inner">
                        {{ number_format($enrollment->completion_percentage, 0) }}%
                    </div>
                </div>
                <h6 class="text-center mb-0">{{ $course->title }}</h6>
                <div class="text-center mt-2" style="font-size: 0.85rem; opacity: 0.9;">
                    {{ $completedModules }} من {{ $totalModules }} دروس مكتملة
                </div>
            </div>

            <!-- Sections & Modules -->
            <div class="sidebar-sections">
                @foreach($course->sections as $section)
                    <div class="section-item">
                        <div class="section-header {{ $currentSection && $currentSection->id == $section->id ? 'active' : '' }}"
                             onclick="toggleSection({{ $section->id }})">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $section->title }}</div>
                                <small class="text-muted">
                                    {{ $section->modules->where('is_visible', true)->count() }} دروس
                                </small>
                            </div>
                            <i class="fas fa-chevron-down" id="chevron-section-{{ $section->id }}"></i>
                        </div>

                        <div class="section-modules {{ $currentSection && $currentSection->id == $section->id ? 'show' : '' }}"
                             id="section-modules-{{ $section->id }}">
                            @foreach($section->modules->where('is_visible', true) as $module)
                                @php
                                    $isCompleted = isset($completedModuleIds) && in_array($module->id, $completedModuleIds);
                                    $isActive = $currentModule && $currentModule->id == $module->id;
                                @endphp

                                <div class="module-item {{ $isActive ? 'active' : '' }} {{ $isCompleted ? 'completed' : '' }}"
                                     onclick="window.location='{{ route('student.learn.module', $module->id) }}'">
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div class="module-icon
                                            {{ $module->module_type == 'lesson' ? 'bg-primary-transparent text-primary' : '' }}
                                            {{ $module->module_type == 'video' ? 'bg-danger-transparent text-danger' : '' }}
                                            {{ $module->module_type == 'quiz' ? 'bg-success-transparent text-success' : '' }}
                                            {{ $module->module_type == 'assignment' ? 'bg-warning-transparent text-warning' : '' }}
                                            {{ $module->module_type == 'resource' ? 'bg-info-transparent text-info' : '' }}">
                                            @if($module->module_type == 'lesson')
                                                <i class="fas fa-book-open"></i>
                                            @elseif($module->module_type == 'video')
                                                <i class="fas fa-play"></i>
                                            @elseif($module->module_type == 'quiz')
                                                <i class="fas fa-question-circle"></i>
                                            @elseif($module->module_type == 'assignment')
                                                <i class="fas fa-tasks"></i>
                                            @elseif($module->module_type == 'resource')
                                                <i class="fas fa-file-download"></i>
                                            @else
                                                <i class="fas fa-file"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div style="font-size: 0.9rem;">{{ $module->title }}</div>
                                            @if($module->duration)
                                                <small class="text-muted">{{ $module->duration }} دقيقة</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="completion-check {{ $isCompleted ? 'completed' : '' }}">
                                        @if($isCompleted)
                                            <i class="fas fa-check"></i>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Main Content -->
        <div class="learning-content">
            @if($currentModule)
                <!-- Content Header -->
                <div class="content-header">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-{{ $currentModule->module_type == 'lesson' ? 'primary' : '' }}
                                                       {{ $currentModule->module_type == 'video' ? 'danger' : '' }}
                                                       {{ $currentModule->module_type == 'quiz' ? 'success' : '' }}
                                                       {{ $currentModule->module_type == 'resource' ? 'info' : '' }}">
                                    {{ ucfirst($currentModule->module_type) }}
                                </span>
                                @if($isCurrentModuleCompleted)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>مكتمل
                                    </span>
                                @endif
                            </div>
                            <h3 class="mb-1">{{ $currentModule->title }}</h3>
                            @if($currentModule->description)
                                <p class="text-muted mb-0">{{ $currentModule->description }}</p>
                            @endif
                        </div>
                        <a href="{{ route('student.courses.my-courses') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>إغلاق
                        </a>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="nav-buttons">
                        @if($previousModule)
                            <a href="{{ route('student.learn.module', $previousModule->id) }}"
                               class="btn btn-nav btn-outline-primary">
                                <i class="fas fa-chevron-right me-2"></i>الدرس السابق
                            </a>
                        @else
                            <button class="btn btn-nav btn-outline-secondary" disabled>
                                <i class="fas fa-chevron-right me-2"></i>الدرس السابق
                            </button>
                        @endif

                        @if($nextModule)
                            <a href="{{ route('student.learn.module', $nextModule->id) }}"
                               class="btn btn-nav btn-outline-primary">
                                الدرس التالي<i class="fas fa-chevron-left ms-2"></i>
                            </a>
                        @else
                            <button class="btn btn-nav btn-outline-secondary" disabled>
                                الدرس التالي<i class="fas fa-chevron-left ms-2"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Content Body -->
                <div class="content-body">
                    @if($currentModule->module_type == 'video')
                        <!-- Video Player -->
                        @include('student.pages.learn.partials.video-player', ['module' => $currentModule])

                    @elseif($currentModule->module_type == 'lesson')
                        <!-- Lesson Content -->
                        @include('student.pages.learn.partials.lesson-content', ['module' => $currentModule])

                    @elseif($currentModule->module_type == 'resource')
                        <!-- Resource -->
                        @include('student.pages.learn.partials.resource', ['module' => $currentModule])

                    @elseif($currentModule->module_type == 'quiz')
                        <!-- Quiz -->
                        @include('student.pages.learn.partials.quiz', ['module' => $currentModule])

                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-file fa-4x mb-3 opacity-25"></i>
                            <h5>محتوى غير مدعوم</h5>
                            <p>نوع المحتوى: {{ $currentModule->module_type }}</p>
                        </div>
                    @endif

                    <!-- CRITICAL: Mark as Complete Button -->
                    <div class="completion-actions">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                @if($isCurrentModuleCompleted)
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    لقد أكملت هذا الدرس بنجاح
                                @else
                                    <i class="fas fa-info-circle me-2"></i>
                                    أكمل هذا الدرس للمتابعة
                                @endif
                            </div>

                            @if($isCurrentModuleCompleted)
                                <form action="{{ route('student.modules.mark-incomplete', $currentModule->id) }}"
                                      method="POST" class="d-inline" id="markIncompleteForm">
                                    @csrf
                                    <button type="submit" class="btn btn-mark-complete completed" id="btnMarkComplete">
                                        <i class="fas fa-undo check-icon"></i>
                                        إلغاء الإنجاز
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('student.modules.mark-complete', $currentModule->id) }}"
                                      method="POST" class="d-inline" id="markCompleteForm">
                                    @csrf
                                    <button type="submit" class="btn btn-mark-complete" id="btnMarkComplete">
                                        <span class="btn-text">تم الإنجاز</span>
                                        <i class="fas fa-check-circle check-icon"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

            @else
                <!-- No Module Selected -->
                <div class="content-body">
                    <div class="text-center py-5">
                        <i class="fas fa-graduation-cap fa-5x text-muted mb-4 opacity-25"></i>
                        <h4 class="text-muted mb-3">مرحباً بك في الكورس!</h4>
                        <p class="text-muted">اختر درساً من القائمة الجانبية للبدء</p>
                        @if($firstModule)
                            <a href="{{ route('student.learn.module', $firstModule->id) }}"
                               class="btn btn-primary mt-3">
                                <i class="fas fa-play me-2"></i>ابدأ التعلم
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

    </div>
@stop

@section('script')
<script>
    // Toggle Section
    function toggleSection(sectionId) {
        const modules = document.getElementById('section-modules-' + sectionId);
        const chevron = document.getElementById('chevron-section-' + sectionId);
        const header = event.currentTarget;

        modules.classList.toggle('show');

        if (modules.classList.contains('show')) {
            chevron.classList.remove('fa-chevron-down');
            chevron.classList.add('fa-chevron-up');
        } else {
            chevron.classList.remove('fa-chevron-up');
            chevron.classList.add('fa-chevron-down');
        }
    }

    // Mark as Complete - CRITICAL FEATURE
    @if($currentModule)
        document.getElementById('markCompleteForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnMarkComplete');
            const originalHTML = btn.innerHTML;

            // Show loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="loading-spinner"></span> جاري الحفظ...';

            // Submit form
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    btn.classList.add('completed');
                    btn.innerHTML = '<i class="fas fa-undo check-icon"></i> إلغاء الإنجاز';

                    // Update sidebar completion indicator
                    const completionCheck = document.querySelector(`.module-item.active .completion-check`);
                    if (completionCheck) {
                        completionCheck.classList.add('completed');
                        completionCheck.innerHTML = '<i class="fas fa-check"></i>';
                    }

                    // Update progress ring
                    const progressRing = document.querySelector('.course-progress-ring');
                    if (progressRing && data.course_progress) {
                        progressRing.style.setProperty('--progress', data.course_progress.percentage);
                        progressRing.querySelector('.course-progress-inner').textContent =
                            Math.round(data.course_progress.percentage) + '%';
                    }

                    // Show success message
                    showNotification('success', 'تم تسجيل إنجازك بنجاح! 🎉');

                    // Auto-navigate to next module after 2 seconds
                    @if($nextModule)
                        setTimeout(() => {
                            window.location.href = '{{ route('student.learn.module', $nextModule->id) }}';
                        }, 2000);
                    @endif
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                    showNotification('error', data.error || 'حدث خطأ');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                btn.innerHTML = originalHTML;
                showNotification('error', 'حدث خطأ في الاتصال');
            });
        });

        document.getElementById('markIncompleteForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('btnMarkComplete');

            // Show loading state
            btn.disabled = true;
            btn.innerHTML = '<span class="loading-spinner"></span> جاري الحفظ...';

            // Submit form
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to update UI
                    location.reload();
                } else {
                    btn.disabled = false;
                    showNotification('error', data.error || 'حدث خطأ');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btn.disabled = false;
                showNotification('error', 'حدث خطأ في الاتصال');
            });
        });
    @endif

    // Notification Helper
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show`;
        alert.style.position = 'fixed';
        alert.style.top = '20px';
        alert.style.left = '50%';
        alert.style.transform = 'translateX(-50%)';
        alert.style.zIndex = '9999';
        alert.style.minWidth = '300px';
        alert.innerHTML = `
            <strong><i class="fas ${icon} me-2"></i>${type === 'success' ? 'نجح!' : 'خطأ!'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    // Auto-expand current section on page load
    document.addEventListener('DOMContentLoaded', function() {
        const activeSection = document.querySelector('.section-header.active');
        if (activeSection) {
            const sectionId = activeSection.getAttribute('onclick').match(/\d+/)[0];
            const chevron = document.getElementById('chevron-section-' + sectionId);
            if (chevron) {
                chevron.classList.remove('fa-chevron-down');
                chevron.classList.add('fa-chevron-up');
            }
        }
    });
</script>
@stop
