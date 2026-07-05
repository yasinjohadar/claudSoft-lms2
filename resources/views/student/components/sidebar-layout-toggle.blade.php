<div class="student-sidebar-layout-toggle js-sidebar-layout-toggle" role="group" aria-label="شكل القائمة الجانبية">
    @if(! empty($showLabel))
        <span class="student-sidebar-layout-toggle__label">{{ $showLabel }}</span>
    @endif
    <div class="student-sidebar-layout-toggle__track" data-active="full">
        <span class="student-sidebar-layout-toggle__slider" aria-hidden="true"></span>
        <button type="button"
                class="student-sidebar-layout-toggle__btn"
                data-layout="full"
                aria-pressed="false"
                title="قائمة كاملة">
            <span class="student-sidebar-layout-toggle__preview student-sidebar-layout-toggle__preview--full" aria-hidden="true"></span>
            <span class="student-sidebar-layout-toggle__text">كاملة</span>
        </button>
        <button type="button"
                class="student-sidebar-layout-toggle__btn"
                data-layout="mini"
                aria-pressed="false"
                title="قائمة مصغّرة">
            <span class="student-sidebar-layout-toggle__preview student-sidebar-layout-toggle__preview--mini" aria-hidden="true"></span>
            <span class="student-sidebar-layout-toggle__text">مصغّرة</span>
        </button>
    </div>
</div>
