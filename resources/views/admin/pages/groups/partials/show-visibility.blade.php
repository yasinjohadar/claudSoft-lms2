@php
    $visibilityRequirements = $group->visibilityRequirements()->with('requiredGroup')->get();
@endphp

@if($visibilityRequirements->isNotEmpty())
    <div class="card custom-card dashboard-today-card group-show-panel dashboard-fade-in mb-4">
        <div class="card-header border-0 pb-0">
            <h4 class="card-title mb-1">
                <i class="fe fe-eye text-primary me-2"></i>
                شروط الظهور للطلاب
            </h4>
            <p class="fs-12 text-muted mb-0">هذه المجموعة تظهر فقط لأعضاء المجموعات التالية.</p>
        </div>
        <div class="card-body pt-3">
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach($visibilityRequirements as $requirement)
                    @if($requirement->requiredGroup)
                        <span class="group-show-chip">
                            <i class="fe fe-users me-1"></i>
                            {{ $requirement->requiredGroup->name }}
                        </span>
                    @endif
                @endforeach
            </div>
            <p class="text-muted small mb-0">
                <i class="fe fe-info me-1"></i>
                الطلاب الذين ليسوا أعضاءً في أي من هذه المجموعات لن يتمكنوا من رؤية هذه المجموعة.
            </p>
        </div>
    </div>
@elseif($group->allow_membership_requests && $group->is_visible_for_students)
    <div class="alert alert-warning border-0 shadow-sm dashboard-fade-in mb-4" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fe fe-alert-triangle fs-5 mt-1"></i>
            <div>
                <strong>تنبيه:</strong> لم يتم تحديد أي مجموعات مطلوبة للظهور. هذه المجموعة <strong>مخفية عن جميع الطلاب</strong> حالياً.
                <br>
                <small class="text-muted">قم بتعديل المجموعة وحدد «المجموعات المطلوبة للظهور» لإظهارها للطلاب المنتمين لتلك المجموعات.</small>
            </div>
        </div>
    </div>
@endif
