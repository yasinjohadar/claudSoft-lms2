@php
    $learnCompletedModules = array_values($completedModules ?? []);
@endphp
<turbo-frame id="student-learn-main" data-turbo-action="advance" target="_top"
    data-current-module-id="{{ (int) $module->id }}"
    data-page-title="{{ e($module->title) }}"
    data-module-title="{{ e($module->title) }}"
    data-module-description="{{ e(Str::limit($module->description ?? '', 80)) }}"
    data-has-description="{{ ($module->description ?? '') !== '' ? '1' : '0' }}"
    data-is-completed="{{ $isCompleted ? '1' : '0' }}"
    data-module-type="{{ e($module->module_type) }}"
    data-completed-modules='@json($learnCompletedModules)'>

    @include('student.components.alerts')

    @include('student.courses.learning.module-main-inner')
</turbo-frame>
