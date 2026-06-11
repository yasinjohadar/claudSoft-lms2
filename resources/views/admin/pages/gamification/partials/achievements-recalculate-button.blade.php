@php
    $modalId = $modalId ?? 'achievementsRecalculateModal';
    $useGroupAction = !empty($useGroupAction);
@endphp

@if ($useGroupAction)
    <button type="button" class="group-show-action" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
        <span class="group-show-action__icon"><i class="fe fe-refresh-cw"></i></span>
        <span class="group-show-action__text">{{ $label ?? 'إعادة تحقق الإنجازات' }}</span>
    </button>
@else
    <button type="button" class="{{ $buttonClass ?? 'btn btn-sm btn-warning' }}" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
        <i class="fe fe-refresh-cw me-1"></i>{{ $label ?? 'إعادة تحقق الإنجازات' }}
    </button>
@endif

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">إعادة تحقق الإنجازات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">سيتم تنفيذ العمليات التالية لجميع الطلاب النشطين:</p>
                <ul class="mb-3 ps-3">
                    <li>مزامنة إحصائيات gamification من مصادر البيانات الفعلية</li>
                    <li>تهيئة وتتبع جميع الإنجازات النشطة</li>
                    <li>منح الإنجازات المستحقة التي لم تُمنح سابقاً</li>
                </ul>
                <p class="text-muted fs-12 mb-0">لا يشمل هذا الزر إعادة احتساب النقاط أو لوحات المتصدرين. قد تستغرق العملية بعض الوقت.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.gamification.achievements.recalculate-all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fe fe-refresh-cw me-1"></i>بدء إعادة التحقق
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
