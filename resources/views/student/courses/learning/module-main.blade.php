@php
    $learnCompletedModulesJson = json_encode(array_values($completedModules ?? []));
@endphp
<turbo-frame id="student-learn-main" data-turbo-action="advance" target="_top"
    data-current-module-id="{{ (int) $module->id }}"
    data-page-title="{{ e($module->title) }}"
    data-module-title="{{ e($module->title) }}"
    data-module-description="{{ e(Str::limit($module->description ?? '', 80)) }}"
    data-has-description="{{ ($module->description ?? '') !== '' ? '1' : '0' }}"
    data-is-completed="{{ $isCompleted ? '1' : '0' }}"
    data-module-type="{{ e($module->module_type) }}"
    data-completed-modules="{{ e($learnCompletedModulesJson) }}">

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Breadcrumb -->
    <div class="page-header">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}" data-turbo="false">الرئيسية</a></li>
            <li class="breadcrumb-item"><a href="{{ route('student.courses.my-courses') }}" data-turbo="false">كورساتي</a></li>
            <li class="breadcrumb-item"><a href="{{ route('student.courses.show', $module->course_id) }}" data-turbo="false">{{ $module->course->title }}</a></li>
            <li class="breadcrumb-item active">{{ $module->title }}</li>
        </ol>
    </div>

    @include('student.courses.learning.module-main-inner')
</turbo-frame>
