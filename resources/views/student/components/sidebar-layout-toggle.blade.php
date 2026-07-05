<div class="student-sidebar-layout-toggle js-sidebar-layout-toggle" role="group" aria-label="شكل القائمة الجانبية">
    @if(! empty($showLabel))
        <span class="student-sidebar-layout-toggle__label">{{ $showLabel }}</span>
    @endif
    <div class="student-sidebar-layout-toggle__options">
        <button type="button"
                class="student-sidebar-layout-toggle__btn"
                data-layout="full"
                aria-pressed="false"
                title="قائمة كاملة">
            <i class="fe fe-menu" aria-hidden="true"></i>
            <span>كاملة</span>
        </button>
        <button type="button"
                class="student-sidebar-layout-toggle__btn"
                data-layout="mini"
                aria-pressed="false"
                title="قائمة مصغّرة">
            <i class="fe fe-disc" aria-hidden="true"></i>
            <span>مصغّرة</span>
        </button>
    </div>
</div>
