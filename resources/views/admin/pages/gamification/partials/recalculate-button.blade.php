@php
    $modalId = $modalId ?? 'gamificationRecalculateModal';
    $useGroupAction = empty($buttonClass);
@endphp

@if ($useGroupAction)
    <button type="button" class="group-show-action" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
        <span class="group-show-action__icon"><i class="fe fe-refresh-cw"></i></span>
        <span class="group-show-action__text">{{ $label ?? 'إعادة احتساب النقاط واللوحات والإنجازات' }}</span>
    </button>
@else
    <button type="button" class="{{ $buttonClass }}" data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
        <i class="fe fe-refresh-cw me-1"></i>{{ $label ?? 'إعادة احتساب النقاط واللوحات والإنجازات' }}
    </button>
@endif

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">إعادة احتساب النقاط واللوحات والإنجازات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">سيتم تنفيذ العمليات التالية:</p>
                <ul class="mb-3 ps-3">
                    <li>إعادة حساب نقاط وإحصائيات جميع الطلاب النشطين من سجل المعاملات</li>
                    <li>تحديث ترتيب جميع لوحات المتصدرين</li>
                    <li>مزامنة إحصائيات الطلاب والتحقق من الإنجازات المستحقة</li>
                </ul>
                <p class="text-muted fs-12 mb-0">قد تستغرق العملية بعض الوقت حسب عدد الطلاب. لا تغلق الصفحة حتى تكتمل.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                <form action="{{ route('admin.gamification.recalculate-all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fe fe-refresh-cw me-1"></i>بدء إعادة الاحتساب
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
